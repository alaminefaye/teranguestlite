<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Gestion des accès tablette : comptes "Client Chambre XXX" (User role=guest avec room_number).
 * Visible par le gérant de l'hôtel dans son dashboard.
 */
class TabletAccessController extends Controller
{
    private function getEnterpriseId(): int
    {
        $id = auth()->user()->enterprise_id;
        if (!$id) {
            abort(403, 'Accès réservé à un administrateur d\'hôtel.');
        }
        return $id;
    }

    private function queryGuestUsers()
    {
        return User::where('enterprise_id', $this->getEnterpriseId())
            ->where('role', 'guest')
            ->orderBy('room_number');
    }

    public function index(Request $request): View
    {
        $query = $this->queryGuestUsers();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('room_number', 'like', '%' . $request->search . '%');
            });
        }

        $guests = $query->paginate(15);
        $total = $this->queryGuestUsers()->count();

        return view('pages.dashboard.tablet-accesses.index', [
            'title' => 'Accès tablettes',
            'guests' => $guests,
            'total' => $total,
        ]);
    }

    public function create(): View
    {
        $enterpriseId = $this->getEnterpriseId();
        $usedRoomNumbers = User::where('enterprise_id', $enterpriseId)
            ->where('role', 'guest')
            ->whereNotNull('room_number')
            ->pluck('room_number');

        $rooms = Room::where('enterprise_id', $enterpriseId)
            ->whereNotIn('room_number', $usedRoomNumbers)
            ->orderBy('room_number')
            ->get();

        return view('pages.dashboard.tablet-accesses.create', [
            'title' => 'Créer un accès tablette',
            'rooms' => $rooms,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $enterpriseId = $this->getEnterpriseId();
        $room = Room::where('id', $validated['room_id'])->where('enterprise_id', $enterpriseId)->firstOrFail();

        if (User::where('enterprise_id', $enterpriseId)->where('role', 'guest')->where('room_number', $room->room_number)->exists()) {
            return back()->withInput()->with('error', 'Un accès tablette existe déjà pour cette chambre.');
        }

        $name = $validated['name'] ?: 'Client Chambre ' . $room->room_number;

        User::create([
            'name' => $name,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'guest',
            'enterprise_id' => $enterpriseId,
            'department' => null,
            'room_number' => $room->room_number,
        ]);

        return redirect()->route('dashboard.tablet-accesses.index')
            ->with('success', 'Accès tablette créé pour la chambre ' . $room->room_number . '.');
    }

    public function edit(int $id): View|RedirectResponse
    {
        $user = User::where('id', $id)
            ->where('role', 'guest')
            ->where('enterprise_id', $this->getEnterpriseId())
            ->firstOrFail();

        $enterpriseId = $this->getEnterpriseId();
        $usedRoomNumbers = User::where('enterprise_id', $enterpriseId)
            ->where('role', 'guest')
            ->whereNotNull('room_number')
            ->where('id', '!=', $user->id)
            ->pluck('room_number');

        $rooms = Room::where('enterprise_id', $enterpriseId)
            ->where(function ($q) use ($user, $usedRoomNumbers) {
                $q->where('room_number', $user->room_number)
                    ->orWhereNotIn('room_number', $usedRoomNumbers);
            })
            ->orderBy('room_number')
            ->get();

        $currentRoomId = Room::where('enterprise_id', $enterpriseId)->where('room_number', $user->room_number)->value('id');

        return view('pages.dashboard.tablet-accesses.edit', [
            'title' => 'Modifier l\'accès tablette',
            'user' => $user,
            'rooms' => $rooms,
            'currentRoomId' => $currentRoomId,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $user = User::where('id', $id)
            ->where('role', 'guest')
            ->where('enterprise_id', $this->getEnterpriseId())
            ->firstOrFail();

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $room = Room::where('id', $validated['room_id'])->where('enterprise_id', $this->getEnterpriseId())->firstOrFail();

        $existing = User::where('enterprise_id', $this->getEnterpriseId())
            ->where('role', 'guest')
            ->where('room_number', $room->room_number)
            ->where('id', '!=', $user->id)
            ->exists();
        if ($existing) {
            return back()->withInput()->with('error', 'Un autre accès tablette existe déjà pour cette chambre.');
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->room_number = $room->room_number;
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        return redirect()->route('dashboard.tablet-accesses.index')
            ->with('success', 'Accès tablette mis à jour.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $user = User::where('id', $id)
            ->where('role', 'guest')
            ->where('enterprise_id', $this->getEnterpriseId())
            ->firstOrFail();

        $roomNumber = $user->room_number;
        $user->delete();

        return redirect()->route('dashboard.tablet-accesses.index')
            ->with('success', 'Accès tablette supprimé (chambre ' . $roomNumber . ').');
    }
}
