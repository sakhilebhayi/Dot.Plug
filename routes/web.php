<?php

use App\Http\Controllers\Auth\EcosystemAuthController;
use App\Http\Controllers\Plug\ExtensionController;
use App\Models\Extension;
use App\Models\Installation;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Jetstream\Jetstream;

Route::get('/auth/ecosystem', [EcosystemAuthController::class, 'handle'])
    ->name('ecosystem.auth');

Route::get('/', fn () => view('welcome'));

// Cookie Policy — Jetstream's termsAndPrivacyPolicy feature covers terms.show/policy.show
// natively (registered at /terms-of-service and /privacy-policy, reading resources/markdown/
// terms.md and policy.md). There's no Jetstream equivalent for a Cookie Policy, so this one is
// wired by hand, following the exact same Markdown-source convention.
Route::get('/cookies', function () {
    return view('cookies', [
        'cookies' => Str::markdown(file_get_contents(Jetstream::localizedMarkdownPath('cookies.md'))),
    ]);
})->name('cookies');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        // A user can reach this route with no active team: Jetstream lets a
        // member leave (or be removed from) their last remaining team,
        // which nulls out current_team_id without signing them out. There
        // is no team-context middleware in this app to intercept that case
        // upstream, so the route itself must guard against a null
        // currentTeam before dereferencing ->id below.
        $team = auth()->user()->currentTeam;

        if (! $team) {
            return redirect()->route('teams.create');
        }

        $installedExtensions = Installation::where('team_id', $team->id)
            ->where('status', 'active')
            ->with('extension')
            ->get()
            ->pluck('extension')
            ->filter();

        $publishedExtensions = Extension::where('developer_team_id', $team->id)->get();

        return view('dashboard', [
            'installedExtensions' => $installedExtensions,
            'publishedExtensions' => $publishedExtensions,
            'installedCount' => $installedExtensions->count(),
            'publishedCount' => $publishedExtensions->count(),
            'certifiedCount' => $publishedExtensions->where('status', 'certified')->count(),
        ]);
    })->name('dashboard');

    Route::get('/extensions', [ExtensionController::class, 'index'])->name('extensions.index');
    Route::get('/extensions/create', [ExtensionController::class, 'create'])->name('extensions.create');
    Route::post('/extensions', [ExtensionController::class, 'store'])->name('extensions.store');
    Route::get('/extensions/{extension}', [ExtensionController::class, 'show'])->name('extensions.show');
    Route::post('/extensions/{extension}/install', [ExtensionController::class, 'install'])->name('extensions.install');
    Route::post('/extensions/{extension}/uninstall', [ExtensionController::class, 'uninstall'])->name('extensions.uninstall');
});
