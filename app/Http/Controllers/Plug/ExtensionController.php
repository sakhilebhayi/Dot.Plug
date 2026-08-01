<?php

namespace App\Http\Controllers\Plug;

use App\Http\Controllers\Controller;
use App\Models\Extension;
use App\Models\ExtensionVersion;
use App\Models\Installation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Basic marketplace CRUD + install/uninstall for the current team. This is
 * deliberately plain controllers + Blade rather than Livewire, matching the
 * "your choice, but be consistent" MVP guidance — the rest of the copied
 * Jetstream shell (Team settings, Profile) is server-rendered Blade too.
 */
class ExtensionController extends Controller
{
    /**
     * Marketplace listing: every certified extension, with install status
     * for the current team.
     */
    public function index(Request $request): View
    {
        $team = $request->user()->currentTeam;

        $extensions = Extension::query()
            ->where('status', 'certified')
            ->with('developerTeam')
            ->when($request->string('category')->isNotEmpty(), fn ($q) => $q->where('category', $request->string('category')))
            ->orderBy('name')
            ->get();

        $installedExtensionIds = Installation::query()
            ->where('team_id', $team->id)
            ->where('status', 'active')
            ->pluck('extension_id');

        return view('extensions.index', [
            'extensions' => $extensions,
            'installedExtensionIds' => $installedExtensionIds,
            'category' => $request->string('category')->toString(),
        ]);
    }

    /**
     * A single extension's detail page.
     */
    public function show(Request $request, Extension $extension): View
    {
        // Draft/decertified listings are the publisher's own unreleased or
        // pulled work product, not public marketplace content — restrict
        // to the publisher team. Certified listings remain open to any
        // authenticated user, matching the marketplace index's visibility.
        Gate::authorize('view', $extension);

        $team = $request->user()->currentTeam;

        $extension->load('developerTeam', 'versions');

        $isInstalled = $extension->isInstalledBy($team);

        return view('extensions.show', [
            'extension' => $extension,
            'isInstalled' => $isInstalled,
        ]);
    }

    /**
     * New-listing form, for developer teams publishing an extension.
     */
    public function create(): View
    {
        Gate::authorize('create', Extension::class);

        return view('extensions.create');
    }

    /**
     * Publish a new extension listing under the current team (as publisher).
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Extension::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category' => ['required', 'string', 'in:integrations,connectors,analytics,automation,vertical,general'],
            'icon' => ['nullable', 'string', 'max:64'],
        ]);

        $extension = Extension::create([
            'developer_team_id' => $request->user()->currentTeam->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(4),
            'tagline' => $validated['tagline'] ?? null,
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'icon' => $validated['icon'] ?? 'extension',
            // MVP: no certification pipeline yet (see wiki.md roadmap), so
            // self-published listings start life as "draft" rather than
            // being auto-certified.
            'status' => 'draft',
        ]);

        ExtensionVersion::create([
            'extension_id' => $extension->id,
            'version' => '1.0.0',
            'changelog' => 'Initial release.',
            'is_current' => true,
        ]);

        return redirect()->route('extensions.show', $extension)
            ->with('flash.banner', 'Extension listing created as a draft. Certification review is not yet automated (see wiki.md roadmap).');
    }

    /**
     * Install the given extension for the current team.
     */
    public function install(Request $request, Extension $extension): RedirectResponse
    {
        $team = $request->user()->currentTeam;

        abort_if($extension->status !== 'certified', 403, 'Only certified extensions can be installed.');

        $currentVersion = $extension->versions()->where('is_current', true)->first();

        Installation::updateOrCreate(
            ['team_id' => $team->id, 'extension_id' => $extension->id],
            [
                'extension_version_id' => $currentVersion?->id,
                'status' => 'active',
                'installed_at' => now(),
                'uninstalled_at' => null,
            ]
        );

        return redirect()->route('extensions.show', $extension)
            ->with('flash.banner', "{$extension->name} installed for {$team->name}.");
    }

    /**
     * Uninstall the given extension for the current team.
     */
    public function uninstall(Request $request, Extension $extension): RedirectResponse
    {
        $team = $request->user()->currentTeam;

        $installation = Installation::query()
            ->where('team_id', $team->id)
            ->where('extension_id', $extension->id)
            ->first();

        if ($installation) {
            $installation->update([
                'status' => 'uninstalled',
                'uninstalled_at' => now(),
            ]);
        }

        return redirect()->route('extensions.show', $extension)
            ->with('flash.banner', "{$extension->name} uninstalled for {$team->name}.");
    }
}
