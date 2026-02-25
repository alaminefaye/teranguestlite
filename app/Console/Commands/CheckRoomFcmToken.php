<?php

namespace App\Console\Commands;

use App\Models\Room;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Console\Command;

class CheckRoomFcmToken extends Command
{
    protected $signature = 'fcm:check-room {room_id? : ID or room number (e.g. 101)}';

    protected $description = 'Vérifie si une chambre a un compte tablette avec token FCM (pour debug notifications push)';

    public function handle(): int
    {
        $roomIdOrNumber = $this->argument('room_id');

        if (!$roomIdOrNumber) {
            $this->listRoomsWithTokens();
            return 0;
        }

        $room = is_numeric($roomIdOrNumber)
            ? Room::withoutGlobalScope('enterprise')->find($roomIdOrNumber)
            : Room::withoutGlobalScope('enterprise')->where('room_number', $roomIdOrNumber)->first();

        if (!$room) {
            $this->error("Chambre non trouvée : {$roomIdOrNumber}");
            return 1;
        }

        $userByRoomId = User::where('enterprise_id', $room->enterprise_id)
            ->where('role', 'guest')
            ->where('room_id', $room->id)
            ->first();

        $userByRoomNumber = null;
        if (!$userByRoomId) {
            $userByRoomNumber = User::where('enterprise_id', $room->enterprise_id)
                ->where('role', 'guest')
                ->where('room_number', $room->room_number)
                ->first();
        }

        $user = $userByRoomId ?? $userByRoomNumber;

        $this->info("Chambre : {$room->room_number} (id={$room->id}), entreprise_id={$room->enterprise_id}");
        $this->newLine();

        if (!$user) {
            $this->warn('Aucun compte tablette (role=guest) lié à cette chambre.');
            $this->line('→ Créez un accès tablette pour cette chambre (Dashboard > Accès tablettes).');
            return 0;
        }

        $this->line("Compte tablette : {$user->name} (user_id={$user->id})");
        $this->line("  room_id    : " . ($user->room_id ?? 'null'));
        $this->line("  room_number: " . ($user->room_number ?? 'null'));
        $hasToken = $user->fcmTokens()->exists();
        $this->line("  fcm_token_count: " . $user->fcmTokens()->count());
        $this->line("  fcmTokens      : " . ($hasToken ? '***enregistré(s)***' : 'aucun'));

        if (!$hasToken) {
            $this->newLine();
            $this->warn('Aucun token FCM. La tablette doit se connecter avec ce compte au moins une fois pour enregistrer le token.');
            $this->line('→ Sur la tablette : connexion avec le compte « ' . $user->name . ' » (email du compte chambre).');
            return 0;
        }

        $this->newLine();
        $this->info('Token présent : les notifications push pour cette chambre devraient être envoyées à ce compte.');
        return 0;
    }

    private function listRoomsWithTokens(): void
    {
        $usersWithToken = User::where('role', 'guest')
            ->has('fcmTokens')
            ->with('room')
            ->get();

        if ($usersWithToken->isEmpty()) {
            $this->warn('Aucun compte tablette avec token FCM enregistré.');
            $this->line('→ Connectez les tablettes avec leurs comptes chambre pour enregistrer les tokens.');
            return;
        }

        $this->info("=== Comptes Tablettes avec Token FCM ({$usersWithToken->count()}) ===");

        $headers = ['User ID', 'Nom Compte', 'Room ID', 'Chambre N°'];
        $rows = [];

        foreach ($usersWithToken as $user) {
            $roomInfo = $user->room ? "chambre {$user->room->room_number}" : ($user->room_number ? "room_number {$user->room_number}" : 'chambre non liée');
            $this->line("  - {$user->name} (user_id={$user->id}) — {$roomInfo}");
        }
    }
}
