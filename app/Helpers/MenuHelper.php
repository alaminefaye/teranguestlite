<?php

namespace App\Helpers;

class MenuHelper
{
    /**
     * Obtenir les groupes de menu selon le rôle de l'utilisateur
     */
    public static function getMenuGroups()
    {
        $user = auth()->user();

        if (!$user) {
            return [];
        }

        // Menu Super Admin
        if ($user->isSuperAdmin()) {
            return self::getSuperAdminMenuGroups();
        }

        // Menu Admin Hôtel
        if ($user->isAdmin()) {
            return self::getAdminMenuGroups();
        }

        // Menu Staff
        if ($user->isStaff()) {
            return self::getStaffMenuGroups();
        }

        // Menu Guest (pas de sidebar admin)
        return [];
    }

    /**
     * Menu Super Admin
     */
    private static function getSuperAdminMenuGroups()
    {
        return [
            [
                'title' => 'Gestion Plateforme',
                'items' => [
                    [
                        'icon' => 'dashboard',
                        'name' => 'Dashboard',
                        'path' => '/admin/dashboard',
                    ],
                    [
                        'icon' => 'enterprise',
                        'name' => 'Entreprises (Hôtels)',
                        'path' => '/admin/enterprises',
                    ],
                    [
                        'icon' => 'user-profile',
                        'name' => 'Utilisateurs',
                        'path' => '/admin/users',
                    ],
                    [
                        'icon' => 'campaign',
                        'name' => 'Annonces & Vidéos',
                        'path' => '/admin/announcements',
                    ],
                ],
            ],
            [
                'title' => 'Autres',
                'items' => [
                    [
                        'icon' => 'user-profile',
                        'name' => 'Profil',
                        'path' => '/profile',
                    ],
                ],
            ],
        ];
    }

    /**
     * Menu Admin Hôtel
     */
    private static function getAdminMenuGroups()
    {
        return [
            [
                'title' => 'Gestion Hôtel',
                'items' => [
                    [
                        'icon' => 'dashboard',
                        'name' => 'Dashboard',
                        'path' => '/dashboard',
                    ],
                    [
                        'icon' => 'room',
                        'name' => 'Chambres',
                        'path' => '/dashboard/rooms',
                    ],
                    [
                        'icon' => 'calendar',
                        'name' => 'Réservations & demandes',
                        'path' => '#',
                        'subItems' => [
                            ['name' => 'Réservations chambres', 'path' => '/dashboard/reservations'],
                            ['name' => 'Réservations Spa', 'path' => '/dashboard/spa-reservations'],
                            ['name' => 'Réservations Excursions', 'path' => '/dashboard/excursion-bookings'],
                            ['name' => 'Réservations Restaurants', 'path' => '/dashboard/restaurant-reservations'],
                            ['name' => 'Demandes Blanchisserie', 'path' => '/dashboard/laundry-requests'],
                            ['name' => 'Demandes - Services Palace', 'path' => '/dashboard/palace-requests'],
                        ],
                    ],
                    [
                        'icon' => 'user-profile',
                        'name' => 'Clients (invités)',
                        'path' => '/dashboard/guests',
                    ],
                    [
                        'icon' => 'qr-code',
                        'name' => 'QR Code Client',
                        'path' => '/dashboard/qrcode-client',
                    ],
                    [
                        'icon' => 'ecommerce',
                        'name' => 'Commandes',
                        'path' => '/dashboard/orders',
                    ],
                    [
                        'icon' => 'receipt',
                        'name' => 'Facturation',
                        'path' => '/dashboard/billing',
                    ],
                    [
                        'icon' => 'menu-food',
                        'name' => 'Menus',
                        'path' => '#',
                        'subItems' => [
                            ['name' => 'Catégories de menu', 'path' => '/dashboard/menu-categories'],
                            ['name' => 'Articles de menu', 'path' => '/dashboard/menu-items'],
                        ],
                    ],
                    [
                        'icon' => 'restaurant',
                        'name' => 'Restaurants & Bars',
                        'path' => '/dashboard/restaurants',
                    ],
                    [
                        'icon' => 'services',
                        'name' => 'Services',
                        'path' => '#',
                        'subItems' => [
                            ['name' => 'Spa & Bien-être', 'path' => '/dashboard/spa-services'],
                            ['name' => 'Bien-être, Sport & Loisirs', 'path' => '/dashboard/leisure-categories'],
                            ['name' => 'Horaires salle de sport', 'path' => '/dashboard/gym-hours'],
                            ['name' => 'Hotel Infos & Sécurité', 'path' => '/dashboard/hotel-infos-security'],
                            ['name' => 'Galerie', 'path' => '/dashboard/gallery'],
                            ['name' => 'Nos établissements', 'path' => '/dashboard/establishments'],
                            ['name' => 'Blanchisserie', 'path' => '/dashboard/laundry-services'],
                            ['name' => 'Amenities & Conciergerie', 'path' => '/dashboard/amenity-categories'],
                            ['name' => 'Services Palace', 'path' => '/dashboard/palace-services'],
                            ['name' => 'Véhicules (location)', 'path' => '/dashboard/vehicles'],
                            ['name' => 'Excursions', 'path' => '/dashboard/excursions'],
                            ['name' => 'Annonces & Vidéos', 'path' => '/dashboard/enterprise-announcements'],
                        ],
                    ],
                    [
                        'icon' => 'users',
                        'name' => 'Staff',
                        'path' => '/dashboard/staff',
                    ],
                    [
                        'icon' => 'tablet',
                        'name' => 'Accès tablettes',
                        'path' => '/dashboard/tablet-accesses',
                    ],
                    [
                        'icon' => 'report',
                        'name' => 'Rapports',
                        'path' => '/dashboard/reports',
                    ],
                    [
                        'icon' => 'rate-review',
                        'name' => 'Avis clients',
                        'path' => '/dashboard/guest-reviews',
                    ],
                    [
                        'icon' => 'package',
                        'name' => 'Stocks',
                        'path' => '#',
                        'subItems' => [
                            ['name' => 'Tableau de bord stocks', 'path' => '/dashboard/stock'],
                            ['name' => 'Catégories de stock', 'path' => '/dashboard/stock-categories'],
                            ['name' => 'Produits / Stock', 'path' => '/dashboard/stock-products'],
                            ['name' => 'Mouvements', 'path' => '/dashboard/stock-movements'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Autres',
                'items' => [
                    [
                        'icon' => 'user-profile',
                        'name' => 'Profil',
                        'path' => '/profile',
                    ],
                ],
            ],
        ];
    }

    /**
     * Menu Staff (selon département)
     */
    private static function getStaffMenuGroups()
    {
        $user = auth()->user();
        $department = $user->department;

        $items = [
            [
                'icon' => 'dashboard',
                'name' => 'Dashboard',
                'path' => '/staff/dashboard',
            ],
        ];

        // Ajouter les items selon le département
        switch ($department) {
            case 'reception':
            case 'concierge':
                $items[] = [
                    'icon' => 'task',
                    'name' => 'Demandes de service',
                    'path' => '/staff/service-requests',
                ];
                break;
            case 'housekeeping':
                $items[] = [
                    'icon' => 'task',
                    'name' => 'Demandes ménage',
                    'path' => '/staff/housekeeping-requests',
                ];
                break;
            case 'room_service':
                $items[] = [
                    'icon' => 'ecommerce',
                    'name' => 'Commandes room service',
                    'path' => '/staff/room-service-orders',
                ];
                break;
            case 'spa':
                $items[] = [
                    'icon' => 'calendar',
                    'name' => 'Réservations spa',
                    'path' => '/staff/spa-bookings',
                ];
                break;
        }

        return [
            [
                'title' => 'Mon département',
                'items' => $items,
            ],
            [
                'title' => 'Autres',
                'items' => [
                    [
                        'icon' => 'user-profile',
                        'name' => 'Profil',
                        'path' => '/profile',
                    ],
                ],
            ],
        ];
    }

    /**
     * Anciennes méthodes pour compatibilité (à supprimer plus tard)
     */
    public static function getMainNavItems()
    {
        return self::getAdminMenuGroups()[0]['items'] ?? [];
    }

    public static function getOthersItems()
    {
        return [
            [
                'icon' => 'user-profile',
                'name' => 'Profil',
                'path' => '/profile',
            ],
        ];
    }

    public static function isActive($path)
    {
        return request()->is(ltrim($path, '/'));
    }

    public static function getIconSvg($iconName)
    {
        $icons = [
            'dashboard' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z" fill="currentColor"></path></svg>',

            'ecommerce' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.31641 4H3.49696C4.24468 4 4.87822 4.55068 4.98234 5.29112L5.13429 6.37161M5.13429 6.37161L6.23641 14.2089C6.34053 14.9493 6.97407 15.5 7.72179 15.5L17.0833 15.5C17.6803 15.5 18.2205 15.146 18.4587 14.5986L21.126 8.47023C21.5572 7.4795 20.8312 6.37161 19.7507 6.37161H5.13429Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M7.7832 19.5H7.7932M16.3203 19.5H16.3303" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>',

            'calendar' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 2C8.41421 2 8.75 2.33579 8.75 2.75V3.75H15.25V2.75C15.25 2.33579 15.5858 2 16 2C16.4142 2 16.75 2.33579 16.75 2.75V3.75H18.5C19.7426 3.75 20.75 4.75736 20.75 6V9V19C20.75 20.2426 19.7426 21.25 18.5 21.25H5.5C4.25736 21.25 3.25 20.2426 3.25 19V9V6C3.25 4.75736 4.25736 3.75 5.5 3.75H7.25V2.75C7.25 2.33579 7.58579 2 8 2ZM8 5.25H5.5C5.08579 5.25 4.75 5.58579 4.75 6V8.25H19.25V6C19.25 5.58579 18.9142 5.25 18.5 5.25H16H8ZM19.25 9.75H4.75V19C4.75 19.4142 5.08579 19.75 5.5 19.75H18.5C18.9142 19.75 19.25 19.4142 19.25 19V9.75Z" fill="currentColor"></path></svg>',

            'user-profile' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5ZM17.0246 18.8566V18.8455C17.0246 16.7744 15.3457 15.0955 13.2746 15.0955H10.7246C8.65354 15.0955 6.97461 16.7744 6.97461 18.8455V18.856C8.38223 19.8895 10.1198 20.5 12 20.5C13.8798 20.5 15.6171 19.8898 17.0246 18.8566ZM2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM11.9991 7.25C10.8847 7.25 9.98126 8.15342 9.98126 9.26784C9.98126 10.3823 10.8847 11.2857 11.9991 11.2857C13.1135 11.2857 14.0169 10.3823 14.0169 9.26784C14.0169 8.15342 13.1135 7.25 11.9991 7.25ZM8.48126 9.26784C8.48126 7.32499 10.0563 5.75 11.9991 5.75C13.9419 5.75 15.5169 7.32499 15.5169 9.26784C15.5169 11.2107 13.9419 12.7857 11.9991 12.7857C10.0563 12.7857 8.48126 11.2107 8.48126 9.26784Z" fill="currentColor"></path></svg>',

            'task' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M7.75586 5.50098C7.75586 5.08676 8.09165 4.75098 8.50586 4.75098H18.4985C18.9127 4.75098 19.2485 5.08676 19.2485 5.50098L19.2485 15.4956C19.2485 15.9098 18.9127 16.2456 18.4985 16.2456H8.50586C8.09165 16.2456 7.75586 15.9098 7.75586 15.4956V5.50098ZM8.50586 3.25098C7.26322 3.25098 6.25586 4.25834 6.25586 5.50098V6.26318H5.50195C4.25931 6.26318 3.25195 7.27054 3.25195 8.51318V18.4995C3.25195 19.7422 4.25931 20.7495 5.50195 20.7495H15.4883C16.7309 20.7495 17.7383 19.7421 17.7383 18.4995L17.7383 17.7456H18.4985C19.7411 17.7456 20.7485 16.7382 20.7485 15.4956L20.7485 5.50097C20.7485 4.25833 19.7411 3.25098 18.4985 3.25098H8.50586ZM16.2383 17.7456H8.50586C7.26322 17.7456 6.25586 16.7382 6.25586 15.4956V7.76318H5.50195C5.08774 7.76318 4.75195 8.09897 4.75195 8.51318V18.4995C4.75195 18.9137 5.08774 19.2495 5.50195 19.2495H15.4883C15.9025 19.2495 16.2383 18.9137 16.2383 18.4995L16.2383 17.7456Z" fill="currentColor"></path></svg>',

            'enterprise' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 21H21M4 18H20M5 18V7.5L12 3L19 7.5V18M9 9H10M9 12H10M9 15H10M14 9H15M14 12H15M14 15H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',

            'room' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 9V19M21 9V19M3 9L12 3L21 9M5 9H19M10 12H10.01M14 12H14.01M10 15H10.01M14 15H14.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',

            'reservation' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 2V5M16 2V5M3.5 9.09H20.5M21 8.5V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M11.9955 13.7H12.0045M8.29431 13.7H8.30329M8.29431 16.7H8.30329" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',

            'report' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M3 3.75C3 3.33579 3.33579 3 3.75 3H20.25C20.6642 3 21 3.33579 21 3.75V20.25C21 20.6642 20.6642 21 20.25 21H3.75C3.33579 21 3 20.6642 3 20.25V3.75ZM4.5 4.5V19.5H19.5V4.5H4.5ZM7.5 8.25C7.5 7.83579 7.83579 7.5 8.25 7.5H15.75C16.1642 7.5 16.5 7.83579 16.5 8.25C16.5 8.66421 16.1642 9 15.75 9H8.25C7.83579 9 7.5 8.66421 7.5 8.25ZM7.5 12C7.5 11.5858 7.83579 11.25 8.25 11.25H15.75C16.1642 11.25 16.5 11.5858 16.5 12C16.5 12.4142 16.1642 12.75 15.75 12.75H8.25C7.83579 12.75 7.5 12.4142 7.5 12ZM7.5 15.75C7.5 15.3358 7.83579 15 8.25 15H12.75C13.1642 15 13.5 15.3358 13.5 15.75C13.5 16.1642 13.1642 16.5 12.75 16.5H8.25C7.83579 16.5 7.5 16.1642 7.5 15.75Z" fill="currentColor"></path></svg>',

            'qr-code' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 5h4v4H3V5zm2 2V7h2v2H5zm-2 8h4v4H3v-4zm2 2v-2h2v2H5zm8-12h4v4h-4V5zm2 2V7h2v2h-2zm4 2h2v2h-2V9zm0 2v2h2v2h-2v-2zm-2 2h-2v2h2v2h2v-2h2v-2h-2v-2h-2v2zm4 2v2h2v2h2v-2h-2v-2h-2zm-8 4h4v4h-4v-4zm2 2v-2h2v2h-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',

            'receipt' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 4v16l2-1.5 2 1.5 2-1.5 2 1.5 2-1.5 2 1.5V4H4z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 9h8M8 13h8M8 17h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',

            'menu-food' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 6h16M4 12h16M4 18h10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 10a3 3 0 1 1 6 0v8h-6v-8zM9 18V6l4 2v10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',

            'restaurant' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 2v8h2v12h2V10h2V2H3zm8 0v6c0 2.2 1.8 4 4 4v10h2V12c-2.2 0-4-1.8-4-4V2h-4zm8 0v20h2V12c2.2 0 4-1.8 4-4V2h-6z" fill="currentColor"/></svg>',

            'users' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 4.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM7 7a4 4 0 1 1 0 8 4 4 0 0 1 0-8zm10 0a4 4 0 1 1 0 8 4 4 0 0 1 0-8zm-5 8.5c-3.5 0-6 1.8-6 3.5V20h12v-1c0-1.7-2.5-3.5-6-3.5zM3 18.5c0-1.7 2.5-3.5 6-3.5s6 1.8 6 3.5V20H3v-1.5zm16 0c0-1.7-2.5-3.5-6-3.5v2c2.5 0 4 1.2 4 2v.5h6V18c0-1.1-.9-2-2-2h-2z" fill="currentColor"/></svg>',

            'tablet' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 4h16v16H4V4zm2 2v12h12V6H6zm5 13h2v-1h-2v1z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',

            'package' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L3 7v10l9 5 9-5V7L12 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 7l9 5 9-5M12 22V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',

            'campaign' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M16 11V15H17C17.5523 15 18 14.5523 18 14V12C18 11.4477 17.5523 11 17 11H16ZM14 11V15C14 16.1046 13.1046 17 12 17H8C6.89543 17 6 16.1046 6 15V13C6 11.8954 6.89543 11 8 11H10.1716L12.5858 8.58579C13.2167 7.95489 14.072 7.6006 14.9645 7.6006H15.0355V11ZM10 15H12C12.5523 15 13 14.5523 13 14V11.232C13 10.9668 12.8946 10.7126 12.7071 10.5251L10.7071 8.52513C10.5196 8.33758 10.2655 8.23223 10 8.23223H8C7.44772 8.23223 7 8.67994 7 9.23223V15ZM22 13C22 15.2091 20.2091 17 18 17H16.2923L16.292 17.0003L13.7071 19.5858C12.8123 20.4806 11.5992 21 10.3333 21H8C5.23858 21 3 18.7614 3 16V13C3 10.2386 5.23858 8 8 8H9.33333C10.5992 8 11.8123 7.48062 12.7071 6.58579L15.2929 4H18C20.2091 4 22 5.79086 22 8V13Z" fill="currentColor"></path></svg>',

            'services' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" stroke="currentColor" stroke-width="2"/><path d="M19.4 15a7.4 7.4 0 0 0 .6-3c0-4-3.5-7-8-7s-8 3-8 7c0 1.2.3 2.3.6 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M4.6 15a7.4 7.4 0 0 1-.6-3c0-4 3.5-7 8-7s8 3 8 7c0 1.2-.3 2.3-.6 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',

            'rate-review' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="currentColor"/></svg>',
        ];

        return $icons[$iconName] ?? '<svg width="1em" height="1em" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="currentColor"/></svg>';
    }
}
