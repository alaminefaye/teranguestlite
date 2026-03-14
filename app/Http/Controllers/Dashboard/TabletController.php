<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Tablet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TabletController extends Controller
{
    public function index(): View
    {
        $tablets = Tablet::with('room')
            ->join('rooms', 'tablets.room_id', '=', 'rooms.id')
            ->orderBy('rooms.room_number')
            ->select('tablets.*')
            ->paginate(15);

        return view('pages.dashboard.tablets.index', [
            'title' => 'Tablettes',
            'tablets' => $tablets,
        ]);
    }

    public function create(): View
    {
        $enterpriseId = auth()->user()->enterprise_id;
        $roomsWithTablet = Tablet::where('enterprise_id', $enterpriseId)->pluck('room_id');
        $rooms = Room::where('enterprise_id', $enterpriseId)
            ->whereNotIn('id', $roomsWithTablet)
            ->orderBy('room_number')
            ->get();

        return view('pages.dashboard.tablets.create', [
            'title' => 'Ajouter une tablette',
            'rooms' => $rooms,
            'roomTypeLabels' => Room::roomTypeLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'name' => 'nullable|string|max:255',
        ]);

        $enterpriseId = auth()->user()->enterprise_id;
        $room = Room::where('id', $validated['room_id'])->where('enterprise_id', $enterpriseId)->firstOrFail();

        if (Tablet::where('enterprise_id', $enterpriseId)->where('room_id', $room->id)->exists()) {
            return back()->withInput()->with('error', 'Cette chambre a déjà une tablette associée.');
        }

        Tablet::create([
            'enterprise_id' => $enterpriseId,
            'room_id' => $room->id,
            'name' => $validated['name'] ?: null,
        ]);

        return redirect()->route('dashboard.tablets.index')
            ->with('success', 'Tablette créée et reliée à la chambre ' . $room->room_number . '.');
    }

    public function destroy(Tablet $tablet): RedirectResponse
    {
        $this->authorizeTablet($tablet);
        $roomNumber = $tablet->room->room_number ?? '';
        $tablet->delete();
        return redirect()->route('dashboard.tablets.index')
            ->with('success', 'Tablette retirée de la chambre ' . $roomNumber . '.');
    }

    private function authorizeTablet(Tablet $tablet): void
    {
        if ($tablet->enterprise_id !== auth()->user()->enterprise_id) {
            abort(403);
        }
    }
}
