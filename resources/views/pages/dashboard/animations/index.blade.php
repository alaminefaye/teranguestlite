@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Animations</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Programme & journal (PDF ou image)</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-6 rounded-lg bg-danger-50 p-4 text-danger-600 dark:bg-danger-500/10 dark:text-danger-400">
        <p class="font-medium mb-2">Veuillez corriger les erreurs :</p>
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('dashboard.animations.update') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Programme (PDF ou image)</label>
                @if($programUrl)
                    <div class="mb-2">
                        <a href="{{ $programUrl }}" target="_blank" class="text-sm text-brand-600 dark:text-brand-400 hover:underline">Ouvrir le programme actuel</a>
                    </div>
                @endif
                <input type="file" name="program_file" accept="application/pdf,image/*"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Journal (PDF ou image)</label>
                @if($journalUrl)
                    <div class="mb-2">
                        <a href="{{ $journalUrl }}" target="_blank" class="text-sm text-brand-600 dark:text-brand-400 hover:underline">Ouvrir le journal actuel</a>
                    </div>
                @endif
                <input type="file" name="journal_file" accept="application/pdf,image/*"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <button type="submit" class="px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Enregistrer</button>
    </div>
</form>
@endsection

