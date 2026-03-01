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
        $qrCode = QrCode::size(300)
            ->errorCorrection('H')
            ->margin(1)
            ->generate($url);

        return view('pages.dashboard.qrcode-client.index', compact('qrCode', 'code', 'url'));
    }
}
