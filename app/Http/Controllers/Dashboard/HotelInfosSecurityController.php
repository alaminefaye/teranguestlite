<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class HotelInfosSecurityController extends Controller
{
    public function index(): View
    {
        $enterprise = auth()->user()->enterprise;
        if (!$enterprise) {
            abort(404, 'Établissement non trouvé.');
        }

        $palaceServices = $enterprise->palaceServices()
            ->orderBy('name')
            ->get();

        return view('pages.dashboard.hotel-infos-security.edit', [
            'title' => 'Hotel Infos & Sécurité',
            'enterprise' => $enterprise,
            'palaceServices' => $palaceServices,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $enterprise = auth()->user()->enterprise;
        if (!$enterprise) {
            abort(404, 'Établissement non trouvé.');
        }

        $request->validate([
            'wifi_network' => 'nullable|string|max:255',
            'wifi_password' => 'nullable|string|max:255',
            'house_rules' => 'nullable|string|max:10000',
            'map_file' => 'nullable|image|max:10240',
            'practical_info' => 'nullable|string|max:5000',
            'doctor_enabled' => 'nullable|boolean',
            'security_enabled' => 'nullable|boolean',
            'doctor_service_id' => 'nullable|integer',
            'security_service_id' => 'nullable|integer',
            'chatbot_url' => 'nullable|url|max:500',
        ]);

        $settings = is_array($enterprise->settings) ? $enterprise->settings : [];
        $hotelInfos = $settings['hotel_infos'] ?? [];
        $hotelInfos['wifi_network'] = $request->input('wifi_network', '');
        $hotelInfos['wifi_password'] = $request->input('wifi_password', '');
        $hotelInfos['house_rules'] = $request->input('house_rules', '');
        $hotelInfos['practical_info'] = $request->input('practical_info', '');

        if ($request->hasFile('map_file')) {
            if (!empty($hotelInfos['map_path'])) {
                Storage::disk('public')->delete($hotelInfos['map_path']);
            }
            $hotelInfos['map_path'] = $request->file('map_file')->store('hotel-infos', 'public');
            $hotelInfos['map_url'] = asset('storage/' . $hotelInfos['map_path']);
        }

        $settings['hotel_infos'] = $hotelInfos;
        $settings['emergency'] = [
            'doctor_enabled' => $request->boolean('doctor_enabled', true),
            'security_enabled' => $request->boolean('security_enabled', true),
            'doctor_service_id' => $request->input('doctor_service_id') ?: null,
            'security_service_id' => $request->input('security_service_id') ?: null,
        ];
        $settings['chatbot_url'] = $request->input('chatbot_url') ? trim($request->input('chatbot_url')) : null;

        $enterprise->update(['settings' => $settings]);

        return redirect()->route('dashboard.hotel-infos-security.index')
            ->with('success', 'Hotel Infos & Sécurité enregistrés.');
    }
}
