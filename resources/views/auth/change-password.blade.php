<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changement de mot de passe - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Logo / Header -->
            <div class="text-center mb-8">
                <div class="mx-auto h-16 w-16 bg-brand-500 rounded-full flex items-center justify-center mb-4">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Changement de mot de passe
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Pour des raisons de sécurité, vous devez changer votre mot de passe
                </p>
            </div>

            <!-- Alert message -->
            @if(session('warning'))
                <div class="mb-6 rounded-lg bg-warning-50 border border-warning-200 p-4 dark:bg-warning-500/10 dark:border-warning-700">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-warning-600 dark:text-warning-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <p class="text-sm text-warning-700 dark:text-warning-400">{{ session('warning') }}</p>
                    </div>
                </div>
            @endif

            <!-- Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8">
                <!-- Important Notice -->
                <div class="mb-6 rounded-lg bg-brand-50 border border-brand-200 p-4 dark:bg-brand-500/10 dark:border-brand-700">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-brand-600 dark:text-brand-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-sm text-brand-700 dark:text-brand-300">
                            <p class="font-semibold mb-1">Première connexion détectée</p>
                            <p>Veuillez créer un nouveau mot de passe sécurisé. Ce mot de passe doit :</p>
                            <ul class="list-disc ml-5 mt-2 space-y-1">
                                <li>Contenir au moins 8 caractères</li>
                                <li>Être différent du mot de passe actuel</li>
                                <li>Être facile à mémoriser pour vous</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('auth.change-password.update') }}" class="space-y-6">
                    @csrf

                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Mot de passe actuel
                        </label>
                        <input 
                            id="current_password" 
                            name="current_password" 
                            type="password" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:text-white @error('current_password') border-error-500 @enderror"
                            placeholder="Entrez votre mot de passe actuel"
                        >
                        @error('current_password')
                            <p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nouveau mot de passe
                        </label>
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:text-white @error('password') border-error-500 @enderror"
                            placeholder="Entrez votre nouveau mot de passe"
                        >
                        @error('password')
                            <p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Confirmer le nouveau mot de passe
                        </label>
                        <input 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            type="password" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:text-white"
                            placeholder="Confirmez votre nouveau mot de passe"
                        >
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        class="w-full flex justify-center items-center px-4 py-3 bg-brand-500 text-white font-medium rounded-lg hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Changer mon mot de passe
                    </button>
                </form>

                <!-- Logout option -->
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 text-center">
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                            Se déconnecter
                        </button>
                    </form>
                </div>
            </div>

            <!-- Security tips -->
            <div class="mt-6 text-center text-xs text-gray-500 dark:text-gray-400">
                <p>💡 Conseil : Utilisez un mot de passe unique que vous n'utilisez nulle part ailleurs</p>
            </div>
        </div>
    </div>
</body>
</html>
