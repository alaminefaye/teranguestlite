@extends('layouts.fullscreen-layout')

@section('content')
    @php
        $landingAsset = fn (string $file) => route('landing.asset', ['file' => $file]);
    @endphp
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <header class="sticky top-0 z-40 border-b border-gray-200/70 bg-white/80 backdrop-blur dark:border-gray-800/70 dark:bg-gray-900/80">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <div class="flex items-center justify-center rounded-full px-4 py-2" style="background-color: #1E252D; border: 1.5px solid #D4AF37;">
                        <img src="{{ asset('images/logo/logo.png') }}" alt="Teranga Guest" class="h-8 w-auto object-contain" />
                    </div>
                </a>

                <nav class="hidden items-center gap-7 text-sm font-medium text-gray-700 md:flex dark:text-gray-300">
                    <a href="#fonctionnalites" class="hover:text-gray-900 dark:hover:text-white">Fonctionnalités</a>
                    <a href="#mobile" class="hover:text-gray-900 dark:hover:text-white">Côté mobile</a>
                    <a href="#web" class="hover:text-gray-900 dark:hover:text-white">Côté web</a>
                    <a href="#galerie" class="hover:text-gray-900 dark:hover:text-white">Galerie</a>
                    <a href="#contact" class="hover:text-gray-900 dark:hover:text-white">Contact</a>
                </nav>

                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}"
                        class="hidden rounded-lg px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300/70 hover:bg-gray-50 md:inline-flex dark:text-white/90 dark:ring-gray-700 dark:hover:bg-gray-800">
                        Se connecter
                    </a>
                    <a href="{{ url('/client') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-theme-md hover:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-500/20">
                        Ouvrir l’app client
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.69l-3.22-3.22a.75.75 0 111.06-1.06l4.5 4.5a.75.75 0 010 1.06l-4.5 4.5a.75.75 0 11-1.06-1.06l3.22-3.22H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
        </header>

        <main>
            <section class="relative overflow-hidden">
                <div class="absolute inset-0">
                    <div class="absolute -top-40 left-1/2 h-[520px] w-[520px] -translate-x-1/2 rounded-full bg-brand-500/15 blur-3xl"></div>
                    <div class="absolute -bottom-44 right-1/3 h-[520px] w-[520px] rounded-full bg-blue-light-500/10 blur-3xl"></div>
                </div>

                <div class="relative mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-12 lg:px-8 lg:py-20">
                    <div class="lg:col-span-6">
                        <div class="inline-flex items-center gap-2 rounded-full bg-brand-500/10 px-3 py-1 text-xs font-semibold text-brand-700 ring-1 ring-brand-500/20 dark:text-brand-200">
                            Plateforme hôtel + app client (mobile / tablette)
                        </div>

                        <h1 class="mt-6 text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl dark:text-white/90">
                            Une expérience client premium, du check-in au room service
                        </h1>

                        <p class="mt-5 text-base leading-7 text-gray-600 dark:text-gray-300">
                            Notre plateforme centralise la gestion hôtelière côté web et offre une app côté client (mobile / tablette)
                            pour commander, réserver, discuter et accéder aux infos utiles.
                        </p>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <a href="{{ route('login') }}"
                                class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-5 py-3 text-sm font-semibold text-white shadow-theme-md hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
                                Accéder au dashboard
                            </a>
                            <a href="#fonctionnalites"
                                class="inline-flex items-center justify-center rounded-lg px-5 py-3 text-sm font-semibold text-gray-900 ring-1 ring-gray-300/70 hover:bg-white dark:text-white/90 dark:ring-gray-700 dark:hover:bg-gray-800">
                                Voir les fonctionnalités
                            </a>
                        </div>

                        <dl class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3">
                            <div class="rounded-xl bg-white p-4 ring-1 ring-gray-200/60 dark:bg-gray-900/60 dark:ring-gray-800/70">
                                <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">Modules</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white/90">Room service</dd>
                            </div>
                            <div class="rounded-xl bg-white p-4 ring-1 ring-gray-200/60 dark:bg-gray-900/60 dark:ring-gray-800/70">
                                <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">Réservations</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white/90">Spa / resto</dd>
                            </div>
                            <div class="rounded-xl bg-white p-4 ring-1 ring-gray-200/60 dark:bg-gray-900/60 dark:ring-gray-800/70">
                                <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">Support</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white/90">Chat + SOS</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="lg:col-span-6">
                        <div class="relative">
                            <div class="absolute -inset-6 rounded-[28px] bg-gradient-to-br from-brand-500/25 via-transparent to-blue-light-500/15 blur-2xl"></div>
                            <div class="relative overflow-hidden rounded-[28px] bg-white ring-1 ring-gray-200/70 shadow-theme-xl dark:bg-gray-900 dark:ring-gray-800">
                                <div class="px-6 py-6 sm:px-8 sm:py-8">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">App client</p>
                                            <p class="mt-2 text-xl font-semibold tracking-tight text-gray-900 dark:text-white/90">Mobile + tablette, simple à utiliser</p>
                                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Commandes, réservations, infos hôtel, chat et assistance.</p>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center rounded-full bg-brand-500/10 px-3 py-1 text-xs font-semibold text-brand-700 ring-1 ring-brand-500/20 dark:text-brand-200">
                                                Room service
                                            </span>
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200/70 dark:bg-gray-800/60 dark:text-gray-200 dark:ring-gray-700">
                                                Réservations
                                            </span>
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200/70 dark:bg-gray-800/60 dark:text-gray-200 dark:ring-gray-700">
                                                Chat
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-8 grid gap-3 sm:grid-cols-2">
                                        <div class="flex items-start gap-3 rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200/70 dark:bg-gray-800/40 dark:ring-gray-700">
                                            <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-brand-500/20 dark:text-brand-200">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M5 7v12a2 2 0 002 2h10a2 2 0 002-2V7" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V5a3 3 0 016 0v2" />
                                                </svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white/90">Commandes</p>
                                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Room service et suivi des statuts.</p>
                                            </div>
                                        </div>

                                        <div class="flex items-start gap-3 rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200/70 dark:bg-gray-800/40 dark:ring-gray-700">
                                            <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-brand-500/20 dark:text-brand-200">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M6 21h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white/90">Réservations</p>
                                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Spa, restaurants, excursions.</p>
                                            </div>
                                        </div>

                                        <div class="flex items-start gap-3 rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200/70 dark:bg-gray-800/40 dark:ring-gray-700">
                                            <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-brand-500/20 dark:text-brand-200">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2a10 10 0 1010 10A10 10 0 0012 2z" />
                                                </svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white/90">Infos hôtel</p>
                                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Livret, horaires, consignes.</p>
                                            </div>
                                        </div>

                                        <div class="flex items-start gap-3 rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200/70 dark:bg-gray-800/40 dark:ring-gray-700">
                                            <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-brand-500/20 dark:text-brand-200">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 15a4 4 0 01-4 4H7l-4 3V7a4 4 0 014-4h10a4 4 0 014 4z" />
                                                </svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white/90">Chat</p>
                                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Messages rapides avec l’hôtel.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="text-sm text-gray-600 dark:text-gray-300">Les captures d’écran sont disponibles dans la galerie en bas.</p>
                                        <a href="#galerie"
                                            class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-5 py-3 text-sm font-semibold text-white shadow-theme-md hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
                                            Voir la galerie
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="fonctionnalites" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid gap-10 lg:grid-cols-12 lg:items-end">
                    <div class="lg:col-span-5">
                        <h2 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-white/90">
                            Les fonctionnalités qui font la différence
                        </h2>
                        <p class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-300">
                            Un seul écosystème pour gérer l’hôtel et améliorer l’expérience client. Conçu pour être clair, rapide et
                            agréable sur mobile comme sur desktop.
                        </p>
                    </div>
                    <div class="lg:col-span-7">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200/70 dark:bg-gray-900 dark:ring-gray-800">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-brand-500/20 dark:text-brand-200">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M5 6h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 14h4M7 18h10" />
                                        </svg>
                                    </span>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Réservations & suivi</h3>
                                </div>
                                <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                    Gestion des réservations, check-in/check-out, annulations et facturation.
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200/70 dark:bg-gray-900 dark:ring-gray-800">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-brand-500/20 dark:text-brand-200">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M5 7v12a2 2 0 002 2h10a2 2 0 002-2V7" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V5a3 3 0 016 0v2" />
                                        </svg>
                                    </span>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Commandes & room service</h3>
                                </div>
                                <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                    Menus, commandes, statuts (préparation, livraison, terminé) et historique.
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200/70 dark:bg-gray-900 dark:ring-gray-800">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-brand-500/20 dark:text-brand-200">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s8-5.33 8-12a8 8 0 10-16 0c0 6.67 8 12 8 12z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 11a2 2 0 100-4 2 2 0 000 4z" />
                                        </svg>
                                    </span>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Services & activités</h3>
                                </div>
                                <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                    Spa, restaurants, excursions, blanchisserie, palace, sport & loisirs.
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200/70 dark:bg-gray-900 dark:ring-gray-800">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-brand-500/20 dark:text-brand-200">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 13h12v8H6z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 9h8" />
                                        </svg>
                                    </span>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Stocks & rapports</h3>
                                </div>
                                <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                    Produits, mouvements, alertes et tableaux de bord pour piloter l’activité.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="mobile" class="bg-white/60 py-16 ring-1 ring-gray-200/60 dark:bg-gray-900/40 dark:ring-gray-800/70">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid gap-10 lg:grid-cols-12">
                        <div class="lg:col-span-5">
                            <h2 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-white/90">
                                Côté mobile / tablette (client)
                            </h2>
                            <p class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-300">
                                Un parcours simple pour les clients : commander, réserver, consulter les infos de l’hôtel et demander de l’aide.
                            </p>
                            <div class="mt-7 inline-flex items-center gap-2 rounded-xl bg-gray-50 px-4 py-3 ring-1 ring-gray-200/60 dark:bg-gray-800/40 dark:ring-gray-700">
                                <img src="{{ asset('client/favicon.png') }}" alt="" class="h-6 w-6" />
                                <span class="text-sm font-semibold text-gray-900 dark:text-white/90">Disponible via</span>
                                <a href="{{ url('/client') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-200 dark:hover:text-brand-100">
                                    /client
                                </a>
                            </div>
                        </div>

                        <div class="lg:col-span-7">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200/70 dark:bg-gray-900 dark:ring-gray-800">
                                    <div class="flex items-start gap-4">
                                        <img src="{{ asset('client/assets/assets/images/sub_laundry.png') }}" alt="" class="h-11 w-11 rounded-xl bg-brand-500/10 p-2 ring-1 ring-brand-500/20" />
                                        <div>
                                            <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Demandes rapides</h3>
                                            <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                                Blanchisserie, palace, demandes et suivi des statuts en temps réel.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200/70 dark:bg-gray-900 dark:ring-gray-800">
                                    <div class="flex items-start gap-4">
                                        <img src="{{ asset('client/assets/assets/images/box_wellness.png') }}" alt="" class="h-11 w-11 rounded-xl bg-brand-500/10 p-2 ring-1 ring-brand-500/20" />
                                        <div>
                                            <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Spa & bien-être</h3>
                                            <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                                Consultation des services, réservation et historique.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200/70 dark:bg-gray-900 dark:ring-gray-800">
                                    <div class="flex items-start gap-4">
                                        <img src="{{ asset('client/assets/assets/images/info_hotel.png') }}" alt="" class="h-11 w-11 rounded-xl bg-brand-500/10 p-2 ring-1 ring-brand-500/20" />
                                        <div>
                                            <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Infos hôtel</h3>
                                            <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                                Livret d’accueil, horaires, consignes et services disponibles.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200/70 dark:bg-gray-900 dark:ring-gray-800">
                                    <div class="flex items-start gap-4">
                                        <img src="{{ asset('client/assets/assets/images/info_urgence.png') }}" alt="" class="h-11 w-11 rounded-xl bg-brand-500/10 p-2 ring-1 ring-brand-500/20" />
                                        <div>
                                            <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Assistance & urgence</h3>
                                            <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                                Bouton SOS, informations de sécurité et contact rapide.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="web" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid gap-10 lg:grid-cols-12 lg:items-start">
                    <div class="lg:col-span-6">
                        <h2 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-white/90">
                            Côté web (hôtel / staff)
                        </h2>
                        <p class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-300">
                            Un dashboard complet pour administrer l’établissement, gérer les demandes et garder une vue claire sur l’activité.
                        </p>

                        <div class="mt-8 grid gap-3 sm:grid-cols-2">
                            <div class="flex items-start gap-3 rounded-xl bg-white p-4 ring-1 ring-gray-200/70 dark:bg-gray-900 dark:ring-gray-800">
                                <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand-500/10 text-brand-700 ring-1 ring-brand-500/20 dark:text-brand-200">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M6 21h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white/90">Planification & opérations</p>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Chambres, réservations, clients, check-in/out, facturation.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 rounded-xl bg-white p-4 ring-1 ring-gray-200/70 dark:bg-gray-900 dark:ring-gray-800">
                                <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand-500/10 text-brand-700 ring-1 ring-brand-500/20 dark:text-brand-200">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white/90">Contenu & services</p>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Menus, services spa, restaurants, excursions, annonces.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 rounded-xl bg-white p-4 ring-1 ring-gray-200/70 dark:bg-gray-900 dark:ring-gray-800">
                                <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand-500/10 text-brand-700 ring-1 ring-brand-500/20 dark:text-brand-200">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white/90">Réactivité</p>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Chat client, demandes et suivi rapide des opérations.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 rounded-xl bg-white p-4 ring-1 ring-gray-200/70 dark:bg-gray-900 dark:ring-gray-800">
                                <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand-500/10 text-brand-700 ring-1 ring-brand-500/20 dark:text-brand-200">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 7v14h12V7" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 11h8M8 15h8" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white/90">Stocks & inventaire</p>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Catégories, produits, mouvements, alertes et suivi.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 rounded-xl bg-white p-4 ring-1 ring-gray-200/70 dark:bg-gray-900 dark:ring-gray-800">
                                <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand-500/10 text-brand-700 ring-1 ring-brand-500/20 dark:text-brand-200">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 17V7a2 2 0 012-2h8a2 2 0 012 2v10" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9h6M9 12h6" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white/90">Rapports & audits</p>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Tableaux de bord, rapports et historique des actions.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 rounded-xl bg-white p-4 ring-1 ring-gray-200/70 dark:bg-gray-900 dark:ring-gray-800">
                                <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand-500/10 text-brand-700 ring-1 ring-brand-500/20 dark:text-brand-200">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 21a8 8 0 0116 0" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white/90">Personnel & accès</p>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Rôles staff, comptes, accès tablette et QR codes.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 rounded-xl bg-white p-4 ring-1 ring-gray-200/70 dark:bg-gray-900 dark:ring-gray-800">
                                <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand-500/10 text-brand-700 ring-1 ring-brand-500/20 dark:text-brand-200">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16v12H4z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white/90">Galerie & contenus</p>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Albums, photos, infos hôtel et contenu client.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-6">
                        <div class="overflow-hidden rounded-3xl bg-gray-900 ring-1 ring-gray-900/20 shadow-theme-xl dark:ring-gray-800">
                            <div class="px-6 py-5">
                                <p class="text-sm font-semibold text-white">Un écosystème complet</p>
                                <p class="mt-2 text-sm text-white/80">
                                    Administration + expérience client, avec une base solide pour évoluer.
                                </p>
                            </div>
                            <div class="grid grid-cols-2 gap-2 px-6 pb-6 sm:grid-cols-3">
                                <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                                    <p class="text-xs font-semibold text-white/70">Modules</p>
                                    <p class="mt-2 text-sm font-semibold text-white">Menus</p>
                                </div>
                                <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                                    <p class="text-xs font-semibold text-white/70">Gestion</p>
                                    <p class="mt-2 text-sm font-semibold text-white">Réservations</p>
                                </div>
                                <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                                    <p class="text-xs font-semibold text-white/70">Support</p>
                                    <p class="mt-2 text-sm font-semibold text-white">Chat</p>
                                </div>
                                <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                                    <p class="text-xs font-semibold text-white/70">Stocks</p>
                                    <p class="mt-2 text-sm font-semibold text-white">Inventaire</p>
                                </div>
                                <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                                    <p class="text-xs font-semibold text-white/70">Rapports</p>
                                    <p class="mt-2 text-sm font-semibold text-white">Audits</p>
                                </div>
                                <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                                    <p class="text-xs font-semibold text-white/70">Accès</p>
                                    <p class="mt-2 text-sm font-semibold text-white">Staff</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="galerie" class="bg-white py-16 ring-1 ring-gray-200/60 dark:bg-gray-900 dark:ring-gray-800/70">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h2 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-white/90">Galerie</h2>
                            <p class="mt-3 text-base leading-7 text-gray-600 dark:text-gray-300">
                                Captures de ton application (tablette + mobile). Mise en page propre, images bien cadrées.
                            </p>
                        </div>
                        <a href="{{ url('/client') }}"
                            class="inline-flex items-center justify-center rounded-lg px-5 py-3 text-sm font-semibold text-gray-900 ring-1 ring-gray-300/70 hover:bg-gray-50 dark:text-white/90 dark:ring-gray-700 dark:hover:bg-gray-800">
                            Voir l’app en action
                        </a>
                    </div>

                    <div class="mt-10 grid gap-6 lg:grid-cols-12">
                        <div class="lg:col-span-7">
                            <div class="overflow-hidden rounded-3xl bg-gray-50 ring-1 ring-gray-200/70 shadow-theme-xl dark:bg-gray-900/40 dark:ring-gray-800">
                                <div class="flex items-center justify-between gap-4 px-6 py-5">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tablette</p>
                                        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">Vue iPad</p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full bg-brand-500/10 px-3 py-1 text-xs font-semibold text-brand-700 ring-1 ring-brand-500/20 dark:text-brand-200">
                                        Grand écran
                                    </span>
                                </div>
                                <div class="flex h-[340px] items-center justify-center px-6 pb-8 sm:h-[440px]">
                                    <img src="{{ $landingAsset('ipad.png') }}" alt="Capture iPad" loading="lazy" decoding="async" class="h-full w-full object-contain" />
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-6 lg:col-span-5">
                            <div class="overflow-hidden rounded-3xl bg-gray-50 ring-1 ring-gray-200/70 shadow-theme-xl dark:bg-gray-900/40 dark:ring-gray-800">
                                <div class="flex items-center justify-between gap-4 px-6 py-5">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Mobile</p>
                                        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">Écran 1</p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full bg-gray-900 px-3 py-1 text-xs font-semibold text-white dark:bg-white dark:text-gray-900">
                                        iPhone
                                    </span>
                                </div>
                                <div class="flex h-[220px] items-center justify-center px-6 pb-8 sm:h-[260px]">
                                    <img src="{{ $landingAsset('iphone1.png') }}" alt="Capture iPhone 1" loading="lazy" decoding="async" class="h-full w-full object-contain" />
                                </div>
                            </div>

                            <div class="overflow-hidden rounded-3xl bg-gray-50 ring-1 ring-gray-200/70 shadow-theme-xl dark:bg-gray-900/40 dark:ring-gray-800">
                                <div class="flex items-center justify-between gap-4 px-6 py-5">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Mobile</p>
                                        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">Écran 2</p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full bg-gray-900 px-3 py-1 text-xs font-semibold text-white dark:bg-white dark:text-gray-900">
                                        iPhone
                                    </span>
                                </div>
                                <div class="flex h-[220px] items-center justify-center px-6 pb-8 sm:h-[260px]">
                                    <img src="{{ $landingAsset('iphone2.png') }}" alt="Capture iPhone 2" loading="lazy" decoding="async" class="h-full w-full object-contain" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer id="contact" class="border-t border-gray-200/70 bg-white dark:border-gray-800/70 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                <div class="grid gap-10 lg:grid-cols-12">
                    <div class="lg:col-span-5">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center rounded-full px-4 py-2" style="background-color: #1E252D; border: 1.5px solid #D4AF37;">
                                <img src="{{ asset('images/logo/logo.png') }}" alt="Teranga Guest" class="h-8 w-auto object-contain" />
                            </div>
                        </div>

                        <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">Web dashboard + app client.</p>

                        <div class="mt-4 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span class="font-semibold text-gray-900 dark:text-white/90">Email :</span>
                                <a href="mailto:contact@teranguest.com" class="font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-200 dark:hover:text-brand-100 underline underline-offset-4">
                                    contact@teranguest.com
                                </a>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span class="font-semibold text-gray-900 dark:text-white/90">Téléphone :</span>
                                <a href="tel:+221770947794" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white underline underline-offset-4">
                                    +221 77 094 77 94
                                </a>
                                <span class="text-gray-400">/</span>
                                <a href="tel:+221773309613" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white underline underline-offset-4">
                                    +221 77 330 96 13
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white/90">Liens</p>
                        <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm font-medium text-gray-600 dark:text-gray-300">
                            <a href="#fonctionnalites" class="hover:text-gray-900 dark:hover:text-white">Fonctionnalités</a>
                            <a href="#mobile" class="hover:text-gray-900 dark:hover:text-white">Côté mobile</a>
                            <a href="#web" class="hover:text-gray-900 dark:hover:text-white">Côté web</a>
                            <a href="#galerie" class="hover:text-gray-900 dark:hover:text-white">Galerie</a>
                            <a href="{{ route('privacy-policy') }}" class="hover:text-gray-900 dark:hover:text-white">Confidentialité</a>
                            <a href="{{ route('login') }}" class="hover:text-gray-900 dark:hover:text-white">Connexion</a>
                            <a href="{{ url('/client') }}" class="hover:text-gray-900 dark:hover:text-white">App client</a>
                        </div>
                    </div>

                    <div class="lg:col-span-3">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white/90">Réseaux sociaux</p>
                        <div class="mt-4 flex items-center gap-3">
                            <a href="https://www.linkedin.com/company/teranguest" target="_blank" rel="noopener noreferrer"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50 text-gray-700 ring-1 ring-gray-200/70 hover:bg-gray-100 hover:text-gray-900 dark:bg-gray-800/60 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-800 dark:hover:text-white"
                                aria-label="LinkedIn">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M4.98 3.5C4.98 4.88 3.87 6 2.5 6S0 4.88 0 3.5 1.12 1 2.5 1 4.98 2.12 4.98 3.5zM.5 23.5h4V7.98h-4V23.5zM8.5 7.98h3.83v2.12h.05c.53-1 1.84-2.06 3.79-2.06 4.05 0 4.8 2.66 4.8 6.11v9.35h-4v-8.29c0-1.98-.04-4.52-2.76-4.52-2.76 0-3.18 2.15-3.18 4.38v8.43h-4V7.98z" />
                                </svg>
                            </a>
                            <a href="https://wa.me/221770947794" target="_blank" rel="noopener noreferrer"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50 text-gray-700 ring-1 ring-gray-200/70 hover:bg-gray-100 hover:text-gray-900 dark:bg-gray-800/60 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-800 dark:hover:text-white"
                                aria-label="WhatsApp">
                                <svg class="h-5 w-5" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
                                    <path d="M19.11 17.03c-.28-.14-1.64-.81-1.9-.9-.25-.09-.44-.14-.62.14-.18.28-.71.9-.87 1.09-.16.19-.32.21-.6.07-.28-.14-1.17-.43-2.23-1.38-.82-.73-1.38-1.64-1.54-1.92-.16-.28-.02-.43.12-.57.12-.12.28-.32.41-.48.14-.16.18-.28.28-.46.09-.18.05-.35-.02-.49-.07-.14-.62-1.49-.85-2.04-.22-.52-.45-.45-.62-.46l-.53-.01c-.18 0-.49.07-.74.35-.25.28-.97.95-.97 2.32 0 1.37.99 2.69 1.13 2.88.14.18 1.95 2.98 4.72 4.18.66.29 1.17.46 1.57.59.66.21 1.26.18 1.74.11.53-.08 1.64-.67 1.87-1.32.23-.65.23-1.2.16-1.32-.07-.12-.25-.19-.53-.33z" />
                                    <path d="M16.04 3C9.4 3 4 8.4 4 15.04c0 2.12.55 4.19 1.6 6.02L4 29l8.2-1.56c1.75.95 3.71 1.46 5.84 1.46C24.68 28.9 30 23.68 30 17.04 30 10.4 22.68 3 16.04 3zm0 22.07c-1.95 0-3.85-.52-5.51-1.5l-.39-.23-4.87.93.92-4.75-.25-.4c-1.02-1.65-1.56-3.55-1.56-5.49C5.38 9.45 10.45 4.38 16.04 4.38c5.59 0 10.58 5.09 10.58 11.28 0 5.59-4.99 9.41-10.58 9.41z" />
                                </svg>
                            </a>
                            <a href="https://x.com/teranguest" target="_blank" rel="noopener noreferrer"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50 text-gray-700 ring-1 ring-gray-200/70 hover:bg-gray-100 hover:text-gray-900 dark:bg-gray-800/60 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-800 dark:hover:text-white"
                                aria-label="X">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M18.9 2H22l-6.8 7.77L23 22h-6.2l-4.85-6.26L6.5 22H3.4l7.28-8.33L1 2h6.35l4.38 5.67L18.9 2zm-1.08 18h1.72L6.58 3.9H4.74L17.82 20z" />
                                </svg>
                            </a>
                            <a href="https://www.facebook.com/teranguest" target="_blank" rel="noopener noreferrer"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50 text-gray-700 ring-1 ring-gray-200/70 hover:bg-gray-100 hover:text-gray-900 dark:bg-gray-800/60 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-800 dark:hover:text-white"
                                aria-label="Facebook">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M22 12a10 10 0 10-11.56 9.87v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.88 3.77-3.88 1.09 0 2.23.2 2.23.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.88h-2.34v6.99A10 10 0 0022 12z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mt-10 border-t border-gray-200/70 pt-6 dark:border-gray-800/70">
                    <p class="text-sm text-gray-600 dark:text-gray-300">&copy; {{ date('Y') }}. Tous droits réservés.</p>
                </div>
            </div>
        </footer>
    </div>
@endsection
