@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Rapports & Audits</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-1">Générez des rapports complets sur l'activité de l'établissement : synthèse, réservations, commandes, facturation, services et journal d'audit. Choisissez la période puis le type de rapport.</p>
</div>

<!-- Période -->
<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Période du rapport</p>
    <form method="GET" action="{{ route('dashboard.reports.index') }}" class="flex flex-wrap gap-4 items-end">
        <div class="min-w-[160px]">
            <x-form.date-picker name="date_from" label="Du" placeholder="Choisir une date" :defaultDate="$date_from" />
        </div>
        <div class="min-w-[160px]">
            <x-form.date-picker name="date_to" label="Au" placeholder="Choisir une date" :defaultDate="$date_to" />
        </div>
        <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Appliquer la période</button>
    </form>
</div>

<!-- Types de rapports -->
<div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
    @foreach($types as $key => $config)
        <a href="{{ route('dashboard.reports.show', $key) }}?date_from={{ $date_from }}&date_to={{ $date_to }}"
            class="block rounded-lg border border-gray-200 bg-white p-5 shadow-theme-sm transition hover:border-brand-300 hover:shadow dark:border-gray-800 dark:bg-gray-900 dark:hover:border-brand-700">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-900/20">
                    <svg class="h-6 w-6 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.5a2 2 0 012 2v14a2 2 0 01-2 2zM15 7v6M12 7v2M9 7v4"></path>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <h3 class="font-semibold text-gray-900 dark:text-white/90">{{ $config['name'] }}</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $config['description'] }}</p>
                    <span class="mt-2 inline-flex items-center text-sm font-medium text-brand-600 dark:text-brand-400">
                        Voir le rapport
                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                </div>
            </div>
        </a>
    @endforeach
</div>
@endsection
