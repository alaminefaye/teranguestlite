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
        // to allow a logo safely placed in the middle without breaking scannability.
        // We also use a custom styling: round or dot style, colored in the brand primary/dark.
        $qrCode = QrCode::size(340)
            ->errorCorrection('H')
            ->margin(1)
            ->color(30, 37, 45) // AppTheme.primaryDark #1E252D approximate RGB
            ->backgroundColor(255, 255, 255, 0) // Transparent background
            ->style('round') // Valid styles: square, dot, round
            ->eye('circle') // Valid eye styles: square, circle
            ->generate($url);

        return view('pages.dashboard.qrcode-client.index', compact('qrCode', 'code', 'url'));
    }
}
