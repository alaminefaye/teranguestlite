<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeClientController extends Controller
{
    public function index(Request $request)
    {
        $code = trim((string) $request->input('code'));

        $url = url('/client');
        if ($code) {
            $url .= '?code=' . urlencode($code);
        }

        // Generate SVG QR Code with High error correction ('H' = 30%)
        // so the center logo overlay doesn't break scannability.
        // Style: 'square' + 'square' eyes = clean, classic look (change to 'round'/'circle' for a softer style).
        $qrCode = QrCode::size(340)
            ->errorCorrection('H')
            ->margin(1)
            ->color(30, 37, 45) // AppTheme.primaryDark #1E252D
            ->backgroundColor(255, 255, 255, 0) // Transparent
            ->style('square') // square = classic; options: square, dot, round
            ->eye('square')   // square = classic; options: square, circle
            ->generate($url);

        return view('pages.dashboard.qrcode-client.index', compact('qrCode', 'code', 'url'));
    }
}
