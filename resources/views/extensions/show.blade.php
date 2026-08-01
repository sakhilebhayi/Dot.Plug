<x-app-layout>
<div style="padding:2rem 2.5rem 3rem;">
    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('extensions.index') }}" style="font-size:0.78rem;color:#71717a;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
            <span class="material-symbols-rounded" style="font-size:16px;">arrow_back</span>
            Back to marketplace
        </a>
    </div>

    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
        <div style="display:flex;align-items:center;gap:0.85rem;">
            <span class="material-symbols-rounded" style="font-size:32px;color:#818cf8;">{{ $extension->icon ?? 'extension' }}</span>
            <div>
                <h1 style="font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:700;color:#f4f4f5;margin:0 0 0.2rem;letter-spacing:-0.01em;">{{ $extension->name }}</h1>
                <p style="font-size:0.78rem;color:#52525b;margin:0;">by {{ $extension->developerTeam->name }} &middot; <span style="text-transform:capitalize;">{{ $extension->category }}</span></p>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:0.6rem;">
            <span class="dot-badge" style="{{ $extension->status === 'certified' ? 'background:rgba(34,197,94,0.1);color:#22c55e;' : ($extension->status === 'decertified' ? 'background:rgba(239,68,68,0.1);color:#ef4444;' : 'background:rgba(245,158,11,0.1);color:#f59e0b;') }}">
                {{ ucfirst($extension->status) }}
            </span>

            @if ($extension->status === 'certified')
                @if ($isInstalled)
                <form method="POST" action="{{ route('extensions.uninstall', $extension) }}">
                    @csrf
                    <button type="submit" class="dot-btn dot-btn-ghost">Uninstall</button>
                </form>
                @else
                <form method="POST" action="{{ route('extensions.install', $extension) }}">
                    @csrf
                    <button type="submit" class="dot-btn dot-btn-primary">Install</button>
                </form>
                @endif
            @endif
        </div>
    </div>

    @if ($extension->tagline)
    <p style="font-size:0.9rem;color:#d4d4d8;margin:0 0 1.5rem;">{{ $extension->tagline }}</p>
    @endif

    <div class="dot-card" style="padding:1.5rem;margin-bottom:1.25rem;">
        <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.09em;color:#52525b;margin-bottom:0.75rem;">Description</div>
        <p style="font-size:0.85rem;color:#a1a1aa;line-height:1.7;margin:0;white-space:pre-line;">{{ $extension->description ?? 'No description provided.' }}</p>
    </div>

    <div class="dot-card" style="padding:1.5rem;">
        <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.09em;color:#52525b;margin-bottom:0.75rem;">Version history</div>
        @forelse ($extension->versions as $version)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.06);">
            <div>
                <span class="metric-val" style="font-size:0.85rem;color:#f4f4f5;">{{ $version->version }}</span>
                @if ($version->is_current)
                <span class="dot-badge dot-badge-accent" style="margin-left:0.5rem;">Current</span>
                @endif
                <p style="font-size:0.75rem;color:#71717a;margin:0.2rem 0 0;">{{ $version->changelog }}</p>
            </div>
            <span style="font-size:0.72rem;color:#52525b;">{{ $version->created_at->format('M d, Y') }}</span>
        </div>
        @empty
        <p style="font-size:0.8rem;color:#52525b;margin:0;">No versions published yet.</p>
        @endforelse
    </div>
</div>
</x-app-layout>
