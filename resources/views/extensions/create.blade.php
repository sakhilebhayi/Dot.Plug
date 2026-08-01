<x-app-layout>
<div style="padding:2rem 2.5rem 3rem;max-width:640px;">
    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('extensions.index') }}" style="font-size:0.78rem;color:#71717a;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
            <span class="material-symbols-rounded" style="font-size:16px;">arrow_back</span>
            Back to marketplace
        </a>
    </div>

    <h1 style="font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:700;color:#f4f4f5;margin:0 0 0.4rem;letter-spacing:-0.01em;">Publish an extension</h1>
    <p style="font-size:0.8rem;color:#52525b;margin:0 0 1.5rem;">
        Published under <strong style="color:#a1a1aa;">{{ auth()->user()->currentTeam->name }}</strong> as the developer team.
        Listings start as drafts &mdash; the certification pipeline described in wiki.md isn't built yet, so nothing here is auto-approved.
    </p>

    @if ($errors->any())
    <div class="dot-card" style="padding:1rem 1.25rem;margin-bottom:1.25rem;border-color:rgba(239,68,68,0.3);">
        <ul style="margin:0;padding-left:1.1rem;color:#ef4444;font-size:0.8rem;">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('extensions.store') }}" class="dot-card" style="padding:1.5rem;display:flex;flex-direction:column;gap:1.1rem;">
        @csrf

        <div>
            <label for="name" style="display:block;font-size:0.75rem;font-weight:600;color:#a1a1aa;margin-bottom:0.4rem;">Name</label>
            <input id="name" name="name" type="text" class="dot-input" value="{{ old('name') }}" required autofocus>
        </div>

        <div>
            <label for="tagline" style="display:block;font-size:0.75rem;font-weight:600;color:#a1a1aa;margin-bottom:0.4rem;">Tagline</label>
            <input id="tagline" name="tagline" type="text" class="dot-input" value="{{ old('tagline') }}" maxlength="255">
        </div>

        <div>
            <label for="description" style="display:block;font-size:0.75rem;font-weight:600;color:#a1a1aa;margin-bottom:0.4rem;">Description</label>
            <textarea id="description" name="description" rows="5" class="dot-input" style="resize:vertical;">{{ old('description') }}</textarea>
        </div>

        <div>
            <label for="category" style="display:block;font-size:0.75rem;font-weight:600;color:#a1a1aa;margin-bottom:0.4rem;">Category</label>
            <select id="category" name="category" class="dot-input">
                @foreach (['integrations', 'connectors', 'analytics', 'automation', 'vertical', 'general'] as $cat)
                <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }} style="text-transform:capitalize;">{{ ucfirst($cat) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="icon" style="display:block;font-size:0.75rem;font-weight:600;color:#a1a1aa;margin-bottom:0.4rem;">Icon (Material Symbols name, optional)</label>
            <input id="icon" name="icon" type="text" class="dot-input" value="{{ old('icon') }}" placeholder="extension">
        </div>

        <div style="display:flex;justify-content:flex-end;">
            <button type="submit" class="dot-btn dot-btn-primary">Publish listing</button>
        </div>
    </form>
</div>
</x-app-layout>
