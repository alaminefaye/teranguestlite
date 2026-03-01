@extends('layouts.app')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">QR Code App Web</h1>
            <p class="text-gray-600 dark:text-gray-400">Générez un QR Code personnalisé avec ou sans code client pour
                l'accès web direct.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <!-- Generateur Form -->
        <div
            class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 self-start">
            <h3 class="font-medium text-gray-900 dark:text-white mb-4 text-lg">Générateur de QR Code</h3>
            <form method="GET" action="{{ route('dashboard.qrcode-client.index') }}" class="space-y-4">
                <div>
                    <label class="mb-2.5 block text-gray-900 dark:text-white font-medium">Lier à un Code Client
                        (Optionnel)</label>
                    <input type="text" name="code" value="{{ $code }}" placeholder="Ex: ABCDEF" maxlength="6"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 text-gray-900 dark:text-white focus:border-brand-500 focus:ring-brand-500 outline-none uppercase tracking-widest text-lg">
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Si vide, le QR Code redirige simplement vers la page de connexion web où le client pourra saisir son
                        code manuellement.
                    </p>
                </div>
                <button type="submit"
                    class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-md shadow-sm font-medium text-white bg-brand-500 hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    Générer / Appliquer le code
                </button>
                @if($code)
                    <a href="{{ route('dashboard.qrcode-client.index') }}"
                        class="w-full flex justify-center text-sm text-brand-500 hover:underline mt-2">Réinitialiser</a>
                @endif
            </form>
        </div>

        <!-- Rendered QR Code -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 flex flex-col items-center text-center"
            id="qrCodeContainer">
            <h3 class="font-medium text-gray-900 dark:text-white mb-6 text-lg">Votre QR Code</h3>

            <div
                class="relative inline-flex bg-white p-4 rounded-xl shadow-sm border border-gray-100 dark:bg-whiteQRBackground">
                <!-- QR Code SVG -->
                {!! $qrCode !!}

                <!-- Logo Overlay in the center (absolutely positioned over SVG) -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="bg-white p-1 rounded-full shadow-[0_2px_8px_rgba(0,0,0,0.15)] flex items-center justify-center"
                        style="width: 60px; height: 60px;">
                        <img src="{{ asset('images/logo/logo.png') }}" class="w-11 h-11 object-contain rounded-full"
                            alt="Teranga Guest" onerror="this.style.display='none'">
                    </div>
                </div>
            </div>

            <div class="mt-8 w-full">
                <p
                    class="text-sm font-medium text-gray-900 dark:text-white break-all bg-gray-50 dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700">
                    <span class="text-gray-500 block mb-1 text-xs uppercase tracking-wider">Lien de redirection :</span>
                    <a href="{{ $url }}" target="_blank"
                        class="text-brand-500 hover:text-brand-600 hover:underline">{{ $url }}</a>
                </p>
                <div class="mt-4 flex flex-col sm:flex-row gap-3 justify-center qr-actions">
                    <button onclick="window.print()"
                        class="inline-flex justify-center items-center px-6 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors border border-gray-200 dark:border-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                            </path>
                        </svg>
                        Imprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .dark \.dark\\:bg-whiteQRBackground {
            background-color: white !important;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #qrCodeContainer,
            #qrCodeContainer * {
                visibility: visible;
            }

            #qrCodeContainer {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none;
                box-shadow: none;
                margin: 0;
                padding: 20px;
                background: white !important;
                color: black !important;
            }

            .qr-actions,
            h3,
            .dark\:text-white {
                display: none !important;
            }

            #qrCodeContainer h3:first-child {
                display: block !important;
                margin-bottom: 30px;
                font-size: 24pt;
                color: black !important;
            }
        }
    </style>
@endsection