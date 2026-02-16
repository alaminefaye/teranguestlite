<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enterprise;
use App\Models\PalaceService;

class HotelInfosSecuritySeeder extends Seeder
{
    public function run(): void
    {
        $enterprises = Enterprise::all();

        foreach ($enterprises as $enterprise) {
            $settings = is_array($enterprise->settings) ? $enterprise->settings : [];

            $hotelInfos = $settings['hotel_infos'] ?? [];
            if (!isset($hotelInfos['wifi_network'])) {
                $hotelInfos['wifi_network'] = 'TERANGAGUEST';
            }
            if (!isset($hotelInfos['wifi_password'])) {
                $hotelInfos['wifi_password'] = 'guest2026';
            }
            if (!isset($hotelInfos['house_rules'])) {
                $hotelInfos['house_rules'] = "Merci de respecter le calme après 22h.\nInterdiction de fumer dans les chambres.";
            }
            if (!isset($hotelInfos['practical_info'])) {
                $hotelInfos['practical_info'] = "Réception 24/7.\nService en chambre disponible de 6h à 23h.";
            }
            $settings['hotel_infos'] = $hotelInfos;

            $emergency = $settings['emergency'] ?? [];
            $emergency['doctor_enabled'] = $emergency['doctor_enabled'] ?? true;
            $emergency['security_enabled'] = $emergency['security_enabled'] ?? true;

            $doctorService = PalaceService::where('enterprise_id', $enterprise->id)
                ->where(function ($q) {
                    $q->where('name', 'like', '%médecin%')
                      ->orWhere('name', 'like', '%medecin%')
                      ->orWhere('name', 'like', '%doctor%')
                      ->orWhere('name', 'like', '%docteur%');
                })
                ->first();

            if (!$doctorService) {
                $doctorService = PalaceService::create([
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
            }

            $securityService = PalaceService::where('enterprise_id', $enterprise->id)
                ->where(function ($q) {
                    $q->where('name', 'like', '%urgence%')
                      ->orWhere('name', 'like', '%sécurité%')
                      ->orWhere('name', 'like', '%securite%')
                      ->orWhere('name', 'like', '%security%')
                      ->orWhere('name', 'like', '%emergency%');
                })
                ->first();

            if (!$securityService) {
                $securityService = PalaceService::create([
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
            }

            $emergency['doctor_service_id'] = $doctorService->id;
            $emergency['security_service_id'] = $securityService->id;
            $settings['emergency'] = $emergency;

            if (!isset($settings['chatbot_url'])) {
                $settings['chatbot_url'] = 'https://assistant.terangaguest.com';
            }

            $enterprise->update(['settings' => $settings]);

            echo "✅ Hotel Infos & Sécurité configurés pour : {$enterprise->name}\n";
        }
    }
}
