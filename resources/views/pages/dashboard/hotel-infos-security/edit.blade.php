@extends('layouts.app')

@section('content')
@php
    $infos = $enterprise->hotel_infos;
    $emergency = $enterprise->emergency;
@endphp
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.index') }}" class="hover:text-brand-500">Dashboard</a>
        <span>/</span>
        <span>Hotel Infos & Sécurité</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Hotel Infos & Sécurité</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Livret d'accueil, assistance & urgence, et chatbot. Ces réglages sont affichés dans l'application mobile.</p>
</div>

@if (session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">
        {{ session('success') }}
    </div>
@endif

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.hotel-infos-security.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="space-y-8">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Hôtel Infos (livret d'accueil)</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Codes Wi-Fi, plans, règlement intérieur et informations pratiques.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="wifi_network" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom du réseau Wi-Fi</label>
                        <input type="text" name="wifi_network" id="wifi_network" value="{{ old('wifi_network', $infos['wifi_network'] ?? '') }}" placeholder="Ex: KingFahd_Guest"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                        @error('wifi_network')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="wifi_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mot de passe Wi-Fi</label>
                        <input type="text" name="wifi_password" id="wifi_password" value="{{ old('wifi_password', $infos['wifi_password'] ?? '') }}" placeholder="Ex: Bienvenue2024"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                        @error('wifi_password')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="mt-4">
                    <label for="house_rules" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Règlement intérieur (texte)</label>
                    <textarea name="house_rules" id="house_rules" rows="4" placeholder="Règles de la maison, horaires..."
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('house_rules', $infos['house_rules'] ?? '') }}</textarea>
                    @error('house_rules')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>
                <div class="mt-4">
                    <label for="map_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Plan de l'hôtel (image)</label>
                    @if(!empty($infos['map_url']))
                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">Image actuelle : <a href="{{ $infos['map_url'] }}" target="_blank" class="text-brand-500">Voir</a></p>
                    @endif
                    <input type="file" name="map_file" id="map_file" accept="image/*"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Max 10 Mo. Laisser vide pour conserver l'image actuelle.</p>
                    @error('map_file')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>
                <div class="mt-4">
                    <label for="practical_info" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Informations pratiques</label>
                    <textarea name="practical_info" id="practical_info" rows="4" placeholder="Check-in/out, petit-déjeuner, parking..."
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('practical_info', $infos['practical_info'] ?? '') }}</textarea>
                    @error('practical_info')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Assistance & Urgence</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Activer les boutons « Médecin » et « Urgence sécurité » dans l'app et choisir le service Palace utilisé pour chaque bouton.
                </p>
                <div class="space-y-6">
                    <div class="flex flex-wrap items-center gap-4">
                        <label class="inline-flex items-center">
                            <input type="hidden" name="doctor_enabled" value="0">
                            <input type="checkbox" name="doctor_enabled" value="1" {{ old('doctor_enabled', $emergency['doctor_enabled'] ?? true) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Assistance médecin</span>
                        </label>
                        <div class="min-w-[260px]">
                            <label for="doctor_service_id" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                Service utilisé pour « Assistance médecin »
                            </label>
                            <select
                                name="doctor_service_id"
                                id="doctor_service_id"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500"
                            >
                                <option value="">Choisir un service...</option>
                                @foreach($palaceServices as $service)
                                    <option value="{{ $service->id }}"
                                        {{ (string) old('doctor_service_id', $emergency['doctor_service_id'] ?? '') === (string) $service->id ? 'selected' : '' }}>
                                        {{ $service->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <label class="inline-flex items-center">
                            <input type="hidden" name="security_enabled" value="0">
                            <input type="checkbox" name="security_enabled" value="1" {{ old('security_enabled', $emergency['security_enabled'] ?? true) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Urgence sécurité</span>
                        </label>
                        <div class="min-w-[260px]">
                            <label for="security_service_id" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                Service utilisé pour « Urgence sécurité »
                            </label>
                            <select
                                name="security_service_id"
                                id="security_service_id"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500"
                            >
                                <option value="">Choisir un service...</option>
                                @foreach($palaceServices as $service)
                                    <option value="{{ $service->id }}"
                                        {{ (string) old('security_service_id', $emergency['security_service_id'] ?? '') === (string) $service->id ? 'selected' : '' }}>
                                        {{ $service->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Chatbot IA Multilingue</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Assistant digital 24/7. Indiquez l'URL du chatbot pour l'afficher dans l'app (sinon « Bientôt disponible »).</p>
                <div>
                    <label for="chatbot_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">URL du chatbot</label>
                    <input type="url" name="chatbot_url" id="chatbot_url" value="{{ old('chatbot_url', $enterprise->chatbot_url ?? '') }}" placeholder="https://..."
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('chatbot_url')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="mt-8 flex items-center gap-3">
            <button type="submit" class="rounded-md bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                Enregistrer
            </button>
            <a href="{{ route('dashboard.index') }}" class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">Annuler</a>
        </div>
    </form>
</div>
@endsection
