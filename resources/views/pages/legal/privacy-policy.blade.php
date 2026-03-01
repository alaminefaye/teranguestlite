@extends('layouts.fullscreen-layout')

@section('content')
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl">
            {{-- Header --}}
            <div class="mb-8 flex flex-col items-center sm:flex-row sm:items-center sm:justify-between gap-4">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-brand-500 dark:hover:text-brand-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour à l'accueil
                </a>
                <div class="flex items-center justify-center rounded-full px-4 py-2" style="background-color: #1E252D; border: 1.5px solid #D4AF37;">
                    <img src="{{ asset('images/logo/logo.png') }}" alt="TeranGuest" class="h-8 w-auto object-contain"/>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-theme-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 sm:p-8 lg:p-10">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        Politique de confidentialité
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">
                        Dernière mise à jour : {{ now()->translatedFormat('d F Y') }}
                    </p>

                    <div class="prose prose-gray dark:prose-invert max-w-none space-y-6 text-gray-700 dark:text-gray-300">
                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">1. Introduction</h2>
                            <p>
                                TeranGuest et Universal Technologies Africa s'engagent à protéger la vie privée des utilisateurs de nos services (application d'accès client, réservations, commandes, services hôteliers). Cette politique décrit les données que nous collectons, leur utilisation et vos droits.
                            </p>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">2. Données collectées</h2>
                            <p>Nous pouvons collecter :</p>
                            <ul class="list-disc pl-6 space-y-1 mt-2">
                                <li>Données d'identification (nom, prénom, email, numéro de téléphone)</li>
                                <li>Informations de séjour (dates, chambre, réservations, commandes)</li>
                                <li>Données de connexion (adresse IP, identifiant appareil) dans le cadre du fonctionnement de l'application</li>
                                <li>Données nécessaires à la facturation et au suivi des services</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">3. Finalités du traitement</h2>
                            <p>Vos données sont utilisées pour :</p>
                            <ul class="list-disc pl-6 space-y-1 mt-2">
                                <li>Gérer votre accès à l'application client et à l'établissement</li>
                                <li>Traiter les réservations, commandes et demandes de services</li>
                                <li>Vous contacter en cas de besoin (confirmations, rappels)</li>
                                <li>Respecter nos obligations légales et comptables</li>
                                <li>Améliorer nos services (analyses agrégées, non identifiantes)</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">4. Base légale et conservation</h2>
                            <p>
                                Le traitement repose sur l'exécution du contrat (prestation hôtelière), le consentement lorsque requis, et le respect des obligations légales. Les données sont conservées pendant la durée nécessaire à ces finalités et aux obligations légales (comptabilité, contentieux), puis supprimées ou anonymisées.
                            </p>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">5. Partage des données</h2>
                            <p>
                                Les données peuvent être partagées avec les prestataires techniques qui hébergent ou font fonctionner l'application, dans le cadre strict de la fourniture du service et sous contrat de confidentialité. Nous ne vendons pas vos données personnelles.
                            </p>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">6. Vos droits</h2>
                            <p>Conformément à la réglementation applicable (RGPD et lois locales), vous disposez notamment des droits suivants :</p>
                            <ul class="list-disc pl-6 space-y-1 mt-2">
                                <li><strong>Accès</strong> : obtenir une copie de vos données</li>
                                <li><strong>Rectification</strong> : faire corriger des données inexactes</li>
                                <li><strong>Effacement</strong> : demander la suppression de vos données dans les limites prévues par la loi</li>
                                <li><strong>Limitation / opposition</strong> : dans certains cas, limiter ou vous opposer au traitement</li>
                                <li><strong>Portabilité</strong> : recevoir vos données dans un format structuré</li>
                            </ul>
                            <p class="mt-3">
                                Pour exercer ces droits ou pour toute question relative à vos données personnelles, contactez-nous aux coordonnées ci-dessous. Vous pouvez également introduire une réclamation auprès de l'autorité de contrôle compétente.
                            </p>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">7. Sécurité</h2>
                            <p>
                                Nous mettons en œuvre des mesures techniques et organisationnelles appropriées pour protéger vos données contre l'accès non autorisé, la perte ou l'altération (accès sécurisé, chiffrement, contrats avec des hébergeurs fiables).
                            </p>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">8. Modifications</h2>
                            <p>
                                Nous pouvons mettre à jour cette politique de confidentialité. La date de dernière mise à jour est indiquée en haut de la page. Nous vous encourageons à la consulter périodiquement.
                            </p>
                        </section>

                        <section class="pt-4 border-t border-gray-200 dark:border-gray-600">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Contact</h2>
                            <p class="mb-2">Pour toute question concernant cette politique ou vos données personnelles :</p>
                            <ul class="space-y-1 text-gray-700 dark:text-gray-300">
                                <li>
                                    <strong>Email :</strong>
                                    <a href="mailto:{{ $contactEmail }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400 dark:hover:text-brand-300 underline">{{ $contactEmail }}</a>
                                </li>
                                <li>
                                    <strong>Téléphone :</strong>
                                    @foreach($contactPhones as $phone)
                                        <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400 dark:hover:text-brand-300">{{ $phone }}</a>@if(!$loop->last), @endif
                                    @endforeach
                                </li>
                            </ul>
                            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                                Universal Technologies Africa
                            </p>
                        </section>
                    </div>
                </div>
            </div>

            <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
                &copy; {{ date('Y') }} TeranGuest. Tous droits réservés.
            </p>
        </div>
    </div>
@endsection
