<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Log;

class HotelAssistantService
{
    public function __construct(protected HttpFactory $http)
    {
    }

    public function chat(User $user, string $message, ?string $locale = null): array
    {
        $enterprise = $user->enterprise;

        $context = [
            'hotel_name' => $enterprise?->name,
            'city' => $enterprise?->city,
            'country' => $enterprise?->country,
            'wifi_network' => $enterprise?->hotel_infos['wifi_network'] ?? null,
            'wifi_password' => $enterprise?->hotel_infos['wifi_password'] ?? null,
            'house_rules' => $enterprise?->hotel_infos['house_rules'] ?? null,
            'practical_info' => $enterprise?->hotel_infos['practical_info'] ?? null,
            'room_number' => $user->room_number,
            'locale' => $locale,
        ];

        $provider = config('services.ai_chat.provider', 'external');

        if ($provider === 'none' || !config('services.ai_chat.base_url')) {
            return $this->offlineFallback($context, $message);
        }

        try {
            $response = $this->http->withHeaders([
                'Authorization' => config('services.ai_chat.api_key') ? 'Bearer ' . config('services.ai_chat.api_key') : null,
                'Accept' => 'application/json',
            ])->post(rtrim(config('services.ai_chat.base_url'), '/') . '/chat', [
                'message' => $message,
                'locale' => $locale,
                'context' => $context,
                'model' => config('services.ai_chat.model'),
            ]);

            if ($response->ok()) {
                $data = $response->json();
                return [
                    'reply' => $data['reply'] ?? '',
                    'metadata' => $data['metadata'] ?? [],
                ];
            }

            Log::error('AI chat error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $this->offlineFallback($context, $message);
        } catch (\Throwable $e) {
            Log::error('AI chat exception', [
                'message' => $e->getMessage(),
            ]);

            return $this->offlineFallback($context, $message);
        }
    }

    protected function offlineFallback(array $context, string $message): array
    {
        $language = $context['locale'] ?? 'fr';
        $hotelName = $context['hotel_name'] ?: 'votre hôtel';

        if ($language === 'en') {
            $reply = "I am a digital assistant for {$hotelName}. I do not have internet access right now, but you can ask the reception for detailed information about Wi‑Fi, services, and emergency assistance.";
        } else {
            $reply = "Je suis l’assistant numérique de {$hotelName}. Je n’ai pas accès au service d’IA complet pour le moment. Pour toute question détaillée (Wi‑Fi, services, assistance d’urgence), merci de contacter la réception.";
        }

        return [
            'reply' => $reply,
            'metadata' => [
                'mode' => 'fallback',
            ],
        ];
    }
}
