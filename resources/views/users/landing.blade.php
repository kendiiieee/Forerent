<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ForeRent – Predict Your Property Success</title>
    <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    opacity: { '85': '0.85' }
                }
            }
        }
    </script>
    <style>
        /* Base resets — cannot be done in Tailwind CDN */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; font-family: 'Open Sans', sans-serif; overflow-x: hidden; }

        /* Navbar — FIXED so it stays on all scroll */
        .navbar-glass {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 999;
            backdrop-filter: blur(40px) saturate(200%);
            -webkit-backdrop-filter: blur(40px) saturate(200%);
            transition: box-shadow 0.35s ease, background 0.35s ease;
        }
        .navbar-glass.scrolled {
            background: rgba(255,255,255,0.38) !important;
            box-shadow: 0 4px 32px rgba(0,0,0,0.14);
        }

        /* Push page content below fixed nav */
        body > section:first-of-type { padding-top: 0; }
        #hero-section { margin-top: 0; }

        /* Nav link — clean underline slide animation, no rounded corners */
        .nav-link {
            position: relative;
            padding: 8px 4px;
            font-weight: 600;
            font-size: 0.92rem;
            letter-spacing: 0.4px;
            text-decoration: none;
            color: rgba(255,255,255,0.82);
            transition: color 0.22s ease;
            display: inline-block;
        }
        /* Bottom underline that slides in from left */
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0;
            width: 100%; height: 2px;
            background: #fff;
            transform: scaleX(0);
            transform-origin: left center;
            transition: transform 0.28s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .nav-link:hover { color: #fff; }
        .nav-link:hover::after { transform: scaleX(1); }

        /* Active — always show underline + bright white */
        .nav-link.active {
            color: #fff;
            font-weight: 700;
        }
        .nav-link.active::after {
            transform: scaleX(1);
            background: #fff;
            height: 2.5px;
        }

        /* Log In shimmer */
        .login-btn { position: relative; overflow: hidden; transition: all 0.25s ease; }
        .login-btn::after {
            content: '';
            position: absolute; top: -50%; left: -75%;
            width: 50%; height: 200%;
            background: linear-gradient(120deg, transparent, rgba(255,255,255,0.30), transparent);
            transform: skewX(-20deg);
            transition: left 0.5s ease;
        }
        .login-btn:hover::after { left: 130%; }
        .login-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(26,63,191,0.52); }

        /* Navbar theme transitions */
        #mainNav { transition: background 0.35s ease, box-shadow 0.35s ease; }
        #mainNav .nav-link,
        #mainNav .nav-logo-text { transition: color 0.3s ease; }
        #mainNav .nav-link::after { transition: transform 0.28s cubic-bezier(0.25, 0.46, 0.45, 0.94), background 0.3s ease; }

        /* DARK theme — white text (over dark/hero backgrounds) */
        #mainNav.nav-dark .nav-link { color: rgba(255,255,255,0.85); }
        #mainNav.nav-dark .nav-link:hover { color: #fff; }
        #mainNav.nav-dark .nav-link.active { color: #fff; }
        #mainNav.nav-dark .nav-link::after { background: #fff; }
        #mainNav.nav-dark { border-bottom-color: rgba(255,255,255,0.28); }

        /* LIGHT theme — dark navy text (over white/light backgrounds) */
        #mainNav.nav-light { background: rgba(255,255,255,0.92) !important; box-shadow: 0 2px 24px rgba(0,0,0,0.12); border-bottom-color: rgba(0,0,0,0.08); }
        #mainNav.nav-light .nav-link { color: #1a3fbf !important; }
        #mainNav.nav-light .nav-link:hover { color: #0b1f6b !important; }
        #mainNav.nav-light .nav-link.active { color: #0b1f6b !important; font-weight: 700; }
        #mainNav.nav-light .nav-link::after { background: #1a3fbf !important; }
        #mainNav.nav-light .nav-logo-text { color: #0b1f6b; }

        /* Page transition overlay */
        #page-transition {
            position: fixed; inset: 0; z-index: 9999;
            background: linear-gradient(135deg, #0b1f6b 0%, #1a3fbf 100%);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.35s ease;
        }
        #page-transition.active { opacity: 1; pointer-events: all; }

        /* Hero card glassmorphism backdrop */
        .hero-card-glass {
            backdrop-filter: blur(28px) saturate(160%);
            -webkit-backdrop-filter: blur(28px) saturate(160%);
        }

        /* Search bar select — remove native appearance */
        .search-field select,
        .search-field input {
            border: none; outline: none;
            font-family: 'Open Sans', sans-serif;
            font-size: 0.88rem; color: #374151;
            background: transparent; width: 100%;
            cursor: pointer; appearance: none; -webkit-appearance: none;
        }
        .search-field select option { color: #374151; }

        /* Neural section radial glow pseudo-element */
        .neural-section::before {
            content: '';
            position: absolute; top: -40%; right: -10%;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(96,165,250,0.08) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }

        /* Module card hover shimmer */
        .module-card::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(96,165,250,0.15), rgba(139,92,246,0.15));
            opacity: 0; transition: opacity 0.35s ease;
            pointer-events: none; border-radius: 16px;
        }
        .module-card:hover::before { opacity: 1; }
        .module-card:hover .module-dot-first { background: #60a5fa; }
        .module-card:hover .module-arrow-btn {
            background: #60a5fa; color: #0f172a; transform: translateX(4px);
        }

        /* Eco-card pseudo-elements (CSS vars & ::before/::after) */
        .eco-card::before {
            content: ''; position: absolute; inset: 0;
            background-image: var(--card-bg-image);
            background-size: cover; background-position: center;
            opacity: 0.35; filter: blur(0px);
            transition: opacity 0.5s ease, filter 0.5s ease; z-index: 1;
        }
        .eco-card.active::before { opacity: 0.7; filter: blur(5px) brightness(1.05); }
        .eco-card::after {
            content: ''; position: absolute; inset: 0;
            background: rgba(255,255,255,0.55);
            backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px);
            opacity: 1; transition: opacity 0.5s ease, backdrop-filter 0.5s ease;
            z-index: 2; pointer-events: none;
        }
        .eco-card.active::after {
            background: linear-gradient(160deg, var(--card-gradient-start) 0%, var(--card-gradient-end) 100%);
            opacity: 0.8; backdrop-filter: none; -webkit-backdrop-filter: none;
        }
        .eco-card-features li::before {
            content: '✓'; display: inline-flex; align-items: center; justify-content: center;
            width: 18px; height: 18px; min-width: 18px;
            background: rgba(255,255,255,0.25); border-radius: 50%;
            color: #fff; font-size: 0.72rem; font-weight: 700;
        }
        .eco-card.owner   { --card-bg-image: url('/images/Owner.svg');   --card-gradient-start: #1a2847; --card-gradient-end: #0f1a2e; }
        .eco-card.manager { --card-bg-image: url('/images/Manager.svg'); --card-gradient-start: #1a3fbf; --card-gradient-end: #0a2878; }
        .eco-card.tenant  { --card-bg-image: url('/images/Tenant.svg');  --card-gradient-start: #0a1c4d; --card-gradient-end: #051028; }
    </style>
</head>
<body>

<!-- Page transition overlay -->
<div id="page-transition"></div>

<!-- ═══════════════════════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════════════════════ -->
<section id="hero-section" class="relative w-full min-h-screen flex flex-col" style="padding-top: 80px;">

    <!-- Background: your uploaded SVG -->
    <img src="/images/Landing_Page_Bg.svg" alt="Landing Page Background" class="absolute inset-0 w-full h-full object-cover object-center z-0">

    <!-- Navbar -->
    <nav id="mainNav" class="navbar-glass flex items-center justify-between px-16 py-4 border-b border-white/[0.28]" style="background: rgba(255,255,255,0.22);">

        <!-- Logo -->
        <a href="#" class="flex items-center gap-3 no-underline group" style="flex-shrink:0;">
            <img src="/images/ForeRent_Logo.svg" alt="ForeRent Logo"
                 style="height: 56px; width: auto; transition: transform 0.3s ease; filter: drop-shadow(0 2px 8px rgba(26,63,191,0.18));"
                 onmouseover="this.style.transform='scale(1.06)'" onmouseout="this.style.transform='scale(1)'">
        </a>

        <!-- Nav Links -->
        <ul class="flex items-center gap-10 list-none">
            <li><a href="#" class="nav-link active">Home</a></li>
            <li><a href="#features" class="nav-link">Features</a></li>
            <li><a href="#about" class="nav-link">About</a></li>
            <li><a href="#contact" class="nav-link">Contacts</a></li>
        </ul>

        <!-- CTA -->
        <div class="flex items-center" style="flex-shrink:0;">
            <a href="/login"
               class="login-btn px-7 py-[10px] rounded-full text-white text-[0.9rem] font-bold no-underline transition-all duration-300 cursor-pointer"
               style="background: linear-gradient(135deg, #1a3fbf, #0b1f6b); box-shadow: 0 4px 16px rgba(26,63,191,0.38);">
                Log In
            </a>
        </div>
    </nav>

    <!-- Hero body -->
    <div class="relative z-[2] flex-1 flex items-center justify-end px-16 pt-16 pb-32">
        <div class="hero-card-glass w-full max-w-[490px] bg-white/[0.22] border border-white/[0.30] rounded-[20px] px-11 py-12 shadow-[0_8px_32px_rgba(0,0,0,0.18),inset_0_1px_0_rgba(255,255,255,0.35)]">
            <h1 class="text-[2.25rem] font-extrabold text-white leading-tight mb-4">
                Let's Predict Your<br>
                Property Success
            </h1>
            <p class="text-[0.97rem] text-white/85 leading-[1.65] mb-9">
                We've created the smartest way to manage your property
                by creating the smartest way to predict.
            </p>
            <div class="flex gap-3.5 flex-wrap">
                <a href="/login" class="inline-flex items-center gap-2 px-8 py-[13px] rounded-[10px] text-white text-[0.92rem] font-bold no-underline transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_8px_24px_rgba(26,63,191,0.55)] shadow-[0_4px_18px_rgba(26,63,191,0.45)] cursor-pointer" style="background: linear-gradient(135deg, #1a3fbf 0%, #0b1f6b 100%);">Get Started <span class="text-lg">→</span></a>
            </div>
        </div>
    </div>

    <!-- Floating search bar -->
    <div class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 z-20 w-[calc(100%-120px)] max-w-[1100px]">
        <div class="flex items-center bg-white rounded-[14px] shadow-[0_12px_40px_rgba(0,0,0,0.22)] pl-5 pr-2.5 py-2.5 gap-0">

            <div class="search-field flex-1 flex flex-col px-5 py-2 border-r border-gray-200 min-w-0">
                <span class="text-[0.70rem] font-bold uppercase tracking-[0.8px] text-gray-400 mb-1">City <span class="text-gray-400">∨</span></span>
                <select class="font-semibold text-gray-800">
                    <option value="" disabled selected>Choose Location</option>
                    <option>Manila</option>
                    <option>Cebu City</option>
                    <option>Davao</option>
                    <option>Quezon City</option>
                    <option>Makati</option>
                    <option>Pasig</option>
                    <option>Taguig</option>
                </select>
            </div>

            <div class="search-field flex-1 flex flex-col px-5 py-2 border-r border-gray-200 min-w-0">
                <span class="text-[0.70rem] font-bold uppercase tracking-[0.8px] text-gray-400 mb-1">Dormitory Type <span class="text-gray-400">∨</span></span>
                <select class="font-semibold text-gray-800">
                    <option value="all-female" selected>All Female</option>
                    <option>All Male</option>
                    <option>Mixed</option>
                    <option>Couple</option>
                    <option>Family</option>
                </select>
            </div>

            <div class="search-field flex-1 flex flex-col px-5 py-2 border-r border-gray-200 min-w-0">
                <span class="text-[0.70rem] font-bold uppercase tracking-[0.8px] text-gray-400 mb-1">Price <span class="text-gray-400">∨</span></span>
                <select class="font-semibold text-gray-800">
                    <option value="24000" selected>₱ 24,000</option>
                    <option>₱1,000 – ₱3,000</option>
                    <option>₱3,000 – ₱6,000</option>
                    <option>₱6,000 – ₱10,000</option>
                    <option>₱10,000 – ₱20,000</option>
                    <option>₱20,000+</option>
                </select>
            </div>

            <div class="search-field flex-1 flex flex-col px-5 py-2 min-w-0">
                <span class="text-[0.70rem] font-bold uppercase tracking-[0.8px] text-gray-400 mb-1">Unit Size <span class="text-gray-400">∨</span></span>
                <select class="font-semibold text-gray-800">
                    <option value="24000" selected>₱ 24,000</option>
                    <option>Small (≤ 20 sqm)</option>
                    <option>Medium (21–40 sqm)</option>
                    <option>Large (41–70 sqm)</option>
                    <option>Extra Large (70+ sqm)</option>
                </select>
            </div>

            <button class="flex-shrink-0 ml-3 w-14 h-14 rounded-[10px] border-none text-white flex items-center justify-center transition-all duration-200 hover:opacity-90 cursor-pointer" style="background: linear-gradient(135deg, #1a3fbf 0%, #0b1f6b 100%);">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35" stroke-linecap="round"/></svg>
            </button>
        </div>
    </div>

</section>

<!-- ═══════════════════════════════════════════════════════════════
     BELOW HERO
═══════════════════════════════════════════════════════════════ -->
<div class="pt-[90px] bg-[#f8faff]">

    <!-- Stats -->
    <div class="flex justify-center max-w-[1100px] mx-auto px-16 pt-14 pb-12">
        <div class="flex-1 text-center px-8 border-r border-[#dde3f0]">
            <div class="text-[2.4rem] font-extrabold text-[#0b1f6b] leading-none mb-1.5">12<span class="text-[#1a3fbf]">K+</span></div>
            <div class="text-[0.84rem] text-gray-500 font-medium">Properties Listed</div>
        </div>
        <div class="flex-1 text-center px-8 border-r border-[#dde3f0]">
            <div class="text-[2.4rem] font-extrabold text-[#0b1f6b] leading-none mb-1.5">8<span class="text-[#1a3fbf]">K+</span></div>
            <div class="text-[0.84rem] text-gray-500 font-medium">Happy Tenants</div>
        </div>
        <div class="flex-1 text-center px-8 border-r border-[#dde3f0]">
            <div class="text-[2.4rem] font-extrabold text-[#0b1f6b] leading-none mb-1.5">95<span class="text-[#1a3fbf]">%</span></div>
            <div class="text-[0.84rem] text-gray-500 font-medium">Prediction Accuracy</div>
        </div>
        <div class="flex-1 text-center px-8">
            <div class="text-[2.4rem] font-extrabold text-[#0b1f6b] leading-none mb-1.5">50<span class="text-[#1a3fbf]">+</span></div>
            <div class="text-[0.84rem] text-gray-500 font-medium">Cities Covered</div>
        </div>
    </div>

    <!-- Neural Architecture Section -->
    <section id="about" class="neural-section relative overflow-hidden py-20 px-16"
             style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);">

        <div class="max-w-[1100px] mx-auto relative z-[1]">

            {{-- Header --}}
            <div class="flex items-end justify-between gap-16 mb-[68px]">

                {{-- Left: eyebrow + title --}}
                <div class="flex-none">
                    <div class="inline-block text-[0.70rem] font-bold tracking-[2px] uppercase text-[#60a5fa] mb-4">
                        Neural Architecture
                    </div>
                    <h2 class="text-[2.8rem] font-extrabold text-white leading-tight max-w-[600px]">
                        Beyond Data.<br>
                        <span class="text-[#60a5fa] italic">Pure Intelligence.</span>
                    </h2>
                </div>

                {{-- Right: subtitle with left border --}}
                <div class="flex-none pl-12 border-l border-[rgba(96,165,250,0.3)] pb-2">
                    <p class="text-[0.95rem] text-white/70 leading-relaxed max-w-[420px]">
                        Our proprietary Intelligence Layer transforms raw property
                        metrics into executable strategic directives.
                    </p>
                </div>
            </div>

            {{-- Cards Grid --}}
            <div class="grid grid-cols-3 gap-8">

                {{-- Module S1 --}}
                <div class="module-card relative overflow-hidden rounded-2xl p-10 cursor-pointer
                            border border-[rgba(96,165,250,0.2)]
                            bg-[rgba(30,58,95,0.6)]
                            transition-all duration-300 ease-in-out
                            hover:bg-[rgba(96,165,250,0.15)] hover:border-[rgba(96,165,250,0.5)]
                            hover:-translate-y-1.5 hover:shadow-[0_20px_40px_rgba(96,165,250,0.2)]">
                    {{-- Icon --}}
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-6 text-[1.8rem]"
                         style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">🏗️</div>
                    <span class="inline-block text-[0.65rem] font-bold tracking-[1.8px] uppercase text-[#93c5fd] mb-3">MODULE S1</span>
                    <h3 class="text-[1.4rem] font-extrabold text-white leading-tight mb-4">Hierarchical Clustering</h3>
                    <p class="text-[0.88rem] text-white/70 leading-[1.68]">Group similar properties or maintenance requests to optimize resource allocation and enable proactive maintenance planning.</p>
                    {{-- Nav --}}
                    <div class="flex justify-between items-center mt-6 pt-6 border-t border-[rgba(96,165,250,0.15)]">
                        <div class="flex gap-1.5">
                            <span class="module-dot-first w-1.5 h-1.5 rounded-full bg-[rgba(96,165,250,0.4)] transition-colors duration-300"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-[rgba(96,165,250,0.4)]"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-[rgba(96,165,250,0.4)]"></span>
                        </div>
                        <div class="module-arrow-btn w-8 h-8 rounded-full flex items-center justify-center text-[#60a5fa]
                                    bg-[rgba(96,165,250,0.2)] border border-[rgba(96,165,250,0.3)]
                                    transition-all duration-300 cursor-pointer">→</div>
                    </div>
                </div>

                {{-- Module S2 --}}
                <div class="module-card relative overflow-hidden rounded-2xl p-10 cursor-pointer
                            border border-[rgba(96,165,250,0.2)]
                            bg-[rgba(30,58,95,0.6)]
                            transition-all duration-300 ease-in-out
                            hover:bg-[rgba(96,165,250,0.15)] hover:border-[rgba(96,165,250,0.5)]
                            hover:-translate-y-1.5 hover:shadow-[0_20px_40px_rgba(96,165,250,0.2)]">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-6 text-[1.8rem]"
                         style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">🎯</div>
                    <span class="inline-block text-[0.65rem] font-bold tracking-[1.8px] uppercase text-[#93c5fd] mb-3">MODULE S2</span>
                    <h3 class="text-[1.4rem] font-extrabold text-white leading-tight mb-4">Rental Price Prediction</h3>
                    <p class="text-[0.88rem] text-white/70 leading-[1.68]">Utilize Multiple Regression to suggest optimal rental prices based on area, bedrooms, and location attributes.</p>
                    <div class="flex justify-between items-center mt-6 pt-6 border-t border-[rgba(96,165,250,0.15)]">
                        <div class="flex gap-1.5">
                            <span class="module-dot-first w-1.5 h-1.5 rounded-full bg-[rgba(96,165,250,0.4)] transition-colors duration-300"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-[rgba(96,165,250,0.4)]"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-[rgba(96,165,250,0.4)]"></span>
                        </div>
                        <div class="module-arrow-btn w-8 h-8 rounded-full flex items-center justify-center text-[#60a5fa]
                                    bg-[rgba(96,165,250,0.2)] border border-[rgba(96,165,250,0.3)]
                                    transition-all duration-300 cursor-pointer">→</div>
                    </div>
                </div>

                {{-- Module S3 --}}
                <div class="module-card relative overflow-hidden rounded-2xl p-10 cursor-pointer
                            border border-[rgba(96,165,250,0.2)]
                            bg-[rgba(30,58,95,0.6)]
                            transition-all duration-300 ease-in-out
                            hover:bg-[rgba(96,165,250,0.15)] hover:border-[rgba(96,165,250,0.5)]
                            hover:-translate-y-1.5 hover:shadow-[0_20px_40px_rgba(96,165,250,0.2)]">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-6 text-[1.8rem]"
                         style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">📈</div>
                    <span class="inline-block text-[0.65rem] font-bold tracking-[1.8px] uppercase text-[#93c5fd] mb-3">MODULE S3</span>
                    <h3 class="text-[1.4rem] font-extrabold text-white leading-tight mb-4">Financial Forecasting</h3>
                    <p class="text-[0.88rem] text-white/70 leading-[1.68]">Institutional-grade estimates for future rental income and maintenance costs so drive data-driven decisions.</p>
                    <div class="flex justify-between items-center mt-6 pt-6 border-t border-[rgba(96,165,250,0.15)]">
                        <div class="flex gap-1.5">
                            <span class="module-dot-first w-1.5 h-1.5 rounded-full bg-[rgba(96,165,250,0.4)] transition-colors duration-300"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-[rgba(96,165,250,0.4)]"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-[rgba(96,165,250,0.4)]"></span>
                        </div>
                        <div class="module-arrow-btn w-8 h-8 rounded-full flex items-center justify-center text-[#60a5fa]
                                    bg-[rgba(96,165,250,0.2)] border border-[rgba(96,165,250,0.3)]
                                    transition-all duration-300 cursor-pointer">→</div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Unified Ecosystem Section -->
    <section class="bg-[#f8faff] py-20 px-16 relative overflow-hidden">
        <div class="max-w-[1100px] mx-auto">

            {{-- Header --}}
            <div class="text-center mb-16">
                <span class="inline-block text-[0.72rem] font-bold tracking-[2.5px] uppercase text-[#1a3fbf] mb-3">Unified Ecosystem</span>
                <h2 class="text-[2.2rem] font-extrabold text-[#0b1f6b] leading-tight mb-3">
                    Three distinct modules, one seamless<br>loop of property productivity.
                </h2>
                <p class="text-[0.95rem] text-gray-500 leading-relaxed max-w-[520px] mx-auto">
                    Tailored solutions for property owners, managers, and empowered tenants.
                </p>
            </div>

            {{-- Cards --}}
            <div id="ecosystemCards" class="flex gap-6 items-stretch h-[520px] justify-start">

                {{-- The Visionary Owner --}}
                <div class="eco-card owner active relative rounded-[20px] overflow-hidden cursor-pointer
                            flex flex-col justify-end p-8 min-h-[520px] shadow-[0_8px_24px_rgba(0,0,0,0.1)]
                            bg-gradient-to-br from-[#f5f5f7] to-[#eeeff2]
                            [flex:0_0_18%] opacity-85
                            [transition:flex_0.5s_ease-in-out,opacity_0.5s_ease-in-out]
                            [&.active]:[flex:0_0_60%] [&.active]:opacity-100"
                     data-card="owner">
                    <div class="relative z-[3] flex flex-col h-full">
                        <span class="eco-card-pill inline-block text-[0.65rem] font-bold tracking-[1.5px] uppercase
                                     px-[14px] py-[6px] rounded-full bg-white/20 text-white mb-4 w-fit
                                     opacity-0 transition-opacity duration-500 delay-100
                                     [.active_&]:opacity-100">
                            PROPERTY OWNER
                        </span>
                        <h3 class="eco-card-title text-[1.4rem] font-extrabold text-[#1a1a1a] leading-tight mb-4
                                   transition-all duration-500
                                   [.active_&]:text-white [.active_&]:text-[1.9rem]">
                            The Visionary<br>Owner
                        </h3>
                        <ul class="eco-card-features list-none flex flex-col gap-3 flex-1 mt-auto mb-4
                                   opacity-0 transition-opacity duration-500 delay-150
                                   [.active_&]:opacity-100">
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">AI-powered financial performance dashboard.</li>
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">Secure centralized document vault.</li>
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">Manager assignment and oversight controls.</li>
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">Occupancy trend visualization and tracking.</li>
                        </ul>
                        <a href="#" class="eco-card-expand text-[0.75rem] font-bold tracking-[1px] uppercase
                                           text-[#1a3fbf] no-underline transition-opacity duration-500
                                           [.active_&]:opacity-0 [.active_&]:pointer-events-none">
                            Expand Module +
                        </a>
                    </div>
                </div>

                {{-- The Strategic Manager --}}
                <div class="eco-card manager relative rounded-[20px] overflow-hidden cursor-pointer
                            flex flex-col justify-end p-8 min-h-[520px] shadow-[0_8px_24px_rgba(0,0,0,0.1)]
                            bg-gradient-to-br from-[#f5f5f7] to-[#eeeff2]
                            [flex:0_0_18%] opacity-85
                            [transition:flex_0.5s_ease-in-out,opacity_0.5s_ease-in-out]
                            [&.active]:[flex:0_0_60%] [&.active]:opacity-100"
                     data-card="manager">
                    <div class="relative z-[3] flex flex-col h-full">
                        <span class="eco-card-pill inline-block text-[0.65rem] font-bold tracking-[1.5px] uppercase
                                     px-[14px] py-[6px] rounded-full bg-white/20 text-white mb-4 w-fit
                                     opacity-0 transition-opacity duration-500 delay-100
                                     [.active_&]:opacity-100">
                            PROPERTY MANAGER
                        </span>
                        <h3 class="eco-card-title text-[1.4rem] font-extrabold text-[#1a1a1a] leading-tight mb-4
                                   transition-all duration-500
                                   [.active_&]:text-white [.active_&]:text-[1.9rem]">
                            The Strategic<br>Manager
                        </h3>
                        <ul class="eco-card-features list-none flex flex-col gap-3 flex-1 mt-auto mb-4
                                   opacity-0 transition-opacity duration-500 delay-150
                                   [.active_&]:opacity-100">
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">Full tenant lifecycle management tools.</li>
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">Integrated real-time messenger system.</li>
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">Maintenance ticket &amp; technician tracking.</li>
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">Rent collection &amp; automated reminders.</li>
                        </ul>
                        <a href="#" class="eco-card-expand text-[0.75rem] font-bold tracking-[1px] uppercase
                                           text-[#1a3fbf] no-underline transition-opacity duration-500
                                           [.active_&]:opacity-0 [.active_&]:pointer-events-none">
                            Expand Module +
                        </a>
                    </div>
                </div>

                {{-- The Empowered Tenant --}}
                <div class="eco-card tenant relative rounded-[20px] overflow-hidden cursor-pointer
                            flex flex-col justify-end p-8 min-h-[520px] shadow-[0_8px_24px_rgba(0,0,0,0.1)]
                            bg-gradient-to-br from-[#f5f5f7] to-[#eeeff2]
                            [flex:0_0_18%] opacity-85
                            [transition:flex_0.5s_ease-in-out,opacity_0.5s_ease-in-out]
                            [&.active]:[flex:0_0_60%] [&.active]:opacity-100"
                     data-card="tenant">
                    <div class="relative z-[3] flex flex-col h-full">
                        <span class="eco-card-pill inline-block text-[0.65rem] font-bold tracking-[1.5px] uppercase
                                     px-[14px] py-[6px] rounded-full bg-white/20 text-white mb-4 w-fit
                                     opacity-0 transition-opacity duration-500 delay-100
                                     [.active_&]:opacity-100">
                            TENANT
                        </span>
                        <h3 class="eco-card-title text-[1.4rem] font-extrabold text-[#1a1a1a] leading-tight mb-4
                                   transition-all duration-500
                                   [.active_&]:text-white [.active_&]:text-[1.9rem]">
                            The Empowered<br>Tenant
                        </h3>
                        <ul class="eco-card-features list-none flex flex-col gap-3 flex-1 mt-auto mb-4
                                   opacity-0 transition-opacity duration-500 delay-150
                                   [.active_&]:opacity-100">
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">One-click payment history &amp; receipt access.</li>
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">Submit and track maintenance live status.</li>
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">Instant broadcast announcements receiver.</li>
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">Direct chat with property management staff.</li>
                        </ul>
                        <a href="#" class="eco-card-expand text-[0.75rem] font-bold tracking-[1px] uppercase
                                           text-[#1a3fbf] no-underline transition-opacity duration-500
                                           [.active_&]:opacity-0 [.active_&]:pointer-events-none">
                            Expand Module +
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Features -->
    <!-- Features / Why ForeRent Section -->
    <section id="features" class="bg-[#f0f4ff] py-16 px-16">
        <div class="max-w-[1100px] mx-auto">

            {{-- Header --}}
            <div class="text-center mb-14">
                <span class="inline-block text-[0.72rem] font-bold tracking-[2.5px] uppercase text-[#1a3fbf] mb-3">Why ForeRent</span>
                <h2 class="text-[2rem] font-extrabold text-[#0b1f6b] leading-tight">
                    Everything you need to<br>
                    <span class="text-[#1a3fbf]">rent smarter</span>
                </h2>
            </div>

            {{-- Cards Grid --}}
            <div class="grid grid-cols-3 gap-7">

                {{-- AI-Powered Predictions --}}
                <div class="bg-white border border-[#e8edf7] rounded-2xl p-9 transition-all duration-200
                            hover:-translate-y-1 hover:shadow-[0_12px_36px_rgba(11,31,107,0.10)]">
                    <div class="w-13 h-13 rounded-xl flex items-center justify-center mb-5
                                bg-gradient-to-br from-[#dbeafe] to-[#eff6ff]"
                         style="width:52px;height:52px;">
                        <svg class="w-[26px] h-[26px] text-[#1a3fbf]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-[1.05rem] font-bold text-[#0b1f6b] mb-2.5">AI-Powered Predictions</h3>
                    <p class="text-[0.88rem] text-gray-500 leading-[1.65]">Our model analyzes thousands of data points to forecast rental prices and market trends with high accuracy.</p>
                </div>

                {{-- Location Intelligence --}}
                <div class="bg-white border border-[#e8edf7] rounded-2xl p-9 transition-all duration-200
                            hover:-translate-y-1 hover:shadow-[0_12px_36px_rgba(11,31,107,0.10)]">
                    <div class="flex items-center justify-center rounded-xl mb-5 bg-gradient-to-br from-[#dbeafe] to-[#eff6ff]"
                         style="width:52px;height:52px;">
                        <svg class="w-[26px] h-[26px] text-[#1a3fbf]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-[1.05rem] font-bold text-[#0b1f6b] mb-2.5">Location Intelligence</h3>
                    <p class="text-[0.88rem] text-gray-500 leading-[1.65]">Explore rentals by neighborhood with detailed insights on accessibility, amenities, and nearby developments.</p>
                </div>

                {{-- Verified Listings --}}
                <div class="bg-white border border-[#e8edf7] rounded-2xl p-9 transition-all duration-200
                            hover:-translate-y-1 hover:shadow-[0_12px_36px_rgba(11,31,107,0.10)]">
                    <div class="flex items-center justify-center rounded-xl mb-5 bg-gradient-to-br from-[#dbeafe] to-[#eff6ff]"
                         style="width:52px;height:52px;">
                        <svg class="w-[26px] h-[26px] text-[#1a3fbf]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-[1.05rem] font-bold text-[#0b1f6b] mb-2.5">Verified Listings</h3>
                    <p class="text-[0.88rem] text-gray-500 leading-[1.65]">Every property is verified to ensure accurate details, genuine photos, and legitimate landlord contacts.</p>
                </div>

                {{-- Price Fairness Score --}}
                <div class="bg-white border border-[#e8edf7] rounded-2xl p-9 transition-all duration-200
                            hover:-translate-y-1 hover:shadow-[0_12px_36px_rgba(11,31,107,0.10)]">
                    <div class="flex items-center justify-center rounded-xl mb-5 bg-gradient-to-br from-[#dbeafe] to-[#eff6ff]"
                         style="width:52px;height:52px;">
                        <svg class="w-[26px] h-[26px] text-[#1a3fbf]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-[1.05rem] font-bold text-[#0b1f6b] mb-2.5">Price Fairness Score</h3>
                    <p class="text-[0.88rem] text-gray-500 leading-[1.65]">Instantly see if a listing is fairly priced, overpriced, or a great deal compared to similar units in the area.</p>
                </div>

                {{-- Real-Time Alerts --}}
                <div class="bg-white border border-[#e8edf7] rounded-2xl p-9 transition-all duration-200
                            hover:-translate-y-1 hover:shadow-[0_12px_36px_rgba(11,31,107,0.10)]">
                    <div class="flex items-center justify-center rounded-xl mb-5 bg-gradient-to-br from-[#dbeafe] to-[#eff6ff]"
                         style="width:52px;height:52px;">
                        <svg class="w-[26px] h-[26px] text-[#1a3fbf]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <h3 class="text-[1.05rem] font-bold text-[#0b1f6b] mb-2.5">Real-Time Alerts</h3>
                    <p class="text-[0.88rem] text-gray-500 leading-[1.65]">Get notified the moment a new listing matching your criteria becomes available in your preferred area.</p>
                </div>

                {{-- Market Reports --}}
                <div class="bg-white border border-[#e8edf7] rounded-2xl p-9 transition-all duration-200
                            hover:-translate-y-1 hover:shadow-[0_12px_36px_rgba(11,31,107,0.10)]">
                    <div class="flex items-center justify-center rounded-xl mb-5 bg-gradient-to-br from-[#dbeafe] to-[#eff6ff]"
                         style="width:52px;height:52px;">
                        <svg class="w-[26px] h-[26px] text-[#1a3fbf]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-[1.05rem] font-bold text-[#0b1f6b] mb-2.5">Market Reports</h3>
                    <p class="text-[0.88rem] text-gray-500 leading-[1.65]">Access monthly reports on rental market trends, average prices, and forecasts by city and property type.</p>
                </div>

            </div>
        </div>
    </section>

</div>

<!-- ═══════════════════════════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════════════════════════ -->
<footer style="background: linear-gradient(135deg, #0a0f1e 0%, #0d1b3e 100%);" class="text-white">

    {{-- Main footer body --}}
    <div class="max-w-[1200px] mx-auto px-16 pt-16 pb-12">
        <div class="flex gap-16">

            {{-- Brand column --}}
            <div class="flex-none w-64">
                {{-- Logo --}}
                <a href="#" class="flex items-center gap-3 no-underline mb-5">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background: linear-gradient(135deg, #1a3fbf, #3b82f6);">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="text-white font-extrabold text-xl tracking-wider uppercase">ForeRent</span>
                </a>

                {{-- Tagline --}}
                <p class="text-white/50 text-[0.88rem] leading-relaxed mb-8">
                    Pioneering AI-driven property management solutions for the modern enterprise.
                </p>

                {{-- Social icon buttons --}}
                <div class="flex gap-3">
                    {{-- Settings / Owner Portal --}}
                    <a href="#" class="w-10 h-10 rounded-full border border-white/20 bg-white/[0.06] hover:bg-white/[0.14] hover:border-[#3b82f6] flex items-center justify-center transition-all duration-200">
                        <svg class="w-4 h-4 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </a>
                    {{-- Team / Tenants --}}
                    <a href="#" class="w-10 h-10 rounded-full border border-white/20 bg-white/[0.06] hover:bg-white/[0.14] hover:border-[#3b82f6] flex items-center justify-center transition-all duration-200">
                        <svg class="w-4 h-4 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </a>
                    {{-- Chat / Support --}}
                    <a href="#" class="w-10 h-10 rounded-full border border-white/20 bg-white/[0.06] hover:bg-white/[0.14] hover:border-[#3b82f6] flex items-center justify-center transition-all duration-200">
                        <svg class="w-4 h-4 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Nav columns --}}
            <div class="flex-1 grid grid-cols-3 gap-8 pt-1">

                {{-- Platform --}}
                <div>
                    <h4 class="text-[0.68rem] font-bold tracking-[2.5px] uppercase text-[#3b82f6] mb-6">Platform</h4>
                    <ul class="list-none flex flex-col gap-4">
                        <li><a href="#" class="text-[0.78rem] font-bold tracking-[1.5px] uppercase text-white/55 hover:text-white no-underline transition-colors duration-200">Owner Portal</a></li>
                        <li><a href="#" class="text-[0.78rem] font-bold tracking-[1.5px] uppercase text-white/55 hover:text-white no-underline transition-colors duration-200">Manager Suite</a></li>
                        <li><a href="#" class="text-[0.78rem] font-bold tracking-[1.5px] uppercase text-white/55 hover:text-white no-underline transition-colors duration-200">Tenant App</a></li>
                        <li><a href="#" class="text-[0.78rem] font-bold tracking-[1.5px] uppercase text-white/55 hover:text-white no-underline transition-colors duration-200">AI Insights</a></li>
                    </ul>
                </div>

                {{-- Company --}}
                <div>
                    <h4 class="text-[0.68rem] font-bold tracking-[2.5px] uppercase text-[#3b82f6] mb-6">Company</h4>
                    <ul class="list-none flex flex-col gap-4">
                        <li><a href="#" class="text-[0.78rem] font-bold tracking-[1.5px] uppercase text-white/55 hover:text-white no-underline transition-colors duration-200">Our Vision</a></li>
                        <li><a href="#" class="text-[0.78rem] font-bold tracking-[1.5px] uppercase text-white/55 hover:text-white no-underline transition-colors duration-200">Methodology</a></li>
                        <li><a href="#" class="text-[0.78rem] font-bold tracking-[1.5px] uppercase text-white/55 hover:text-white no-underline transition-colors duration-200">Support</a></li>
                        <li><a href="#" class="text-[0.78rem] font-bold tracking-[1.5px] uppercase text-white/55 hover:text-white no-underline transition-colors duration-200">Contact</a></li>
                    </ul>
                </div>

                {{-- Enterprise --}}
                <div>
                    <h4 class="text-[0.68rem] font-bold tracking-[2.5px] uppercase text-[#3b82f6] mb-6">Enterprise</h4>
                    <ul class="list-none flex flex-col gap-4">
                        <li><a href="#" class="text-[0.78rem] font-bold tracking-[1.5px] uppercase text-white/55 hover:text-white no-underline transition-colors duration-200">Data Privacy</a></li>
                        <li><a href="#" class="text-[0.78rem] font-bold tracking-[1.5px] uppercase text-white/55 hover:text-white no-underline transition-colors duration-200">Terms of Use</a></li>
                        <li><a href="#" class="text-[0.78rem] font-bold tracking-[1.5px] uppercase text-white/55 hover:text-white no-underline transition-colors duration-200">Security</a></li>
                    </ul>
                </div>

            </div>
        </div>
    </div>

    {{-- Bottom bar --}}
    <div class="border-t border-white/[0.08] max-w-[1200px] mx-auto px-16 py-5 flex items-center justify-between">
        <span class="text-[0.68rem] font-bold tracking-[2px] uppercase text-white/35">
            &copy; {{ date('Y') }} ForeRent Inc. Built for Scale.
        </span>
        <div class="flex items-center gap-8">
            <a href="#" class="text-[0.68rem] font-bold tracking-[2px] uppercase text-white/35 hover:text-white no-underline transition-colors duration-200">Instagram</a>
            <a href="#" class="text-[0.68rem] font-bold tracking-[2px] uppercase text-white/35 hover:text-white no-underline transition-colors duration-200">LinkedIn</a>
            <a href="#" class="text-[0.68rem] font-bold tracking-[2px] uppercase text-white/35 hover:text-white no-underline transition-colors duration-200">Twitter</a>
        </div>
    </div>

</footer>

@vite('resources/js/app.js')

<script>
    // ─── NAVBAR THEME: Light vs Dark based on scroll position ───────────────
    // We use explicit section color mapping — much more reliable than
    // getComputedStyle (which returns transparent for most sections).

    // Sections that have a DARK background (white text is fine)
    const DARK_SECTION_IDS = ['hero-section', 'about'];

    function updateNavTheme() {
        const nav = document.getElementById('mainNav');
        const scrollY = window.scrollY;
        const navBottom = nav.offsetHeight + scrollY;

        // Scrolled glass effect
        if (scrollY > 30) nav.classList.add('scrolled');
        else nav.classList.remove('scrolled');

        let isDark = true; // default: assume dark (hero)

        // Check every tracked section
        document.querySelectorAll('section[id], #hero-section').forEach(section => {
            const rect = section.getBoundingClientRect();
            const top = rect.top + scrollY;
            const bottom = rect.bottom + scrollY;
            // Is the navbar bottom edge inside this section?
            if (navBottom >= top && navBottom <= bottom) {
                isDark = DARK_SECTION_IDS.includes(section.id);
            }
        });

        // Also check the light wrapper div (stats + features area)
        const lightWrapper = document.querySelector('div.pt-\\[90px\\]');
        if (lightWrapper) {
            const r = lightWrapper.getBoundingClientRect();
            const top = r.top + scrollY;
            const bottom = r.bottom + scrollY;
            if (navBottom >= top && navBottom <= bottom) {
                // Check if we're NOT inside a dark sub-section
                let insideDarkSub = false;
                DARK_SECTION_IDS.forEach(id => {
                    const el = document.getElementById(id);
                    if (el && id !== 'hero-section') {
                        const er = el.getBoundingClientRect();
                        if (navBottom >= er.top + scrollY && navBottom <= er.bottom + scrollY) {
                            insideDarkSub = true;
                        }
                    }
                });
                if (!insideDarkSub) isDark = false;
            }
        }

        if (isDark) {
            nav.classList.remove('nav-light');
            nav.classList.add('nav-dark');
        } else {
            nav.classList.remove('nav-dark');
            nav.classList.add('nav-light');
        }
    }

    window.addEventListener('scroll', updateNavTheme, { passive: true });
    window.addEventListener('load', updateNavTheme);
    document.addEventListener('DOMContentLoaded', updateNavTheme);

    // ─── SMOOTH SCROLL WITH PAGE FLASH ANIMATION ────────────────────────────
    const overlay = document.getElementById('page-transition');

    function animatedScrollTo(targetEl, linkEl) {
        if (!targetEl) return;

        // Set active link
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        if (linkEl) linkEl.classList.add('active');

        // Flash overlay in
        overlay.classList.add('active');

        setTimeout(() => {
            // Scroll to target
            const yOffset = -document.getElementById('mainNav').offsetHeight;
            const y = targetEl.getBoundingClientRect().top + window.scrollY + yOffset;
            window.scrollTo({ top: y, behavior: 'instant' });

            // Fade overlay out
            setTimeout(() => {
                overlay.classList.remove('active');
            }, 80);
        }, 280);
    }

    document.addEventListener('DOMContentLoaded', function () {

        // Home link → hero section
        document.querySelectorAll('.nav-link').forEach(link => {
            const href = link.getAttribute('href');

            if (href === '#' || href === '' || link.textContent.trim() === 'Home') {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const hero = document.getElementById('hero-section');
                    animatedScrollTo(hero, this);
                });

            } else if (href === '#about' || link.textContent.trim() === 'About') {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const about = document.getElementById('about');
                    animatedScrollTo(about, this);
                });

            } else if (href && href.startsWith('#')) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) animatedScrollTo(target, this);
                });
            }
        });

        // ─── ECOSYSTEM CARD INTERACTIONS ────────────────────────────────────
        const ecoCards = document.querySelectorAll('.eco-card');
        ecoCards.forEach(card => {
            card.addEventListener('click', function (e) {
                e.preventDefault();
                ecoCards.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
            });
        });
    });
</script>

</body>
</html>
