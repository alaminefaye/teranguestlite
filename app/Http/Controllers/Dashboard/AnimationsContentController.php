<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AnimationsContentController extends Controller
{
    public function index(): View
    {
        $enterprise = auth()->user()->enterprise;
        $settings = is_array($enterprise->settings) ? $enterprise->settings : [];
        $animations = $settings['animations'] ?? [];

        $programUrl = $animations['program_url'] ?? null;
        if (!$programUrl && !empty($animations['program_path'])) {
            $programUrl = asset('storage/' . $animations['program_path']);
        }
        $journalUrl = $animations['journal_url'] ?? null;
        if (!$journalUrl && !empty($animations['journal_path'])) {
            $journalUrl = asset('storage/' . $animations['journal_path']);
        }

        return view('pages.dashboard.animations.index', [
            'title' => 'Animations',
            'programUrl' => $programUrl,
            'journalUrl' => $journalUrl,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'program_file' => 'nullable|file|max:30720|mimes:pdf,jpg,jpeg,png',
            'journal_file' => 'nullable|file|max:30720|mimes:pdf,jpg,jpeg,png',
        ]);

        $enterprise = auth()->user()->enterprise;
        $settings = is_array($enterprise->settings) ? $enterprise->settings : [];
        $animations = is_array($settings['animations'] ?? null) ? $settings['animations'] : [];

        if ($request->hasFile('program_file')) {
            if (!empty($animations['program_path'])) {
                Storage::disk('public')->delete($animations['program_path']);
            }
            $animations['program_path'] = $request->file('program_file')->store('animations', 'public');
            unset($animations['program_url']);
        }

        if ($request->hasFile('journal_file')) {
            if (!empty($animations['journal_path'])) {
                Storage::disk('public')->delete($animations['journal_path']);
            }
            $animations['journal_path'] = $request->file('journal_file')->store('animations', 'public');
            unset($animations['journal_url']);
        }

        $settings['animations'] = $animations;
        $enterprise->update(['settings' => $settings]);

        return redirect()->route('dashboard.animations.index')
            ->with('success', 'Animations mises à jour.');
    }
}

