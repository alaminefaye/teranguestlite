@extends('layouts.guest')

@section('content')
<div class="space-y-6">
    <!-- Welcome Message -->
    <div class="bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl p-6 text-white">
        <h2 class="text-2xl font-bold mb-2">Bienvenue, {{ auth()->user()->name }} !</h2>
        <p class="text-brand-100">Nous sommes ravis de vous accueillir dans notre établissement.</p>
    </div>

    <!-- Quick Actions -->
    <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Services rapides</h3>
        <div class="grid grid-cols-2 gap-4">
            <!-- Room Service -->
            <a href="{{ route('guest.room-service.index') }}" class="tablet-card bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md border border-gray-200 dark:border-gray-700">
                <div class="flex flex-col items-center text-center gap-3">
                    <div class="bg-brand-100 dark:bg-brand-900 rounded-full p-4">
                        <svg class="w-8 h-8 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800 dark:text-white/90">Room Service</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Commander vos repas</p>
                    </div>
                </div>
            </a>

            <!-- Mes Commandes -->
            <a href="{{ route('guest.orders.index') }}" class="tablet-card bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md border border-gray-200 dark:border-gray-700">
                <div class="flex flex-col items-center text-center gap-3">
                    <div class="bg-success-100 dark:bg-success-900 rounded-full p-4">
                        <svg class="w-8 h-8 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800 dark:text-white/90">Mes Commandes</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Suivre mes commandes</p>
                    </div>
                </div>
            </a>

            <!-- Restaurants (Coming soon) -->
            <a href="#" class="tablet-card bg-gray-100 dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 opacity-60 cursor-not-allowed">
                <div class="flex flex-col items-center text-center gap-3">
                    <div class="bg-gray-200 dark:bg-gray-700 rounded-full p-4">
                        <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-600 dark:text-gray-400">Restaurants</h4>
                        <p class="text-sm text-gray-500">Bientôt disponible</p>
                    </div>
                </div>
            </a>

            <!-- Services (Coming soon) -->
            <a href="#" class="tablet-card bg-gray-100 dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 opacity-60 cursor-not-allowed">
                <div class="flex flex-col items-center text-center gap-3">
                    <div class="bg-gray-200 dark:bg-gray-700 rounded-full p-4">
                        <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-600 dark:text-gray-400">Autres Services</h4>
                        <p class="text-sm text-gray-500">Bientôt disponible</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Information Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Informations</h3>
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <div class="bg-gray-100 dark:bg-gray-700 rounded-full p-2">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Votre chambre</p>
                    <p class="font-semibold text-gray-800 dark:text-white/90">Chambre {{ auth()->user()->room_number ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="bg-gray-100 dark:bg-gray-700 rounded-full p-2">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Heure locale</p>
                    <p class="font-semibold text-gray-800 dark:text-white/90" id="current-time">--:--</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="bg-gray-100 dark:bg-gray-700 rounded-full p-2">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Contact</p>
                    <p class="font-semibold text-gray-800 dark:text-white/90">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Afficher l'heure en temps réel
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        document.getElementById('current-time').textContent = timeString;
    }
    
    updateTime();
    setInterval(updateTime, 1000);
</script>
@endsection
