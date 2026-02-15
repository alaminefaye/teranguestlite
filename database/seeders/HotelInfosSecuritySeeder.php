<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enterprise;

class HotelInfosSecuritySeeder extends Seeder
{
    public function run(): void
    {
        $enterprises = Enterprise::all();

        foreach ($enterprises as $enterprise) {
            $settings = is_array($enterprise->settings) ? $enterprise->settings : [];

            if (!isset($settings['hotel_infos'])) {
                $settings['hotel_infos'] = [
                    'wifi_network' => 'TERANGAGUEST',
                    'wifi_password' => 'guest2026',
                    'house_rules' => "Merci de respecter le calme après 22h.\nInterdiction de fumer dans les chambres.",
                    'practical_info' => "Réception 24/7.\nService en chambre disponible de 6h à 23h.",
                ];
            }

            if (!isset($settings['emergency'])) {
                $settings['emergency'] = [
                    'doctor_enabled' => true,
                    'security_enabled' => true,
                ];
            }

            if (!isset($settings['chatbot_url'])) {
                $settings['chatbot_url'] = 'https://assistant.terangaguest.com';
            }

            $enterprise->update(['settings' => $settings]);

            echo "✅ Hotel Infos & Sécurité configurés pour : {$enterprise->name}\n";
        }
    }
}

