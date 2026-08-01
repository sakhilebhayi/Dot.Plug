<x-app-layout>
<div style="padding:2rem 2.5rem 3rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;">
        <div>
            <h1 style="font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:700;color:#f4f4f5;margin:0 0 0.2rem;letter-spacing:-0.01em;">Marketplace</h1>
            <p style="font-size:0.78rem;color:#52525b;margin:0;">{{ $extensions->count() }} certified extension{{ $extensions->count() === 1 ? '' : 's' }} available</p>
        </div>
        <a href="{{ route('extensions.create') }}" class="dot-btn dot-btn-primary">
            <span class="material-symbols-rounded" style="font-size:16px;">add</span>
            Publish an extension
        </a>
    </div>

    <div style="display:flex;gap:0.5rem;margin-bottom:1.5rem;flex-wrap:wrap;">
        <a href="{{ route('extensions.index') }}" class="dot-badge {{ $category === '' ? 'dot-badge-accent' : '' }}" style="{{ $category === '' ? '' : 'background:rgba(255,255,255,0.05);color:#a1a1aa;' }} text-decoration:none;">All</a>
        @foreach (['integrations', 'connectors', 'analytics', 'automation', 'vertical', 'general'] as $cat)
        <a href="{{ route('extensions.index', ['category' => $cat]) }}" class="dot-badge {{ $category === $cat ? 'dot-badge-accent' : '' }}" style="{{ $category === $cat ? '' : 'background:rgba(255,255,255,0.05);color:#a1a1aa;' }} text-decoration:none;text-transform:capitalize;">{{ $cat }}</a>
        @endforeach
    </div>

    @if ($extensions->isEmpty())
    <div class="dot-card" style="padding:3rem;text-align:center;">
        <p style="color:#52525b;font-size:0.85rem;margin:0;">No certified extensions in this category yet.</p>
    </div>
    @else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;">
        @foreach ($extensions as $extension)
        <a href="{{ route('extensions.show', $extension) }}" class="dot-card" style="padding:1.25rem 1.5rem;text-decoration:none;display:block;">
            <div style="display:flex;align-items:center;gap:0.6rem;margin-bottom:0.75rem;">
                <span class="material-symbols-rounded" style="font-size:20px;color:#818cf8;">{{ $extension->icon ?? 'extension' }}</span>
                <div style="font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;color:#f4f4f5;">{{ $extension->name }}</div>
            </div>
            <p style="font-size:0.8rem;color:#a1a1aa;margin:0 0 0.75rem;min-height:2.2em;">{{ $extension->tagline }}</p>
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:0.72rem;color:#52525b;">by {{ $extension->developerTeam->name }}</span>
                @if ($installedExtensionIds->contains($extension->id))
                <span class="dot-badge dot-badge-accent">Installed</span>
                @else
                <span class="dot-badge" style="background:rgba(255,255,255,0.05);color:#71717a;text-transform:capitalize;">{{ $extension->category }}</span>
                @endif
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>
</x-app-layout>
