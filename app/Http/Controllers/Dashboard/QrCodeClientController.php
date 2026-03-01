<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeClientController extends Controller
{
    private function buildQrData(Request $request): array
    {
        $code = trim((string) $request->input('code'));
        $url = url('/client');
        if ($code) {
            $url .= '?code=' . urlencode($code);
        }
        $qrCode = QrCode::size(340)
            ->errorCorrection('H')
            ->margin(1)
            ->color(30, 37, 45)
            ->backgroundColor(255, 255, 255, 0)
            ->style('square')
            ->eye('square')
            ->generate($url);

        return compact('qrCode', 'code', 'url');
    }

    public function index(Request $request)
    {
        $data = $this->buildQrData($request);

        return view('pages.dashboard.qrcode-client.index', $data);
    }

    /**
     * Télécharger le QR Code client en PDF.
     * On génère le QR en PNG (base64) pour le PDF car DomPDF n'affiche pas correctement le SVG inline.
     */
    public function pdf(Request $request)
    {
        $data = $this->buildQrData($request);

        $logoPath = public_path('images/logo/logo.png');
        $logoBase64 = null;
        if (is_file($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }
        $data['logoBase64'] = $logoBase64;

        // QR en image PNG pour que DomPDF l'affiche (SVG non fiable dans le PDF)
        $qrCodePngBase64 = null;
        try {
            if (extension_loaded('imagick')) {
                $pngBlob = QrCode::format('png')
                    ->size(340)
                    ->errorCorrection('H')
                    ->margin(1)
                    ->color(30, 37, 45)
                    ->backgroundColor(255, 255, 255)
                    ->style('square')
                    ->eye('square')
                    ->generate($data['url']);
                $qrCodePngBase64 = $pngBlob ? base64_encode($pngBlob) : null;
            }
        } catch (\Throwable $e) {
            // imagick non dispo ou erreur : on garde null, la vue utilisera le SVG en secours
        }

        if ($qrCodePngBase64) {
            $data['qrCodePngBase64'] = $qrCodePngBase64;
            $data['qrCodeSvgDataUri'] = null;
        } else {
            $data['qrCodePngBase64'] = null;
            $data['qrCodeSvgDataUri'] = 'data:image/svg+xml;base64,' . base64_encode($data['qrCode']);
        }

        $pdf = Pdf::loadView('pages.dashboard.qrcode-client.pdf', $data)
            ->setPaper('a4', 'portrait');

        $filename = $data['code']
            ? 'qrcode-acces-client-' . $data['code'] . '.pdf'
            : 'qrcode-acces-client.pdf';

        return $pdf->download($filename);
    }
}
