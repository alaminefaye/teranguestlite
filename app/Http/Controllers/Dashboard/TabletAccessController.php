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
 * Gestion des accès tablette : uniquement les identifiants avec le rôle guest
 * (comptes "Client Chambre XXX"). Visible par le gérant de l'hôtel dans son dashboard.
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

    /** Requête des accès tablette : uniquement les users avec rôle guest. */
    private function queryTabletAccessUsers()
    {
        return User::guests()
            ->with('room')
            ->where('enterprise_id', $this->getEnterpriseId())
            ->orderBy('room_number');
    }

    public function index(Request $request): View
    {
        $query = $this->queryTabletAccessUsers();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('room_number', 'like', '%' . $request->search . '%');
            });
        }

        $guests = $query->paginate(15);
        $total = $this->queryTabletAccessUsers()->count();

        return view('pages.dashboard.tablet-accesses.index', [
            'title' => 'Accès tablettes',
            'guests' => $guests,
            'total' => $total,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        $enterpriseId = $this->getEnterpriseId();

        // Chambres déjà reliées : uniquement par les identifiants avec rôle guest
        $usedRoomIds = User::guests()
            ->where('enterprise_id', $enterpriseId)
            ->whereNotNull('room_id')
            ->pluck('room_id');
        $usedRoomNumbers = User::guests()
            ->where('enterprise_id', $enterpriseId)
            ->whereNotNull('room_number')
            ->pluck('room_number');
        $roomIdsByNumber = Room::where('enterprise_id', $enterpriseId)
            ->whereIn('room_number', $usedRoomNumbers)
            ->pluck('id');
        $allUsedRoomIds = $usedRoomIds->merge($roomIdsByNumber)->unique()->values();

        $preselectedRoomId = request('room_id') ? (int) request('room_id') : null;
        if ($preselectedRoomId && $allUsedRoomIds->contains($preselectedRoomId)) {
            $preselectedRoom = Room::where('enterprise_id', $enterpriseId)->find($preselectedRoomId);
            $existingAccess = $preselectedRoom
                ? User::guests()
                    ->where('enterprise_id', $enterpriseId)
                    ->where(function ($q) use ($preselectedRoom) {
                        $q->where('room_id', $preselectedRoom->id)->orWhere('room_number', $preselectedRoom->room_number);
                    })
                    ->first()
                : null;
            if ($existingAccess) {
                return redirect()->route('dashboard.tablet-accesses.edit', $existingAccess->id)
                    ->with('info', 'Cette chambre a déjà un accès tablette. Vous pouvez le modifier ci-dessous.');
            }
        }

        $rooms = Room::where('enterprise_id', $enterpriseId)
            ->whereNotIn('id', $allUsedRoomIds)
            ->orderBy('room_number')
            ->get();

        // Pré-sélection : n'ajouter la chambre à la liste que si elle n'est pas déjà reliée
        if ($preselectedRoomId && !$rooms->contains('id', $preselectedRoomId)) {
            $preselectedRoom = Room::where('enterprise_id', $enterpriseId)->find($preselectedRoomId);
            if ($preselectedRoom && !$allUsedRoomIds->contains($preselectedRoom->id)) {
                $rooms = $rooms->push($preselectedRoom)->sortBy('room_number')->values();
            } else {
                $preselectedRoomId = null;
            }
        }

        return view('pages.dashboard.tablet-accesses.create', [
            'title' => 'Créer un accès tablette',
            'rooms' => $rooms,
            'preselectedRoomId' => $preselectedRoomId,
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

        $alreadyLinked = User::guests()
            ->where('enterprise_id', $enterpriseId)
            ->where(function ($q) use ($room) {
                $q->where('room_id', $room->id)->orWhere('room_number', $room->room_number);
            })
            ->exists();
        if ($alreadyLinked) {
            return back()->withInput()->with('error', 'Un accès tablette existe déjà pour cette chambre.');
        }

        $name = $validated['name'] ?: 'Client Chambre ' . $room->room_number;

        User::create([
            'name' => $name,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'client_code' => mb_strtoupper(mb_substr(uniqid(), -6)), // Génération d'un code client unique de 6 caractères
            'role' => 'guest',
            'enterprise_id' => $enterpriseId,
            'department' => null,
            'room_number' => $room->room_number,
            'room_id' => $room->id,
        ]);

        return redirect()->route('dashboard.tablet-accesses.index')
            ->with('success', 'Accès tablette créé pour la chambre ' . $room->room_number . '.');
    }

    public function edit(int $id): View|RedirectResponse
    {
        $user = User::guests()
            ->where('id', $id)
            ->where('enterprise_id', $this->getEnterpriseId())
            ->firstOrFail();

        $enterpriseId = $this->getEnterpriseId();
        // Chambres déjà prises par un autre accès (uniquement rôle guest)
        $usedRoomIds = User::guests()
            ->where('enterprise_id', $enterpriseId)
            ->whereNotNull('room_id')
            ->where('id', '!=', $user->id)
            ->pluck('room_id');
        $usedRoomNumbers = User::guests()
            ->where('enterprise_id', $enterpriseId)
            ->where('id', '!=', $user->id)
            ->whereNotNull('room_number')
            ->pluck('room_number');
        $roomIdsByNumber = Room::where('enterprise_id', $enterpriseId)
            ->whereIn('room_number', $usedRoomNumbers)
            ->pluck('id');
        $allUsedRoomIds = $usedRoomIds->merge($roomIdsByNumber)->unique()->values();

        $currentRoomId = $user->room_id ?? Room::where('enterprise_id', $enterpriseId)->where('room_number', $user->room_number)->value('id');
        $rooms = Room::where('enterprise_id', $enterpriseId)
            ->where(function ($q) use ($currentRoomId, $allUsedRoomIds) {
                $q->where('id', $currentRoomId)
                    ->orWhereNotIn('id', $allUsedRoomIds);
            })
            ->orderBy('room_number')
            ->get();

        return view('pages.dashboard.tablet-accesses.edit', [
            'title' => 'Modifier l\'accès tablette',
            'user' => $user,
            'rooms' => $rooms,
            'currentRoomId' => $currentRoomId,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $user = User::guests()
            ->where('id', $id)
            ->where('enterprise_id', $this->getEnterpriseId())
            ->firstOrFail();

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $room = Room::where('id', $validated['room_id'])->where('enterprise_id', $this->getEnterpriseId())->firstOrFail();

        $existing = User::guests()
            ->where('enterprise_id', $this->getEnterpriseId())
            ->where('room_id', $room->id)
            ->where('id', '!=', $user->id)
            ->exists();
        if ($existing) {
            return back()->withInput()->with('error', 'Un autre accès tablette existe déjà pour cette chambre.');
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->room_number = $room->room_number;
        $user->room_id = $room->id;
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        return redirect()->route('dashboard.tablet-accesses.index')
            ->with('success', 'Accès tablette mis à jour.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $user = User::guests()
            ->where('id', $id)
            ->where('enterprise_id', $this->getEnterpriseId())
            ->firstOrFail();

        $roomNumber = $user->room_number;
        $user->delete();

        return redirect()->route('dashboard.tablet-accesses.index')
            ->with('success', 'Accès tablette supprimé (chambre ' . $roomNumber . ').');
    }

    /**
     * Régénérer le code client (admin / gérant uniquement)
     */
    public function regenerateClientCode(int $id): RedirectResponse
    {
        $user = User::guests()
            ->where('id', $id)
            ->where('enterprise_id', $this->getEnterpriseId())
            ->firstOrFail();

        $newCode = mb_strtoupper(mb_substr(uniqid(), -6));
        $user->update(['client_code' => $newCode]);

        return back()->with('success', 'Nouveau QR Code / Code Client généré : ' . $newCode);
    }
}
