<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class GuestController extends Controller
{
    public function index(Request $request): View
    {
        $query = Guest::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('access_code', 'like', '%' . $request->search . '%')
                  ->orWhere('id_document_number', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('with_reservation') && $request->with_reservation === '1') {
            $query->whereHas('reservations');
        }

        $sort = $request->get('sort', 'name_asc');
        if ($sort === 'name_desc') {
            $query->orderBy('name', 'desc');
        } elseif ($sort === 'created_desc') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sort === 'created_asc') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('name', 'asc');
        }

        $guests = $query->paginate(15);
        $stats = [
            'total' => Guest::count(),
            'with_reservation' => Guest::whereHas('reservations')->count(),
        ];

        return view('pages.dashboard.guests.index', [
            'title' => 'Clients (invités)',
            'guests' => $guests,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        return view('pages.dashboard.guests.create', [
            'title' => 'Enregistrer un client',
            'nationalities' => config('nationalities', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->validationRules());

        $validated['enterprise_id'] = auth()->user()->enterprise_id;
        $validated['access_code'] = Guest::generateAccessCode($validated['enterprise_id']);

        if ($request->hasFile('id_document_photo')) {
            $validated['id_document_photo'] = $request->file('id_document_photo')
                ->store('guests/id-documents', 'public');
        }
        if ($request->hasFile('id_document_photo_verso')) {
            $validated['id_document_photo_verso'] = $request->file('id_document_photo_verso')
                ->store('guests/id-documents', 'public');
        }

        $guest = Guest::create($validated);

        return redirect()->route('dashboard.guests.show', $guest)
            ->with('success', 'Client enregistré avec succès ! Code tablette : ' . $guest->access_code);
    }

    public function show(Guest $guest): View
    {
        $guest->loadCount('reservations');
        $orders = $guest->orders()
            ->with(['orderItems', 'room'])
            ->orderByDesc('created_at')
            ->take(50)
            ->get();
        return view('pages.dashboard.guests.show', [
            'title' => $guest->name,
            'guest' => $guest,
            'orders' => $orders,
        ]);
    }

    public function edit(Guest $guest): View
    {
        return view('pages.dashboard.guests.edit', [
            'title' => 'Modifier ' . $guest->name,
            'guest' => $guest,
            'nationalities' => config('nationalities', []),
        ]);
    }

    public function update(Request $request, Guest $guest): RedirectResponse
    {
        $validated = $request->validate($this->validationRules());

        if ($request->hasFile('id_document_photo')) {
            if ($guest->id_document_photo) {
                Storage::disk('public')->delete($guest->id_document_photo);
            }
            $validated['id_document_photo'] = $request->file('id_document_photo')
                ->store('guests/id-documents', 'public');
        }
        if ($request->hasFile('id_document_photo_verso')) {
            if ($guest->id_document_photo_verso) {
                Storage::disk('public')->delete($guest->id_document_photo_verso);
            }
            $validated['id_document_photo_verso'] = $request->file('id_document_photo_verso')
                ->store('guests/id-documents', 'public');
        }

        $guest->update($validated);

        return redirect()->route('dashboard.guests.show', $guest)
            ->with('success', 'Client mis à jour avec succès !');
    }

    private function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'gender' => 'nullable|string|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'nationality' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'id_document_type' => 'nullable|string|max:50',
            'id_document_number' => 'nullable|string|max:100',
            'id_document_place_of_issue' => 'nullable|string|max:150',
            'id_document_issued_at' => 'nullable|date',
            'id_document_photo' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
            'id_document_photo_verso' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
            'notes' => 'nullable|string',
        ];
    }

    public function destroy(Guest $guest): RedirectResponse
    {
        if ($guest->reservations()->exists()) {
            return back()->with('error', 'Impossible de supprimer ce client : des réservations sont associées.');
        }
        $guest->delete();
        return redirect()->route('dashboard.guests.index')
            ->with('success', 'Client supprimé avec succès !');
    }

    /**
     * Régénérer le code (admin / gérant uniquement)
     */
    public function regenerateCode(Guest $guest): RedirectResponse
    {
        $newCode = $guest->regenerateAccessCode();
        return back()->with('success', 'Nouveau code tablette : ' . $newCode);
    }
}
