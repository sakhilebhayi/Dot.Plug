<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Dot.Plug — The developer portal for the Dot Ecosystem</title>
        <meta name="description" content="Build, certify, and publish extensions that add capability to any Dot platform without touching that platform's core codebase.">

        <!-- Favicon -->
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Karla:wght@400;500;600;700&family=Fira+Code:wght@400;500;600&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --ink: #17121f;
                --ink-soft: #1e1828;
                --violet: #6b4c93;
                --violet-soft: #8567ab;
                --gold: #f0c33a;
                --gold-soft: #f5d573;
                --paper: #f1eef5;
                --mist: #a89bb8;
                --line: rgba(241, 238, 245, 0.1);
                --font-display: 'Manrope', system-ui, sans-serif;
                --font-body: 'Karla', system-ui, sans-serif;
                --font-mono: 'Fira Code', ui-monospace, monospace;
                --ease-out: cubic-bezier(0.23, 1, 0.32, 1);
            }
            html { background: var(--ink); }
            body { font-family: var(--font-body); background: var(--ink); color: var(--paper); }
            .font-display { font-family: var(--font-display); }
            .font-mono { font-family: var(--font-mono); }

            .press { transition: transform 160ms var(--ease-out); }
            .press:active { transform: scale(0.97); }

            @media (prefers-reduced-motion: no-preference) {
                .reveal {
                    opacity: 0;
                    transform: translateY(14px);
                    transition: opacity 600ms var(--ease-out), transform 600ms var(--ease-out);
                }
                .reveal.is-visible { opacity: 1; transform: translateY(0); }
            }
            @media (prefers-reduced-motion: reduce) {
                .reveal { opacity: 1; transform: none; }
            }

            @media (hover: hover) and (pointer: fine) {
                .row-hover:hover { background: rgba(241, 238, 245, 0.03); }
                .link-underline { background-size: 0% 1px; }
                .link-underline:hover { background-size: 100% 1px; }
            }
            .link-underline {
                background-image: linear-gradient(currentColor, currentColor);
                background-position: 0 100%;
                background-repeat: no-repeat;
                transition: background-size 220ms var(--ease-out);
            }
        </style>
    </head>
    <body class="antialiased">

        <!-- Nav -->
        <header
            id="site-header"
            class="fixed top-0 left-0 right-0 z-50 transition-colors duration-300 border-b border-transparent"
        >
            <nav class="max-w-[1400px] mx-auto px-5 sm:px-8 py-3 flex items-center justify-between">
                <a href="/" class="flex items-center gap-2.5 press">
                    <img src="{{ asset('images/logo-light.png') }}" alt="Dot.Plug" class="h-14 sm:h-[4.5rem] w-auto">
                </a>

                <div class="hidden md:flex items-center gap-8 font-mono text-[13px] tracking-wide uppercase text-[var(--mist)]">
                    <a href="#marketplace" class="link-underline hover:text-[var(--paper)] pb-0.5">Marketplace</a>
                    <a href="#lifecycle" class="link-underline hover:text-[var(--paper)] pb-0.5">Lifecycle</a>
                </div>

                @if (Route::has('login'))
                    <div class="flex items-center gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="press flex items-center gap-2 px-5 py-2.5 bg-[var(--gold)] hover:bg-[var(--gold-soft)] text-[#17121f] text-sm font-display font-semibold rounded-lg transition-colors">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="hidden sm:block text-sm font-medium text-[var(--mist)] hover:text-[var(--paper)] transition-colors">
                                Sign in
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="press px-5 py-2.5 bg-[var(--gold)] hover:bg-[var(--gold-soft)] text-[#17121f] text-sm font-display font-semibold rounded-lg transition-colors">
                                    Get started
                                </a>
                            @endif
                        @endauth

                        <button id="menu-toggle" class="md:hidden press p-2 -mr-2 text-[var(--paper)]" aria-label="Toggle menu" aria-expanded="false">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path id="icon-open" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 7h16M4 12h16M4 17h16"></path>
                                <path id="icon-close" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endif
            </nav>

            <div id="mobile-menu" class="hidden md:hidden border-t border-[var(--line)] bg-[#17121f]">
                <div class="flex flex-col px-5 py-4 gap-1 font-mono text-sm uppercase tracking-wide">
                    <a href="#marketplace" class="px-3 py-2.5 text-[var(--mist)] hover:text-[var(--paper)]">Marketplace</a>
                    <a href="#lifecycle" class="px-3 py-2.5 text-[var(--mist)] hover:text-[var(--paper)]">Lifecycle</a>
                    @guest
                        <a href="{{ route('login') }}" class="px-3 py-2.5 text-[var(--mist)] hover:text-[var(--paper)]">Sign in</a>
                    @endguest
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section class="relative min-h-[100dvh] flex items-end overflow-hidden">
            <div class="absolute inset-0" style="background: radial-gradient(ellipse 80% 60% at 15% 0%, rgba(107,76,147,0.16) 0%, transparent 60%), var(--ink);"></div>

            <!-- Signature element: line-art plug + sockets — echoes the logo's own plug icon and "plug into any Dot platform" -->
            <svg class="hidden lg:block absolute right-[5%] bottom-[8%] h-[64%] w-auto opacity-[0.16] pointer-events-none" viewBox="0 0 260 300" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M60 280L120 220" stroke="#f1eef5" stroke-width="4" stroke-linecap="round"/>
                <path d="M110 190C90 210 90 240 110 260C130 280 160 280 180 260L200 240L130 170L110 190Z" stroke="#f1eef5" stroke-width="3" stroke-linejoin="round"/>
                <path d="M170 130L140 160M210 170L180 200" stroke="#f1eef5" stroke-width="4" stroke-linecap="round"/>
                <!-- sockets it can plug into, representing other Dot platforms -->
                <rect x="30" y="40" width="34" height="46" rx="4" stroke="#f0c33a" stroke-width="2.5"/>
                <circle cx="40" cy="56" r="2.5" fill="#f0c33a"/>
                <circle cx="54" cy="56" r="2.5" fill="#f0c33a"/>
                <rect x="90" y="30" width="34" height="46" rx="4" stroke="#f0c33a" stroke-width="2.5" opacity="0.6"/>
                <circle cx="100" cy="46" r="2.5" fill="#f0c33a" opacity="0.6"/>
                <circle cx="114" cy="46" r="2.5" fill="#f0c33a" opacity="0.6"/>
            </svg>

            <div class="relative z-10 max-w-[1400px] mx-auto px-5 sm:px-8 pt-32 pb-16 sm:pb-20 w-full">
                <div class="max-w-2xl reveal" data-reveal>
                    <p class="font-mono text-xs tracking-[0.18em] uppercase text-[var(--gold)] mb-6">
                        Developer portal &amp; extension marketplace
                    </p>

                    <h1 class="font-display font-bold text-4xl sm:text-5xl lg:text-6xl leading-[1.05] tracking-tight text-[var(--paper)] mb-6">
                        Build once.<br>Plug into any Dot platform.
                    </h1>

                    <p class="text-lg text-[var(--mist)] leading-relaxed max-w-xl mb-10">
                        Dot.Plug is where third-party developers build, certify, and publish extensions — integrations, connectors, and vertical tools — that add capability to any Dot platform without touching that platform's core codebase. We govern the touch, not the extension.
                    </p>

                    @guest
                        <div class="flex flex-wrap items-center gap-4">
                            <a href="{{ route('register') }}" class="press px-7 py-3.5 bg-[var(--gold)] hover:bg-[var(--gold-soft)] text-[#17121f] font-display font-semibold rounded-lg transition-colors">
                                Get started
                            </a>
                            <a href="#marketplace" class="press flex items-center gap-2 px-7 py-3.5 text-[var(--paper)] font-medium rounded-lg border border-[var(--line)] hover:border-[var(--mist)] transition-colors">
                                Explore the marketplace
                            </a>
                        </div>
                    @endguest
                </div>
            </div>

            <!-- Status strip — real certification states from wiki.md §3, not fabricated metrics -->
            <div class="relative z-10 w-full border-t border-[var(--line)] bg-[#17121f]/60 backdrop-blur-sm">
                <div class="max-w-[1400px] mx-auto px-5 sm:px-8 py-4 flex flex-wrap gap-x-8 gap-y-2 font-mono text-[11px] tracking-[0.14em] uppercase text-[var(--mist)]">
                    <span>Any team can publish</span>
                    <span class="text-[var(--gold)]">·</span>
                    <span>Versioned releases</span>
                    <span class="text-[var(--gold)]">·</span>
                    <span>Draft, certified, decertified</span>
                    <span class="text-[var(--gold)]">·</span>
                    <span>No core fork required</span>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section id="marketplace" class="py-24 sm:py-28 px-5 sm:px-8">
            <div class="max-w-[1400px] mx-auto">
                <div class="max-w-xl mb-16 reveal" data-reveal>
                    <p class="font-mono text-xs tracking-[0.18em] uppercase text-[var(--gold)] mb-4">Govern the touch, not the extension</p>
                    <h2 class="font-display font-semibold text-3xl sm:text-4xl text-[var(--paper)] leading-tight">
                        What an extension does in its own scope is the publisher's business
                    </h2>
                </div>

                <div class="grid md:grid-cols-2 border-t border-[var(--line)]">
                    @php
                        $features = [
                            ['tag' => 'Marketplace', 'title' => 'List, publish, install', 'body' => 'List, publish, view, install, and uninstall extensions — integrations, connectors, and vertical tools — per team.'],
                            ['tag' => 'Releases', 'title' => 'Versioned releases', 'body' => 'Every extension version carries its own changelog record, with a clear current-version flag.'],
                            ['tag' => 'Status', 'title' => 'Certification state', 'body' => 'Every extension carries a lifecycle state — draft, certified, or decertified — your team can check before installing.'],
                            ['tag' => 'Publishing', 'title' => 'Team-owned publishing', 'body' => 'Any team in the Dot Ecosystem can become a publisher. No separate developer account, no gatekept signup.'],
                            ['tag' => 'Scope', 'title' => 'Capability-scoped by design', 'body' => 'The ecosystem governs how an extension is allowed to touch a platform, not what the extension is. No shortcuts, no extra tax for being third-party.'],
                            ['tag' => 'Trust', 'title' => 'A governed boundary where it matters', 'body' => 'The moment an extension publishes into or consumes from the shared intelligence layer, the same rules apply to every publisher.'],
                        ];
                    @endphp
                    @foreach ($features as $i => $f)
                        <div class="row-hover border-b border-[var(--line)] {{ $i % 2 === 0 ? 'md:border-r' : '' }} px-1 py-8 sm:py-10 transition-colors reveal" data-reveal>
                            <p class="font-mono text-[11px] tracking-[0.14em] uppercase text-[var(--gold)] mb-3">{{ $f['tag'] }}</p>
                            <h3 class="font-display font-semibold text-xl text-[var(--paper)] mb-2.5">{{ $f['title'] }}</h3>
                            <p class="text-[var(--mist)] leading-relaxed max-w-md">{{ $f['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Lifecycle — the five-stage model from wiki.md §1, styled as the platform's own data artifact -->
        <section id="lifecycle" class="py-24 sm:py-28 px-5 sm:px-8 bg-[var(--ink-soft)] border-y border-[var(--line)]">
            <div class="max-w-[1400px] mx-auto">
                <div class="grid lg:grid-cols-[minmax(0,1fr)_minmax(0,1.6fr)] gap-12 lg:gap-20">
                    <div class="reveal" data-reveal>
                        <p class="font-mono text-xs tracking-[0.18em] uppercase text-[var(--gold)] mb-4">The lifecycle we own</p>
                        <h2 class="font-display font-semibold text-3xl sm:text-4xl text-[var(--paper)] leading-tight mb-5">
                            Build, certify, grant, run, retire
                        </h2>
                        <p class="text-[var(--mist)] leading-relaxed max-w-sm">
                            Dot.Plug's job is narrow and specific: own the extension lifecycle end to end — not what an extension does inside it.
                        </p>
                    </div>

                    <div class="reveal overflow-x-auto" data-reveal>
                        <div class="flex items-stretch gap-0 min-w-[560px] font-mono text-xs uppercase tracking-[0.1em]">
                            @php
                                $stages = [
                                    ['label' => 'Build', 'note' => 'A publisher team builds an extension version'],
                                    ['label' => 'Certify', 'note' => 'Lifecycle state: draft, certified, or decertified'],
                                    ['label' => 'Grant', 'note' => 'An installing team scopes what it may touch'],
                                    ['label' => 'Run', 'note' => 'The extension operates inside its granted scope'],
                                    ['label' => 'Retire', 'note' => 'Uninstall or decertify, cleanly'],
                                ];
                            @endphp
                            @foreach ($stages as $i => $s)
                                <div class="flex-1 {{ $i > 0 ? 'border-l border-[var(--line)] pl-4' : '' }} {{ $i < count($stages) - 1 ? 'pr-4' : '' }}">
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="text-[var(--violet-soft)]">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                        <span class="text-[var(--paper)] font-display normal-case text-sm font-semibold tracking-normal">{{ $s['label'] }}</span>
                                    </div>
                                    <p class="text-[var(--mist)] normal-case tracking-normal leading-relaxed">{{ $s['note'] }}</p>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-8 text-sm text-[var(--mist)] normal-case font-body max-w-md">
                            An extension's internal logic and any per-customer data it processes inside its own scope stay out of our data model — we know what it's entitled to touch, never its internals.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="relative py-28 sm:py-36 px-5 sm:px-8 overflow-hidden">
            <div class="absolute inset-0" style="background: radial-gradient(ellipse 70% 50% at 50% 100%, rgba(240,195,58,0.08) 0%, transparent 65%), var(--ink);"></div>

            <div class="relative z-10 max-w-2xl mx-auto text-center reveal" data-reveal>
                <h2 class="font-display font-semibold text-3xl sm:text-4xl text-[var(--paper)] leading-tight mb-5">
                    Extend your platform without forking it
                </h2>
                <p class="text-[var(--mist)] leading-relaxed mb-10 max-w-lg mx-auto">
                    Publish an extension, or install one from another team — all without touching your platform's core.
                </p>

                @guest
                    <div class="flex flex-wrap justify-center gap-4">
                        <a href="{{ route('register') }}" class="press px-8 py-3.5 bg-[var(--gold)] hover:bg-[var(--gold-soft)] text-[#17121f] font-display font-semibold rounded-lg transition-colors">
                            Get started
                        </a>
                        <a href="{{ route('login') }}" class="press px-8 py-3.5 text-[var(--paper)] font-medium rounded-lg border border-[var(--line)] hover:border-[var(--mist)] transition-colors">
                            Sign in
                        </a>
                    </div>
                @endguest
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-14 px-5 sm:px-8 border-t border-[var(--line)]">
            <div class="max-w-[1400px] mx-auto flex flex-col sm:flex-row items-center justify-between gap-6">
                <a href="/" class="flex items-center gap-2.5">
                    <img src="{{ asset('images/logo-light.png') }}" alt="Dot.Plug" class="h-11 w-auto opacity-90">
                </a>
                <div class="flex items-center gap-6 font-mono text-xs tracking-wide uppercase text-[var(--mist)]">
                    <a href="{{ route('policy.show') }}" class="hover:text-[var(--paper)] transition-colors">Privacy</a>
                    <a href="{{ route('cookies') }}" class="hover:text-[var(--paper)] transition-colors">Cookies</a>
                    <a href="{{ route('terms.show') }}" class="hover:text-[var(--paper)] transition-colors">Terms</a>
                </div>
                <p class="font-mono text-xs tracking-wide text-[var(--mist)]">
                    &copy; {{ date('Y') }} Dot.Plug. Developer portal &amp; extension marketplace for the Dot Ecosystem.
                </p>
            </div>
        </footer>

        <script>
            // Nav scroll state + mobile menu (vanilla JS — no Alpine dependency on this guest page)
            const header = document.getElementById('site-header');
            const onScroll = () => {
                header.classList.toggle('bg-[#17121f]/95', window.pageYOffset > 24);
                header.classList.toggle('backdrop-blur-md', window.pageYOffset > 24);
                header.classList.toggle('border-[var(--line)]', window.pageYOffset > 24);
                header.classList.toggle('border-transparent', window.pageYOffset <= 24);
            };
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();

            const menuToggle = document.getElementById('menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            const iconOpen = document.getElementById('icon-open');
            const iconClose = document.getElementById('icon-close');
            if (menuToggle) {
                menuToggle.addEventListener('click', () => {
                    const isOpen = !mobileMenu.classList.contains('hidden');
                    mobileMenu.classList.toggle('hidden', isOpen);
                    iconOpen.classList.toggle('hidden', !isOpen);
                    iconClose.classList.toggle('hidden', isOpen);
                    menuToggle.setAttribute('aria-expanded', String(!isOpen));
                });
            }

            if (window.matchMedia('(prefers-reduced-motion: no-preference)').matches && 'IntersectionObserver' in window) {
                const io = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            io.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
                document.querySelectorAll('[data-reveal]').forEach((el) => io.observe(el));
            } else {
                document.querySelectorAll('[data-reveal]').forEach((el) => el.classList.add('is-visible'));
            }
        </script>
    </body>
</html>
