<?php

use App\Http\Controllers\Auth\EcosystemAuthController;
use App\Http\Controllers\Plug\ExtensionController;
use App\Models\Extension;
use App\Models\Installation;
use Illuminate\Support\Facades\Route;

Route::get('/auth/ecosystem', [EcosystemAuthController::class, 'handle'])
    ->name('ecosystem.auth');

Route::get('/', fn () => view('welcome'));

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        $team = auth()->user()->currentTeam;

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
