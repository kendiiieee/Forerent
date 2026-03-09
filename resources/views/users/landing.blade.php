<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ForeRent – Predict Your Property Success</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            font-family: 'Open Sans', sans-serif;
            overflow-x: hidden;
        }

        /* ─── HERO SECTION ─────────────────────────────────────────────── */
        .hero {
            position: relative;
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* SVG background */
        .hero-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            z-index: 0;
        }

        /* Dark overlay for readability */
        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                135deg,
                rgba(3, 18, 72, 0.55) 0%,
                rgba(11, 31, 107, 0.40) 50%,
                rgba(3, 18, 72, 0.60) 100%
            );
            z-index: 1;
        }

        /* ─── NAVBAR ────────────────────────────────────────────────────── */
        .navbar {
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 60px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(22px) saturate(180%);
            -webkit-backdrop-filter: blur(22px) saturate(180%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.18);
        }

        .navbar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .navbar-logo svg {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
        }

        .navbar-logo-text {
            font-size: 1.5rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 0.5px;
        }

        .navbar-logo-text span {
            color: #60a5fa;
        }

        .navbar-links {
            display: flex;
            align-items: center;
            gap: 36px;
            list-style: none;
        }

        .navbar-links a {
            color: rgba(255, 255, 255, 0.90);
            text-decoration: none;
            font-size: 0.92rem;
            font-weight: 500;
            letter-spacing: 0.3px;
            transition: color 0.2s;
        }

        .navbar-links a:hover {
            color: #ffffff;
        }

        .navbar-links a.active {
            color: #ffffff;
            font-weight: 700;
            border-bottom: 2px solid #60a5fa;
            padding-bottom: 2px;
        }

        .navbar-cta {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-login {
            padding: 9px 26px;
            border-radius: 8px;
            border: 1.5px solid rgba(255, 255, 255, 0.70);
            background: transparent;
            color: #ffffff;
            font-family: 'Open Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s;
            text-decoration: none;
        }

        .btn-login:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: #ffffff;
        }

        .btn-register {
            padding: 9px 26px;
            border-radius: 8px;
            border: none;
            background: linear-gradient(135deg, #1a3fbf, #0b1f6b);
            color: #ffffff;
            font-family: 'Open Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.15s;
            text-decoration: none;
        }

        .btn-register:hover {
            opacity: 0.88;
            transform: translateY(-1px);
        }

        /* ─── HERO BODY ─────────────────────────────────────────────────── */
        .hero-body {
            position: relative;
            z-index: 2;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 60px 60px 120px;
        }

        /* Glassmorphism hero card */
        .hero-card {
            width: 100%;
            max-width: 490px;
            background: rgba(255, 255, 255, 0.13);
            backdrop-filter: blur(28px) saturate(160%);
            -webkit-backdrop-filter: blur(28px) saturate(160%);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 20px;
            padding: 48px 44px 44px;
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.28),
                inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }

        .hero-eyebrow {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #93c5fd;
            margin-bottom: 14px;
        }

        .hero-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.25;
            margin-bottom: 16px;
        }

        .hero-title span {
            color: #60a5fa;
        }

        .hero-subtitle {
            font-size: 0.97rem;
            color: rgba(255, 255, 255, 0.80);
            line-height: 1.65;
            margin-bottom: 36px;
        }

        .hero-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .btn-primary {
            padding: 13px 32px;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, #1a3fbf 0%, #0b1f6b 100%);
            color: #ffffff;
            font-family: 'Open Sans', sans-serif;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.18s, box-shadow 0.18s;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 18px rgba(26, 63, 191, 0.45);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(26, 63, 191, 0.55);
        }

        .btn-secondary {
            padding: 13px 32px;
            border-radius: 10px;
            border: 1.5px solid rgba(255, 255, 255, 0.60);
            background: transparent;
            color: #ffffff;
            font-family: 'Open Sans', sans-serif;
            font-size: 0.92rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.18s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        /* ─── SEARCH BAR ────────────────────────────────────────────────── */
        .search-wrapper {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translate(-50%, 50%);
            z-index: 20;
            width: calc(100% - 120px);
            max-width: 1100px;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.22);
            padding: 10px 10px 10px 20px;
            gap: 0;
        }

        .search-field {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 8px 20px;
            border-right: 1px solid #e5e7eb;
            min-width: 0;
        }

        .search-field:last-of-type {
            border-right: none;
        }

        .search-label {
            font-size: 0.70rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #0b1f6b;
            margin-bottom: 4px;
        }

        .search-field select,
        .search-field input {
            border: none;
            outline: none;
            font-family: 'Open Sans', sans-serif;
            font-size: 0.88rem;
            color: #374151;
            background: transparent;
            width: 100%;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
        }

        .search-field select option {
            color: #374151;
        }

        .search-btn {
            flex-shrink: 0;
            padding: 14px 30px;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, #1a3fbf 0%, #0b1f6b 100%);
            color: #ffffff;
            font-family: 'Open Sans', sans-serif;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.2s, transform 0.15s;
            white-space: nowrap;
        }

        .search-btn:hover {
            opacity: 0.90;
            transform: translateY(-1px);
        }

        .search-btn svg {
            width: 16px;
            height: 16px;
        }

        /* ─── SECTION BELOW HERO ────────────────────────────────────────── */
        .below-hero {
            padding-top: 90px;
            background: #f8faff;
        }

        /* ─── STATS STRIP ───────────────────────────────────────────────── */
        .stats-strip {
            display: flex;
            justify-content: center;
            gap: 0;
            padding: 56px 60px 48px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .stat-item {
            flex: 1;
            text-align: center;
            padding: 0 32px;
            border-right: 1px solid #dde3f0;
        }

        .stat-item:last-child {
            border-right: none;
        }

        .stat-number {
            font-size: 2.4rem;
            font-weight: 800;
            color: #0b1f6b;
            line-height: 1;
            margin-bottom: 6px;
        }

        .stat-number span {
            color: #1a3fbf;
        }

        .stat-label {
            font-size: 0.84rem;
            color: #6b7280;
            font-weight: 500;
        }

        /* ─── FEATURES ──────────────────────────────────────────────────── */
        .features-section {
            padding: 60px 60px 80px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 52px;
        }

        .section-eyebrow {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #1a3fbf;
            margin-bottom: 12px;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 800;
            color: #0b1f6b;
            line-height: 1.2;
        }

        .section-title span {
            color: #1a3fbf;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }

        .feature-card {
            background: #ffffff;
            border: 1px solid #e8edf7;
            border-radius: 16px;
            padding: 36px 30px;
            transition: transform 0.22s, box-shadow 0.22s;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 36px rgba(11, 31, 107, 0.10);
        }

        .feature-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            background: linear-gradient(135deg, #dbeafe, #eff6ff);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .feature-icon svg {
            width: 26px;
            height: 26px;
            color: #1a3fbf;
        }

        .feature-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0b1f6b;
            margin-bottom: 10px;
        }

        .feature-desc {
            font-size: 0.88rem;
            color: #6b7280;
            line-height: 1.65;
        }

        /* ─── FOOTER ────────────────────────────────────────────────────── */
        .footer {
            background: #0b1f6b;
            color: rgba(255,255,255,0.65);
            text-align: center;
            padding: 28px 60px;
            font-size: 0.82rem;
        }

        .footer a {
            color: #93c5fd;
            text-decoration: none;
        }

        /* ─── RESPONSIVE ────────────────────────────────────────────────── */
        @media (max-width: 1024px) {
            .navbar { padding: 16px 32px; }
            .hero-body { padding: 40px 32px 120px; }
            .search-wrapper { width: calc(100% - 64px); }
            .features-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .navbar-links { display: none; }
            .hero-body { justify-content: center; padding: 40px 20px 130px; }
            .hero-card { max-width: 100%; }
            .hero-title { font-size: 1.75rem; }
            .search-bar { flex-wrap: wrap; gap: 8px; padding: 16px; }
            .search-field { border-right: none; border-bottom: 1px solid #e5e7eb; padding: 8px 0; }
            .search-field:last-of-type { border-bottom: none; }
            .search-btn { width: 100%; justify-content: center; }
            .stats-strip { flex-direction: column; gap: 28px; padding: 40px 20px; }
            .stat-item { border-right: none; border-bottom: 1px solid #dde3f0; padding-bottom: 24px; }
            .stat-item:last-child { border-bottom: none; }
            .features-section { padding: 40px 20px 60px; }
            .features-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════════════════════ -->
<section class="hero">

    <!-- Background: your uploaded SVG -->
    <img
        class="hero-bg"
        src="{{ asset('images/Group_5999.svg') }}"
        alt="City skyline background"
    >

    <!-- Overlay -->
    <div class="hero-overlay"></div>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="#" class="navbar-logo">
            <!-- Building icon -->
            <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="36" height="36" rx="8" fill="rgba(255,255,255,0.15)"/>
                <path d="M8 28V12l8-4v20H8z" fill="#60a5fa"/>
                <path d="M16 28V8l12 3v17H16z" fill="white"/>
                <rect x="18" y="12" width="3" height="3" rx="0.5" fill="#1a3fbf"/>
                <rect x="23" y="12" width="3" height="3" rx="0.5" fill="#1a3fbf"/>
                <rect x="18" y="18" width="3" height="3" rx="0.5" fill="#1a3fbf"/>
                <rect x="23" y="18" width="3" height="3" rx="0.5" fill="#1a3fbf"/>
                <rect x="10" y="16" width="2.5" height="2.5" rx="0.5" fill="white" fill-opacity="0.8"/>
                <rect x="10" y="21" width="2.5" height="2.5" rx="0.5" fill="white" fill-opacity="0.8"/>
                <rect x="20" y="23" width="4" height="5" rx="0.5" fill="#1a3fbf"/>
            </svg>
            <span class="navbar-logo-text">Fore<span>Rent</span></span>
        </a>

        <ul class="navbar-links">
            <li><a href="#" class="active">Home</a></li>
            <li><a href="#">Listings</a></li>
            <li><a href="#">Predictions</a></li>
            <li><a href="#">About</a></li>
            <li><a href="#">Contact</a></li>
        </ul>

        <div class="navbar-cta">
            <a href="/login" class="btn-login">Log In</a>
            <a href="/login" class="btn-register">Get Started</a>
        </div>
    </nav>

    <!-- Hero body -->
    <div class="hero-body">
        <div class="hero-card">
            <span class="hero-eyebrow">Smart Property Intelligence</span>
            <h1 class="hero-title">
                Let's Predict Your<br>
                <span>Property Success</span>
            </h1>
            <p class="hero-subtitle">
                ForeRent uses advanced forecasting to help landlords and tenants
                find the right rental at the right price — powered by real market data.
            </p>
            <div class="hero-actions">
                <a href="/login" class="btn-primary">Get Started</a>
                <a href="#features" class="btn-secondary">Learn More</a>
            </div>
        </div>
    </div>

    <!-- Floating search bar -->
    <div class="search-wrapper">
        <div class="search-bar">

            <div class="search-field">
                <span class="search-label">City</span>
                <select>
                    <option value="" disabled selected>Select city</option>
                    <option>Manila</option>
                    <option>Cebu City</option>
                    <option>Davao</option>
                    <option>Quezon City</option>
                    <option>Makati</option>
                    <option>Pasig</option>
                    <option>Taguig</option>
                </select>
            </div>

            <div class="search-field">
                <span class="search-label">Property Type</span>
                <select>
                    <option value="" disabled selected>Select type</option>
                    <option>Dormitory</option>
                    <option>Apartment</option>
                    <option>Condo Unit</option>
                    <option>Boarding House</option>
                    <option>Studio</option>
                </select>
            </div>

            <div class="search-field">
                <span class="search-label">Price Range</span>
                <select>
                    <option value="" disabled selected>Select range</option>
                    <option>₱1,000 – ₱3,000</option>
                    <option>₱3,000 – ₱6,000</option>
                    <option>₱6,000 – ₱10,000</option>
                    <option>₱10,000 – ₱20,000</option>
                    <option>₱20,000+</option>
                </select>
            </div>

            <div class="search-field">
                <span class="search-label">Unit Size</span>
                <select>
                    <option value="" disabled selected>Select size</option>
                    <option>Small (≤ 20 sqm)</option>
                    <option>Medium (21–40 sqm)</option>
                    <option>Large (41–70 sqm)</option>
                    <option>Extra Large (70+ sqm)</option>
                </select>
            </div>

            <button class="search-btn">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m21 21-4.35-4.35" stroke-linecap="round"/>
                </svg>
                Search
            </button>
        </div>
    </div>

</section>

<!-- ═══════════════════════════════════════════════════════════════
     BELOW HERO
═══════════════════════════════════════════════════════════════ -->
<div class="below-hero">

    <!-- Stats -->
    <div class="stats-strip">
        <div class="stat-item">
            <div class="stat-number">12<span>K+</span></div>
            <div class="stat-label">Properties Listed</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">8<span>K+</span></div>
            <div class="stat-label">Happy Tenants</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">95<span>%</span></div>
            <div class="stat-label">Prediction Accuracy</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">50<span>+</span></div>
            <div class="stat-label">Cities Covered</div>
        </div>
    </div>

    <!-- Features -->
    <section id="features" class="features-section">
        <div class="section-header">
            <span class="section-eyebrow">Why ForeRent</span>
            <h2 class="section-title">Everything you need to<br><span>rent smarter</span></h2>
        </div>

        <div class="features-grid">

            <div class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="feature-title">AI-Powered Predictions</h3>
                <p class="feature-desc">Our model analyzes thousands of data points to forecast rental prices and market trends with high accuracy.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="feature-title">Location Intelligence</h3>
                <p class="feature-desc">Explore rentals by neighborhood with detailed insights on accessibility, amenities, and nearby developments.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="feature-title">Verified Listings</h3>
                <p class="feature-desc">Every property is verified to ensure accurate details, genuine photos, and legitimate landlord contacts.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="feature-title">Price Fairness Score</h3>
                <p class="feature-desc">Instantly see if a listing is fairly priced, overpriced, or a great deal compared to similar units in the area.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <h3 class="feature-title">Real-Time Alerts</h3>
                <p class="feature-desc">Get notified the moment a new listing matching your criteria becomes available in your preferred area.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="feature-title">Market Reports</h3>
                <p class="feature-desc">Access monthly reports on rental market trends, average prices, and forecasts by city and property type.</p>
            </div>

        </div>
    </section>

</div>

<!-- ═══════════════════════════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════════════════════════ -->
<footer class="footer">
    <p>&copy; {{ date('Y') }} ForeRent. All rights reserved. &nbsp;·&nbsp;
        <a href="#">Privacy Policy</a> &nbsp;·&nbsp;
        <a href="#">Terms of Service</a>
    </p>
</footer>

</body>
</html>
