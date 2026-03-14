<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslationService
{
    protected string $driver;
    protected int $timeout;

    public function __construct()
    {
        $this->driver = config('translation.driver', 'deepl');
        $this->timeout = config('translation.timeout_seconds', 5);
    }

    /**
     * Traduit un texte de la langue source vers la langue cible.
     * Retourne null en cas d'erreur (jamais d'exception pour éviter les 500).
     */
    public function translate(string $text, string $targetLang, string $sourceLang = 'fr'): ?string
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        if ($this->driver === 'deepl') {
            return $this->translateWithDeepL($text, $targetLang, $sourceLang);
        }

        return $this->translateWithGoogle($text, $targetLang, $sourceLang);
    }

    protected function translateWithDeepL(string $text, string $targetLang, string $sourceLang): ?string
    {
        $apiKey = config('translation.deepl.api_key');
        if (empty($apiKey)) {
            Log::warning('TranslationService: DEEPL_API_KEY non configurée.');
            return $this->translateWithGoogle($text, $targetLang, $sourceLang);
        }

        $baseUrl = rtrim(config('translation.deepl.base_url', 'https://api-free.deepl.com'), '/');
        $url = $baseUrl . '/v2/translate';

        $targetLang = $this->toDeepLLang($targetLang);
        $sourceLang = $this->toDeepLLang($sourceLang);

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'DeepL-Auth-Key ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'text' => [$text],
                    'target_lang' => $targetLang,
                    'source_lang' => $sourceLang,
                ]);

            if (! $response->successful()) {
                Log::warning('TranslationService DeepL error: ' . $response->status() . ' ' . $response->body());
                return null;
            }

            $data = $response->json();
            $translated = $data['translations'][0]['text'] ?? null;

            return is_string($translated) ? trim($translated) : null;
        } catch (\Throwable $e) {
            Log::warning('TranslationService DeepL exception: ' . $e->getMessage());
            return null;
        }
    }

    protected function toDeepLLang(string $lang): string
    {
        return match (strtolower($lang)) {
            'en' => 'EN',
            'es' => 'ES',
            'ar' => 'AR',
            'fr' => 'FR',
            'de' => 'DE',
            'it' => 'IT',
            default => strtoupper($lang),
        };
    }

    protected function translateWithGoogle(string $text, string $targetLang, string $sourceLang): ?string
    {
        try {
            $translated = GoogleTranslate::trans($text, $targetLang, $sourceLang);
            return is_string($translated) && trim($translated) !== '' ? trim($translated) : null;
        } catch (\Throwable $e) {
            Log::warning('TranslationService Google exception: ' . $e->getMessage());
            return null;
        }
    }
}
