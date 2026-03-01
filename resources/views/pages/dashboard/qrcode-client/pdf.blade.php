<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>QR Code Accès Client - TeranGuest</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; text-align: center; padding: 24px; }
        h1 { font-size: 20px; margin: 0 0 24px 0; color: #1E252D; }
        .qr-wrapper { position: relative; display: inline-block; margin: 0 auto 20px; }
        .qr-wrapper .qr-svg { display: block; }
        .qr-wrapper .logo-overlay {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 72px;
            height: 72px;
            margin-left: -36px;
            margin-top: -36px;
            background: #fff;
            border-radius: 50%;
            border: 2px solid #e5e7eb;
            padding: 10px;
            box-sizing: border-box;
        }
        .qr-wrapper .logo-overlay img { width: 52px; height: 52px; display: block; }
        .url-block { margin-top: 16px; padding: 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; word-break: break-all; font-size: 11px; color: #374151; }
        .url-label { font-size: 10px; text-transform: uppercase; color: #6b7280; margin-bottom: 4px; }
    </style>
</head>
<body>
    <h1>Votre Accès Client</h1>

    <div class="qr-wrapper">
        <div class="qr-svg">{!! $qrCode !!}</div>
        @if($logoBase64 ?? null)
            <div class="logo-overlay">
                <img src="{{ $logoBase64 }}" alt="TeranGuest">
            </div>
        @endif
    </div>

    <div class="url-block">
        <div class="url-label">Lien de redirection</div>
        <span>{{ $url }}</span>
    </div>
</body>
</html>
