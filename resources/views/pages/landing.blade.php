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
                            Plateforme hôtel + app invité (mobile / tablette)
                        </div>

                        <h1 class="mt-6 text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl dark:text-white/90">
                            Une expérience client premium, du check-in au room service
                        </h1>

                        <p class="mt-5 text-base leading-7 text-gray-600 dark:text-gray-300">
                            Notre plateforme centralise la gestion hôtelière côté web et offre une app côté invité (mobile / tablette)
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
                                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aperçu</p>
                                            <p class="mt-2 text-xl font-semibold tracking-tight text-gray-900 dark:text-white/90">App invité (mobile + tablette)</p>
                                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Room service, réservations, infos hôtel, chat & assistance.</p>
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

                                    <div class="mt-8 grid gap-4 lg:grid-cols-12">
                                        <div class="lg:col-span-7">
                                            <div class="group flex h-[280px] items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-gray-50 via-white to-gray-50 ring-1 ring-gray-200/70 shadow-theme-lg dark:from-gray-900/40 dark:via-gray-900 dark:to-gray-900 dark:ring-gray-800 sm:h-[340px]">
                                                <img src="{{ $landingAsset('ipad.png') }}" alt="Capture iPad" loading="lazy" decoding="async"
                                                    class="h-full w-full object-contain p-4 transition duration-500 group-hover:scale-[1.01]" />
                                            </div>
                                            <div class="mt-3 flex items-center justify-between">
                                                <span class="text-sm font-semibold text-gray-900 dark:text-white/90">Tablette</span>
                                                <span class="text-sm text-gray-600 dark:text-gray-300">iPad</span>
                                            </div>
                                        </div>
                                        <div class="grid gap-4 lg:col-span-5">
                                            <div class="group flex h-[170px] items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-gray-50 via-white to-gray-50 ring-1 ring-gray-200/70 shadow-theme-lg dark:from-gray-900/40 dark:via-gray-900 dark:to-gray-900 dark:ring-gray-800 sm:h-[190px]">
                                                <img src="{{ $landingAsset('iphone1.png') }}" alt="Capture iPhone 1" loading="lazy" decoding="async"
                                                    class="h-full w-full object-contain p-4 transition duration-500 group-hover:scale-[1.02]" />
                                            </div>
                                            <div class="group flex h-[170px] items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-gray-50 via-white to-gray-50 ring-1 ring-gray-200/70 shadow-theme-lg dark:from-gray-900/40 dark:via-gray-900 dark:to-gray-900 dark:ring-gray-800 sm:h-[190px]">
                                                <img src="{{ $landingAsset('iphone2.png') }}" alt="Capture iPhone 2" loading="lazy" decoding="async"
                                                    class="h-full w-full object-contain p-4 transition duration-500 group-hover:scale-[1.02]" />
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-semibold text-gray-900 dark:text-white/90">Mobile</span>
                                                <span class="text-sm text-gray-600 dark:text-gray-300">iPhone</span>
                                            </div>
                                        </div>
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
                            Un seul écosystème pour gérer l’hôtel et améliorer l’expérience invité. Conçu pour être clair, rapide et
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
                                Côté mobile / tablette (invité)
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

                        <div class="mt-8 grid gap-3">
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
                        </div>
                    </div>

                    <div class="lg:col-span-6">
                        <div class="overflow-hidden rounded-3xl bg-gray-900 ring-1 ring-gray-900/20 shadow-theme-xl dark:ring-gray-800">
                            <div class="px-6 py-5">
                                <p class="text-sm font-semibold text-white">Un écosystème complet</p>
                                <p class="mt-2 text-sm text-white/80">
                                    Administration + expérience invité, avec une base solide pour évoluer.
                                </p>
                            </div>
                            <div class="grid grid-cols-3 gap-2 px-6 pb-6">
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

            <section id="contact" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="rounded-3xl bg-gray-900 px-6 py-10 shadow-theme-xl ring-1 ring-gray-900/20 sm:px-10 sm:py-12">
                    <div class="grid gap-8 lg:grid-cols-12 lg:items-center">
                        <div class="lg:col-span-7">
                            <h2 class="text-3xl font-semibold tracking-tight text-white">
                                Prêt à présenter ton application comme un vrai produit
                            </h2>
                            <p class="mt-4 text-base leading-7 text-white/80">
                                Cette page d’accueil est faite pour expliquer clairement ton app (web + mobile) et accueillir tes photos.
                            </p>
                        </div>
                        <div class="lg:col-span-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                                <a href="{{ route('login') }}"
                                    class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-3 text-sm font-semibold text-gray-900 hover:bg-gray-100">
                                    Se connecter
                                </a>
                                <a href="{{ url('/client') }}"
                                    class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-600">
                                    Ouvrir l’app client
                                </a>
                            </div>
                            <p class="mt-3 text-sm text-white/70 sm:text-right">
                                Politique de confidentialité :
                                <a class="font-semibold text-white underline underline-offset-4 hover:opacity-90" href="{{ route('privacy-policy') }}">
                                    voir la page
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-gray-200/70 bg-white py-10 dark:border-gray-800/70 dark:bg-gray-900">
            <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center rounded-full px-4 py-2" style="background-color: #1E252D; border: 1.5px solid #D4AF37;">
                        <img src="{{ asset('images/logo/logo.png') }}" alt="Teranga Guest" class="h-8 w-auto object-contain" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Web dashboard + app invité.</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm font-medium text-gray-600 dark:text-gray-300">
                    <a href="#fonctionnalites" class="hover:text-gray-900 dark:hover:text-white">Fonctionnalités</a>
                    <a href="#galerie" class="hover:text-gray-900 dark:hover:text-white">Galerie</a>
                    <a href="{{ route('privacy-policy') }}" class="hover:text-gray-900 dark:hover:text-white">Confidentialité</a>
                    <a href="{{ route('login') }}" class="hover:text-gray-900 dark:hover:text-white">Connexion</a>
                </div>
            </div>
        </footer>
    </div>
@endsection
