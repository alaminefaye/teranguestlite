<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HotelAssistantService;
use Illuminate\Http\Request;

class HotelAssistantController extends Controller
{
    public function __construct(protected HotelAssistantService $assistant)
    {
    }

    public function chat(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'locale' => ['nullable', 'string', 'max:10'],
        ]);

        $result = $this->assistant->chat(
            $user,
            $validated['message'],
            $validated['locale'] ?? $request->getLocale()
        );

        return response()->json([
            'success' => true,
            'data' => [
                'reply' => $result['reply'],
                'metadata' => $result['metadata'],
            ],
        ]);
    }
}

