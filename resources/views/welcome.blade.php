<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dot.Plug — Developer Portal &amp; Extension Marketplace</title>
    <meta name="description" content="Build, certify, and publish extensions that add capability to any Dot platform without touching that platform's core codebase.">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{background:#0f0d0a;color:#e4e4e7;font-family:'Inter',system-ui,sans-serif;font-size:15px;line-height:1.6;overflow-x:hidden}
        :root{--accent:#fb923c;--accent-soft:rgba(251,146,60,0.12)}
        a{color:inherit}
        h1,h2,h3{font-family:'Space Grotesk',sans-serif}
        .wrap{max-width:1180px;margin:0 auto;padding-inline:max(1.5rem,5vw)}
        .btn-primary{display:inline-flex;align-items:center;gap:8px;padding:12px 26px;border-radius:10px;background:var(--accent);color:#0f0d0a;font-weight:700;text-decoration:none;transition:filter .15s}
        .btn-primary:hover{filter:brightness(1.12)}
        .btn-ghost{display:inline-flex;align-items:center;gap:8px;padding:12px 26px;border-radius:10px;background:transparent;border:1px solid rgba(255,255,255,0.14);color:#a1a1aa;text-decoration:none;font-weight:600;transition:all .15s}
        .btn-ghost:hover{border-color:rgba(251,146,60,0.5);color:#f4f4f5}
        .badge{display:inline-flex;align-items:center;gap:7px;padding:6px 14px;background:var(--accent-soft);border:1px solid rgba(251,146,60,0.3);border-radius:100px;font-size:12px;font-weight:600;color:#fdba74}
        .card{background:#171310;border:1px solid rgba(255,255,255,0.08);border-radius:14px;padding:1.75rem;transition:border-color .2s}
        .card:hover{border-color:rgba(251,146,60,0.35)}
        .card-icon{width:44px;height:44px;border-radius:12px;background:var(--accent-soft);border:1px solid rgba(251,146,60,0.25);display:flex;align-items:center;justify-content:center;margin-bottom:1.1rem;font-size:20px}
        .status-pill{display:inline-block;padding:3px 10px;border-radius:100px;font-size:11px;font-weight:700;letter-spacing:.03em;}
        .status-draft{background:rgba(161,161,170,0.15);color:#a1a1aa;}
        .status-certified{background:rgba(251,146,60,0.15);color:#fdba74;}
    </style>
</head>
<body>
    {{-- Nav --}}
    <nav style="position:sticky;top:0;z-index:50;background:rgba(15,13,10,0.85);backdrop-filter:blur(14px);border-bottom:1px solid rgba(255,255,255,0.06);">
        <div class="wrap" style="height:64px;display:flex;align-items:center;justify-content:space-between;">
            <a href="/" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
                <img src="{{ asset('images/logo.png') }}" alt="Dot.Plug" style="height:34px;width:auto;">
                <span style="font-family:'Space Grotesk',sans-serif;font-size:16px;font-weight:700;letter-spacing:-0.01em;color:#f4f4f5;">Dot.Plug</span>
            </a>
            <div style="display:flex;align-items:center;gap:12px;">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-primary" style="padding:9px 20px;font-size:14px;">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn-ghost" style="padding:9px 20px;font-size:14px;">Sign in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-primary" style="padding:9px 20px;font-size:14px;">Get started</a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <section style="position:relative;padding:8rem max(1.5rem,5vw) 6rem;overflow:hidden;">
        <!-- Photographic Background: real blue-circuit-board photo by Umberto (@umby), unsplash.com/photos/blue-circuit-board-jXd2FSvcRr8 -->
        <div style="position:absolute;inset:0;background-image:url('https://images.unsplash.com/photo-1562408590-e32931084e23?q=80&amp;w=2400&amp;auto=format&amp;fit=crop');background-size:cover;background-position:center;"></div>
        <div style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(15,13,10,0.88) 0%,rgba(15,13,10,0.93) 55%,#0f0d0a 100%);"></div>
        <div style="position:absolute;inset:0;background:linear-gradient(90deg,#0f0d0a 0%,rgba(15,13,10,0.55) 45%,transparent 80%);"></div>

        <div class="wrap" style="position:relative;max-width:760px;">
            <div class="badge">
                <span>Developer Portal &amp; Extension Marketplace</span>
            </div>
            <h1 style="font-size:clamp(2.3rem,5.5vw,3.6rem);font-weight:700;color:#f4f4f5;line-height:1.12;letter-spacing:-0.02em;margin:1.4rem 0 1.3rem;">
                Build once. Plug into<br>any Dot platform
            </h1>
            <p style="font-size:1.08rem;color:#a1a1aa;max-width:600px;margin-bottom:2.2rem;line-height:1.7;">
                Dot.Plug is where third-party developers build, certify, and publish extensions — integrations, connectors, and vertical tools — that add capability to any Dot platform without requiring changes to that platform's core codebase. We own the extension lifecycle; what an extension does inside its own granted scope is the publisher's business.
            </p>
            <div style="display:flex;gap:14px;flex-wrap:wrap;">
                @guest
                    <a href="{{ route('register') }}" class="btn-primary">Get started</a>
                    <a href="#features" class="btn-ghost">Explore the marketplace</a>
                @endguest
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-primary">Go to Dashboard</a>
                @endauth
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section id="features" style="padding:1rem max(1.5rem,5vw) 5rem;">
        <div class="wrap">
            <div style="text-align:center;max-width:640px;margin:0 auto 3rem;">
                <h2 style="font-size:2rem;font-weight:700;color:#f4f4f5;letter-spacing:-0.02em;margin-bottom:0.75rem;">Govern the touch, not the extension</h2>
                <p style="color:#a1a1aa;font-size:15px;">Our job is narrow and specific: own build, certify, grant, run, retire.</p>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1.25rem;">
                <div class="card">
                    <div class="card-icon">🧩</div>
                    <h3 style="font-size:1rem;font-weight:700;color:#f4f4f5;margin-bottom:0.5rem;">Extension Marketplace</h3>
                    <p style="font-size:13.5px;color:#a1a1aa;">List, publish, install, and manage extensions — integrations, connectors, and vertical tools — per team.</p>
                </div>
                <div class="card">
                    <div class="card-icon">🏷️</div>
                    <h3 style="font-size:1rem;font-weight:700;color:#f4f4f5;margin-bottom:0.5rem;">Versioned Releases</h3>
                    <p style="font-size:13.5px;color:#a1a1aa;">Every extension version carries its own changelog record, with a clear current-version flag.</p>
                </div>
                <div class="card">
                    <div class="card-icon">✅</div>
                    <h3 style="font-size:1rem;font-weight:700;color:#f4f4f5;margin-bottom:0.5rem;">Certification Status</h3>
                    <p style="font-size:13.5px;color:#a1a1aa;margin-bottom:0.75rem;">Every extension carries a clear lifecycle state your team can check before installing.</p>
                    <div><span class="status-pill status-draft">Draft</span> &nbsp; <span class="status-pill status-certified">Certified</span></div>
                </div>
                <div class="card">
                    <div class="card-icon">👥</div>
                    <h3 style="font-size:1rem;font-weight:700;color:#f4f4f5;margin-bottom:0.5rem;">Team-Owned Publishing</h3>
                    <p style="font-size:13.5px;color:#a1a1aa;">Any team in the Dot Ecosystem can become a publisher — no separate developer account required.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section style="padding:2rem max(1.5rem,5vw) 7rem;text-align:center;">
        <div class="wrap" style="max-width:600px;padding:3rem 2.5rem;background:#171310;border:1px solid rgba(251,146,60,0.18);border-radius:20px;">
            <h2 style="font-size:1.7rem;font-weight:700;color:#f4f4f5;letter-spacing:-0.02em;margin-bottom:0.75rem;">Extend your platform without forking it</h2>
            <p style="font-size:14px;color:#a1a1aa;margin-bottom:2rem;">Publish an extension, or install one from another team — all without touching your platform's core.</p>
            @guest
                <a href="{{ route('register') }}" class="btn-primary">Create your free account</a>
            @else
                <a href="{{ url('/dashboard') }}" class="btn-primary">Go to your Dashboard</a>
            @endguest
        </div>
    </section>

    {{-- Footer --}}
    <footer style="border-top:1px solid rgba(255,255,255,0.06);padding:2.5rem max(1.5rem,5vw);">
        <div class="wrap" style="display:flex;flex-direction:column;align-items:center;gap:1rem;text-align:center;">
            <img src="{{ asset('images/logo.png') }}" alt="Dot.Plug" style="height:30px;width:auto;opacity:0.9;">
            <p style="font-size:12px;color:#52525b;">&copy; {{ date('Y') }} Dot.Plug · Developer portal &amp; extension marketplace for the Dot Ecosystem</p>
        </div>
    </footer>
</body>
</html>
