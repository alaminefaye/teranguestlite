<?php

namespace App\Helpers;

/**
 * Clés des sections que le staff peut gérer (tuiles Espace Admin + notifications).
 * Chaque staff reçoit les notifications et ne voit que les sections qui lui sont affectées.
 */
final class StaffSection
{
    public const ROOM_SERVICE_ORDERS = 'room_service_orders';
    public const RESTAURANT_RESERVATIONS = 'restaurant_reservations';
    public const SPA_RESERVATIONS = 'spa_reservations';
    public const EXCURSIONS = 'excursions';
    public const LAUNDRY_REQUESTS = 'laundry_requests';
    public const PALACE_SERVICES = 'palace_services';
    public const ASSISTANCE_EMERGENCY = 'assistance_emergency';
    public const CHAT_MESSAGES = 'chat_messages';
    public const BILLING_INVOICING = 'billing_invoicing';

    /** Toutes les clés (pour validation et listes). */
    public static function all(): array
    {
        return [
            self::ROOM_SERVICE_ORDERS,
            self::RESTAURANT_RESERVATIONS,
            self::SPA_RESERVATIONS,
            self::EXCURSIONS,
            self::LAUNDRY_REQUESTS,
            self::PALACE_SERVICES,
            self::ASSISTANCE_EMERGENCY,
            self::CHAT_MESSAGES,
            self::BILLING_INVOICING,
        ];
    }

    /** Libellés pour l’interface (dashboard web). */
    public static function labels(): array
    {
        return [
            self::ROOM_SERVICE_ORDERS => 'Commandes Room Service',
            self::RESTAURANT_RESERVATIONS => 'Réservations Restaurants',
            self::SPA_RESERVATIONS => 'Réservations Spa & Bien-être',
            self::EXCURSIONS => 'Excursions & Activités',
            self::LAUNDRY_REQUESTS => 'Demandes Blanchisserie',
            self::PALACE_SERVICES => 'Services Palace / Conciergerie',
            self::ASSISTANCE_EMERGENCY => 'Assistance & Urgence',
            self::CHAT_MESSAGES => 'Messages / Chat client',
            self::BILLING_INVOICING => 'Facturation / Notes de chambre',
        ];
    }

    public static function label(string $key): string
    {
        return self::labels()[$key] ?? $key;
    }
}
