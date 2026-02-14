<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Console\Command;

class TestFirebaseCredentials extends Command
{
    protected $signature = 'fcm:test
                            {--user= : User ID with fcm_token to send a test notification}
                            {--curl : Affiche une commande curl pour tester FCM depuis ce serveur (avec --user=ID pour le device token)}';

    protected $description = 'Vérifie le chargement des credentials Firebase et optionnellement envoie une notification test';

    public function handle(): int
    {
        $this->info('Vérification Firebase...');

        try {
            app('firebase');
            $this->info('Credentials chargés avec succès.');
        } catch (\Throwable $e) {
            $this->error('Erreur chargement credentials: ' . $e->getMessage());
            $this->line('Vérifiez FIREBASE_CREDENTIALS dans .env et que le fichier existe (racine ou storage/app/firebase/).');
            return 1;
        }

        if ($this->option('curl')) {
            return $this->outputCurlCommand();
        }

        $userId = $this->option('user');
        if ($userId) {
            $user = User::find($userId);
            if (! $user || empty($user->fcm_token)) {
                $this->warn("User {$userId} non trouvé ou sans fcm_token. Utilisez un ID d'utilisateur qui a un token (ex. compte tablette connecté).");
                return 1;
            }
            $this->info("Envoi d'une notification test à l'utilisateur {$user->id} ({$user->name})...");
            try {
                $sent = app(FirebaseNotificationService::class)->sendToUser(
                    $user,
                    'Test TerangaGuest',
                    'Si vous voyez ce message, les notifications push sont opérationnelles.'
                );
                if ($sent) {
                    $this->info('Notification envoyée avec succès.');
                } else {
                    $this->warn('Envoi a échoué (voir storage/logs/laravel.log).');
                }
            } catch (\Throwable $e) {
                $this->error('Erreur envoi: ' . $e->getMessage());
                return 1;
            }
        } else {
            $this->line('Pour envoyer une notification test : php artisan fcm:test --user=6');
            $this->line('Pour afficher une commande curl de test : php artisan fcm:test --curl --user=6');
        }

        return 0;
    }

    /**
     * Affiche une commande curl prête à l'emploi pour tester FCM depuis ce serveur.
     * Si curl renvoie 200 → le proxy ne touche pas à Authorization vers Google ; sinon (401) → proxy ou règle ciblant googleapis.com.
     */
    private function outputCurlCommand(): int
    {
        if (! app()->bound('firebase.fcm.get_token')) {
            $this->warn('Token FCM direct non disponible (fallback SDK utilisé). Impossible d\'afficher la commande curl.');
            return 0;
        }

        $userId = $this->option('user');
        $deviceToken = null;
        if ($userId) {
            $user = User::find($userId);
            if ($user && ! empty($user->fcm_token)) {
                $deviceToken = $user->fcm_token;
            }
        }
        if (! $deviceToken) {
            $deviceToken = '<COLLER_ICI_LE_FCM_TOKEN_DEVICE>';
        }

        $accessToken = app('firebase.fcm.get_token');
        $projectId = app('firebase.fcm.project_id');
        $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';
        $body = json_encode([
            'message' => [
                'token' => $deviceToken,
                'notification' => ['title' => 'Test curl', 'body' => 'Si vous voyez ceci, FCM fonctionne depuis ce serveur.'],
            ],
        ]);

        $this->line('');
        $this->info('Commande curl à exécuter sur CE serveur (test direct vers FCM) :');
        $this->line('');
        $this->line('curl -s -w "\\nHTTP_CODE:%{http_code}" -X POST "' . $url . '" \\');
        $this->line('  -H "Authorization: Bearer ' . $accessToken . '" \\');
        $this->line('  -H "Content-Type: application/json" \\');
        $this->line("  -d '" . $body . "'");
        $this->line('');
        $this->comment('Si HTTP_CODE:200 → FCM reçoit bien le header ; le souci vient peut-être de PHP/Guzzle.');
        $this->comment('Si HTTP_CODE:401 → Authorization est supprimé ou refusé vers fcm.googleapis.com (proxy/règle).');
        $this->line('');

        return 0;
    }
}
