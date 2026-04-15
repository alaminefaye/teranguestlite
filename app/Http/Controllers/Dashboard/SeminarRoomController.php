<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\SeminarRoom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SeminarRoomController extends Controller
{
    public function index(Request $request): View
    {
        $query = SeminarRoom::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            }
            if ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $rooms = $query->ordered()->paginate(12);

        $stats = [
            'total' => SeminarRoom::count(),
            'active' => SeminarRoom::active()->count(),
        ];

        return view('pages.dashboard.seminar-rooms.index', [
            'title' => 'Séminaires',
            'rooms' => $rooms,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        return view('pages.dashboard.seminar-rooms.create', ['title' => 'Créer une salle']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:0',
            'equipments' => 'nullable|string',
            'image' => 'nullable|image|max:30720',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['enterprise_id'] = auth()->user()->enterprise_id;
        $validated['is_active'] = $request->has('is_active');
        $validated['equipments'] = $this->parseLines($request->input('equipments'));

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('seminar-rooms', 'public');
        }

        SeminarRoom::create($validated);

        return redirect()->route('dashboard.seminar-rooms.index')
            ->with('success', 'Salle créée avec succès !');
    }

    public function show(SeminarRoom $seminar_room): View
    {
        return view('pages.dashboard.seminar-rooms.show', [
            'title' => (string) $seminar_room->name,
            'room' => $seminar_room,
        ]);
    }

    public function edit(SeminarRoom $seminar_room): View
    {
        return view('pages.dashboard.seminar-rooms.edit', [
            'title' => 'Modifier ' . (string) $seminar_room->name,
            'room' => $seminar_room,
        ]);
    }

    public function update(Request $request, SeminarRoom $seminar_room): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:0',
            'equipments' => 'nullable|string',
            'image' => 'nullable|image|max:30720',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['equipments'] = $this->parseLines($request->input('equipments'));

        if ($request->hasFile('image')) {
            if ($seminar_room->image) {
                Storage::disk('public')->delete($seminar_room->image);
            }
            $validated['image'] = $request->file('image')->store('seminar-rooms', 'public');
        }

        $seminar_room->update($validated);

        return redirect()->route('dashboard.seminar-rooms.index')
            ->with('success', 'Salle mise à jour avec succès !');
    }

    public function destroy(SeminarRoom $seminar_room): RedirectResponse
    {
        if ($seminar_room->image) {
            Storage::disk('public')->delete($seminar_room->image);
        }

        $seminar_room->delete();

        return redirect()->route('dashboard.seminar-rooms.index')
            ->with('success', 'Salle supprimée avec succès !');
    }

    public function toggleActive(SeminarRoom $seminar_room): RedirectResponse
    {
        $seminar_room->update(['is_active' => !$seminar_room->is_active]);
        $label = $seminar_room->is_active ? 'affichée' : 'masquée';
        return redirect()->route('dashboard.seminar-rooms.index')
            ->with('success', "Salle {$label}.");
    }

    private function parseLines(?string $value): array
    {
        if (empty($value)) {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode("\n", $value))));
    }
}

