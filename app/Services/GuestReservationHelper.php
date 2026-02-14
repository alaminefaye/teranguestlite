<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;

/**
 * Vérifie qu'un utilisateur (client chambre) a un séjour valide (réservation active)
 * et retourne la chambre + réservation + guest_id pour lier les réservations (spa, restaurant, etc.) à un client.
 */
class GuestReservationHelper
{
    /**
     * Message d'erreur 403 quand pas de client valide (aucun code saisi ou pas de séjour actif du compte).
     */
    public const MESSAGE_REQUIRE_VALID_CLIENT = 'Réservation possible uniquement pour les clients avec un séjour valide. Entrez votre code client ou connectez-vous avec le compte de la chambre.';

    /**
     * Message d'erreur 403 quand un code a été saisi mais il est invalide ou le séjour lié à ce code est expiré.
     */
    public const MESSAGE_CLIENT_CODE_INVALID_OR_EXPIRED = 'Code client invalide ou expiré. Vérifiez le code à 6 chiffres reçu à l\'enregistrement ou contactez la réception.';

    /**
     * Pour l'utilisateur connecté (room_number + enterprise_id), retourne la chambre, la réservation active et le guest_id.
     * Retourne null si l'utilisateur n'a pas de chambre ou aucun séjour actif (check_in <= now <= check_out, status confirmed/checked_in).
     *
     * @return array{room: Room, reservation: Reservation, room_id: int, guest_id: int|null}|null
     */
    public static function activeStayForUser(User $user): ?array
    {
        if (! $user->room_number || ! $user->enterprise_id) {
            return null;
        }

        $room = Room::withoutGlobalScope('enterprise')
            ->where('enterprise_id', $user->enterprise_id)
            ->where('room_number', $user->room_number)
            ->first();

        if (! $room) {
            return null;
        }

        $reservation = Reservation::withoutGlobalScope('enterprise')
            ->where('room_id', $room->id)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->where('check_in', '<=', now())
            ->where('check_out', '>=', now())
            ->orderByDesc('check_in')
            ->first();

        if (! $reservation) {
            return null;
        }

        return [
            'room' => $room,
            'reservation' => $reservation,
            'room_id' => $room->id,
            'guest_id' => $reservation->guest_id,
        ];
    }

    /**
     * Vérifie que l'utilisateur a un séjour actif. Retourne le contexte (room_id, guest_id) ou null.
     * Utile pour les contrôleurs qui créent une réservation / demande.
     *
     * @return array{room_id: int, guest_id: int|null}|null
     */
    public static function requireActiveStayForUser(User $user): ?array
    {
        $ctx = self::activeStayForUser($user);
        if (! $ctx) {
            return null;
        }
        return [
            'room_id' => $ctx['room_id'],
            'guest_id' => $ctx['guest_id'],
        ];
    }

    /**
     * Valide le code client pour l'utilisateur (chambre liée à son room_number).
     * Retourne room_id et guest_id si le code est valide et qu'un séjour actif existe pour cette chambre.
     *
     * @return array{room_id: int, guest_id: int}|null
     */
    public static function validateClientCodeForUser(User $user, ?string $code): ?array
    {
        $code = $code ? trim($code) : '';
        if ($code === '' || ! $user->room_number || ! $user->enterprise_id) {
            return null;
        }

        $room = Room::withoutGlobalScope('enterprise')
            ->where('enterprise_id', $user->enterprise_id)
            ->where('room_number', $user->room_number)
            ->first();

        if (! $room) {
            return null;
        }

        $guest = Guest::withoutGlobalScope('enterprise')
            ->where('enterprise_id', $user->enterprise_id)
            ->where('access_code', $code)
            ->first();

        if (! $guest) {
            return null;
        }

        $reservation = Reservation::withoutGlobalScope('enterprise')
            ->where('guest_id', $guest->id)
            ->where('room_id', $room->id)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->where('check_in', '<=', now())
            ->where('check_out', '>=', now())
            ->first();

        if (! $reservation) {
            return null;
        }

        return [
            'room_id' => $room->id,
            'guest_id' => $guest->id,
        ];
    }

    /**
     * Retourne le contexte client (room_id, guest_id) : soit séjour actif de l'utilisateur,
     * soit validation par code client. Si aucun des deux, retourne null.
     *
     * @return array{room_id: int, guest_id: int|null}|null
     */
    public static function requireActiveStayOrClientCode(User $user, ?string $clientCode): ?array
    {
        $stay = self::requireActiveStayForUser($user);
        if ($stay !== null) {
            return $stay;
        }
        return self::validateClientCodeForUser($user, $clientCode);
    }

    /**
     * Pour les réservations (spa, restaurant, etc.) : si un code client est saisi, il doit être valide.
     * On n'utilise pas le séjour actif du compte connecté quand un code est fourni (évite d'accepter un code invalide).
     * Si aucun code n'est fourni, on accepte le séjour actif du compte.
     *
     * @return array{room_id: int, guest_id: int|null}|null
     */
    public static function requireValidCodeOrActiveStay(User $user, ?string $clientCode): ?array
    {
        $code = $clientCode !== null ? trim($clientCode) : '';
        if ($code !== '') {
            return self::validateClientCodeForUser($user, $clientCode);
        }
        return self::requireActiveStayForUser($user);
    }
}
