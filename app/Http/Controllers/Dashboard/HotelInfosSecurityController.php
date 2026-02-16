<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Enterprise;
use App\Models\PalaceService;
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

        return view('pages.dashboard.hotel-infos-security.edit', [
            'title' => 'Hotel Infos & Sécurité',
            'enterprise' => $enterprise,
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
        ];
        $settings['chatbot_url'] = $request->input('chatbot_url') ? trim($request->input('chatbot_url')) : null;

        $enterprise->update(['settings' => $settings]);

        $this->syncEmergencyPalaceServices($enterprise, $settings['emergency']);

        return redirect()->route('dashboard.hotel-infos-security.index')
            ->with('success', 'Hotel Infos & Sécurité enregistrés.');
    }

    private function syncEmergencyPalaceServices(Enterprise $enterprise, array $emergency): void
    {
        $doctorEnabled = (bool) ($emergency['doctor_enabled'] ?? false);
        $securityEnabled = (bool) ($emergency['security_enabled'] ?? false);

        $doctorService = PalaceService::where('enterprise_id', $enterprise->id)
            ->where(function ($q) {
                $q->where('category', 'concierge')
                  ->orWhereNull('category');
            })
            ->where(function ($q) {
                $q->where('name', 'like', '%médecin%')
                  ->orWhere('name', 'like', '%medecin%')
                  ->orWhere('name', 'like', '%doctor%')
                  ->orWhere('name', 'like', '%docteur%');
            })
            ->first();

        if ($doctorEnabled) {
            if (!$doctorService) {
                PalaceService::create([
                    'enterprise_id' => $enterprise->id,
                    'name' => 'Assistance médecin',
                    'category' => 'concierge',
                    'description' => 'Assistance médicale pour les clients de l’hôtel.',
                    'image' => null,
                    'price' => 0,
                    'price_on_request' => true,
                    'status' => 'available',
                    'is_premium' => false,
                    'display_order' => 0,
                ]);
            } else {
                $doctorService->update(['status' => 'available']);
            }
        } elseif ($doctorService) {
            $doctorService->update(['status' => 'unavailable']);
        }

        $securityService = PalaceService::where('enterprise_id', $enterprise->id)
            ->where(function ($q) {
                $q->where('category', 'concierge')
                  ->orWhereNull('category');
            })
            ->where(function ($q) {
                $q->where('name', 'like', '%urgence%')
                  ->orWhere('name', 'like', '%sécurité%')
                  ->orWhere('name', 'like', '%securite%')
                  ->orWhere('name', 'like', '%security%')
                  ->orWhere('name', 'like', '%emergency%');
            })
            ->first();

        if ($securityEnabled) {
            if (!$securityService) {
                PalaceService::create([
                    'enterprise_id' => $enterprise->id,
                    'name' => 'Urgence sécurité',
                    'category' => 'concierge',
                    'description' => 'Urgence sécurité pour les clients de l’hôtel.',
                    'image' => null,
                    'price' => 0,
                    'price_on_request' => true,
                    'status' => 'available',
                    'is_premium' => false,
                    'display_order' => 0,
                ]);
            } else {
                $securityService->update(['status' => 'available']);
            }
        } elseif ($securityService) {
            $securityService->update(['status' => 'unavailable']);
        }
    }
}
