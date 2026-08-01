<x-app-layout>
<div style="padding:2rem 2.5rem 3rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;">
        <div>
            <h1 style="font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:700;color:#f4f4f5;margin:0 0 0.2rem;letter-spacing:-0.01em;">Dashboard</h1>
            <p style="font-size:0.78rem;color:#52525b;margin:0;">{{ now()->format('l, F j, Y') }}</p>
        </div>
        <a href="{{ route('extensions.index') }}" class="dot-btn dot-btn-primary">
            <span class="material-symbols-rounded" style="font-size:16px;">storefront</span>
            Browse marketplace
        </a>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:2rem;">
        <div class="dot-card" style="padding:1.25rem 1.5rem;">
            <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.09em;color:#52525b;margin-bottom:0.75rem;">Installed Extensions</div>
            <div class="metric-val" style="font-size:2rem;font-weight:600;color:#818cf8;">{{ $installedCount }}</div>
        </div>
        <div class="dot-card" style="padding:1.25rem 1.5rem;">
            <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.09em;color:#52525b;margin-bottom:0.75rem;">Published Extensions</div>
            <div class="metric-val" style="font-size:2rem;font-weight:600;color:#f4f4f5;">{{ $publishedCount }}</div>
        </div>
        <div class="dot-card" style="padding:1.25rem 1.5rem;">
            <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.09em;color:#52525b;margin-bottom:0.75rem;">Certified</div>
            <div class="metric-val" style="font-size:2rem;font-weight:600;color:#22c55e;">{{ $certifiedCount }}</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
        <div class="dot-card" style="padding:1.5rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.09em;color:#52525b;">Installed by this team</div>
                <a href="{{ route('extensions.index') }}" style="font-size:0.72rem;color:#818cf8;text-decoration:none;">Browse &rarr;</a>
            </div>
            @forelse ($installedExtensions as $extension)
            <a href="{{ route('extensions.show', $extension) }}" style="display:flex;align-items:center;gap:0.6rem;padding:0.55rem 0;border-bottom:1px solid rgba(255,255,255,0.06);text-decoration:none;color:inherit;">
                <span class="material-symbols-rounded" style="font-size:18px;color:#818cf8;">{{ $extension->icon ?? 'extension' }}</span>
                <span style="font-size:0.82rem;color:#d4d4d8;">{{ $extension->name }}</span>
            </a>
            @empty
            <p style="font-size:0.8rem;color:#52525b;margin:0;">No extensions installed yet.</p>
            @endforelse
        </div>

        <div class="dot-card" style="padding:1.5rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.09em;color:#52525b;">Published by this team</div>
                <a href="{{ route('extensions.create') }}" style="font-size:0.72rem;color:#818cf8;text-decoration:none;">Publish new &rarr;</a>
            </div>
            @forelse ($publishedExtensions as $extension)
            <a href="{{ route('extensions.show', $extension) }}" style="display:flex;align-items:center;justify-content:space-between;padding:0.55rem 0;border-bottom:1px solid rgba(255,255,255,0.06);text-decoration:none;color:inherit;">
                <span style="display:flex;align-items:center;gap:0.6rem;">
                    <span class="material-symbols-rounded" style="font-size:18px;color:#818cf8;">{{ $extension->icon ?? 'extension' }}</span>
                    <span style="font-size:0.82rem;color:#d4d4d8;">{{ $extension->name }}</span>
                </span>
                <span class="dot-badge" style="{{ $extension->status === 'certified' ? 'background:rgba(34,197,94,0.1);color:#22c55e;' : 'background:rgba(245,158,11,0.1);color:#f59e0b;' }}">{{ ucfirst($extension->status) }}</span>
            </a>
            @empty
            <p style="font-size:0.8rem;color:#52525b;margin:0;">This team hasn't published any extensions yet.</p>
            @endforelse
        </div>
    </div>
</div>
</x-app-layout>
