@extends('layouts.guest')

@section('content')
<div class="w-full">
    <!-- Hero Section -->
    <section class="hero-section relative min-h-screen w-full overflow-hidden bg-gradient-to-br from-slate-50 via-white to-blue-50">

        <!-- Background Building Image with Scale Animation -->
        <div class="hero-bg-image absolute inset-0 w-full h-full">
            <img
                src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 600'%3E%3Crect fill='%23f0f9ff' width='1200' height='600'/%3E%3Cg opacity='0.1'%3E%3Crect x='400' y='100' width='80' height='400' fill='%23001C64'/%3E%3Crect x='500' y='150' width='80' height='350' fill='%23103FD3'/%3E%3Crect x='600' y='120' width='80' height='380' fill='%23001C64'/%3E%3Crect x='700' y='140' width='80' height='360' fill='%23103FD3'/%3E%3C/g%3E%3C/svg%3E"
                alt="Building"
                class="w-full h-full object-cover"
            />
        </div>

        <!-- Navbar Container -->
        <nav class="hero-nav relative z-20 bg-white/80 backdrop-blur-md border-b border-gray-200/50">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <!-- Logo -->
                <div class="hero-logo font-bold text-2xl text-blue-900 opacity-0">
                    ForeRent
                </div>

                <!-- Center Links -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="#" class="hero-nav-link text-gray-700 hover:text-blue-900 font-medium opacity-0">Home</a>
                    <a href="#" class="hero-nav-link text-gray-700 hover:text-blue-900 font-medium opacity-0">Features</a>
                    <a href="#" class="hero-nav-link text-gray-700 hover:text-blue-900 font-medium opacity-0">About</a>
                    <a href="#" class="hero-nav-link text-gray-700 hover:text-blue-900 font-medium opacity-0">Contact</a>
                </div>

                <!-- Log In Button -->
                <button class="hero-login-btn px-6 py-2 bg-blue-900 text-white rounded-lg font-medium hover:bg-blue-800 transition opacity-0">
                    Log In
                </button>
            </div>
        </nav>

        <!-- Hero Content -->
        <div class="relative z-10 h-[calc(100vh-80px)] flex flex-col items-center justify-center px-6">
            <!-- Prediction Card -->
            <div class="hero-prediction-card opacity-0 max-w-2xl">
                <div class="bg-white/95 backdrop-blur-xl rounded-2xl p-8 md:p-12 shadow-2xl border border-white/50">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4 text-center">
                        Let's Predict Your <span class="text-blue-600">Property Success</span>
                    </h1>
                    <p class="text-lg text-gray-600 text-center mb-8">
                        Harness AI-powered analytics to forecast rental demand, optimize property management, and maximize returns.
                    </p>
                </div>
            </div>

            <!-- Search Bar - Bottom Overlap -->
            <div class="hero-search-bar absolute bottom-0 translate-y-1/2 w-full max-w-4xl px-6 opacity-0">
                <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="search-field-item opacity-0">
                            <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                            <input type="text" placeholder="Enter city" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div class="search-field-item opacity-0">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dormitory Type</label>
                            <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option>Select type</option>
                                <option>Studio</option>
                                <option>1 Bedroom</option>
                                <option>2 Bedrooms</option>
                            </select>
                        </div>
                        <div class="search-field-item opacity-0">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Budget</label>
                            <input type="number" placeholder="Min price" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div class="search-field-item opacity-0 flex items-end">
                            <button class="w-full bg-blue-900 text-white py-3 rounded-lg font-medium hover:bg-blue-800 transition">
                                Search
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Row -->
    <section class="stats-section py-24 bg-white relative z-5">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="stat-item opacity-0 text-center">
                    <div class="text-5xl font-bold text-blue-900 mb-2">12K+</div>
                    <p class="text-gray-600 text-lg">Properties Analyzed</p>
                </div>
                <div class="stat-item opacity-0 text-center">
                    <div class="text-5xl font-bold text-blue-900 mb-2">8K+</div>
                    <p class="text-gray-600 text-lg">Happy Owners</p>
                </div>
                <div class="stat-item opacity-0 text-center">
                    <div class="text-5xl font-bold text-blue-900 mb-2">98%</div>
                    <p class="text-gray-600 text-lg">Prediction Accuracy</p>
                </div>
                <div class="stat-item opacity-0 text-center">
                    <div class="text-5xl font-bold text-blue-900 mb-2">24h</div>
                    <p class="text-gray-600 text-lg">Average Response</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Dark Section: Beyond Data -->
    <section class="dark-section py-24 bg-gradient-to-br from-slate-900 via-blue-900 to-slate-800 text-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 mb-20 items-center">
                <!-- Left: Text -->
                <div class="dark-text-left opacity-0">
                    <h2 class="text-5xl font-bold mb-6">
                        Beyond Data.<br />Pure Intelligence.
                    </h2>
                    <p class="text-lg text-white/80 leading-relaxed">
                        Our proprietary AI engine synthesizes market trends, demographic shifts, and rental patterns to deliver predictive insights that traditional analysis simply cannot provide.
                    </p>
                </div>

                <!-- Right: Text -->
                <div class="dark-text-right opacity-0">
                    <p class="text-lg text-white/80 leading-relaxed mb-6">
                        From automated forecasting to real-time demand metrics, ForeRent transforms raw data into actionable intelligence.
                    </p>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <span class="text-blue-400 text-xl">✓</span>
                            <span>ML-powered predictive analytics</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-blue-400 text-xl">✓</span>
                            <span>Real-time market insights</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-blue-400 text-xl">✓</span>
                            <span>Comprehensive property analysis</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Feature Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="dark-feature-card opacity-0 bg-white/10 backdrop-blur-xl rounded-xl p-8 border border-white/20 hover:border-blue-400/50 transition">
                    <div class="text-4xl mb-4">🔮</div>
                    <h3 class="text-2xl font-bold mb-3">Clustering</h3>
                    <p class="text-white/70">Group similar properties to identify emerging markets and optimization opportunities.</p>
                </div>
                <div class="dark-feature-card opacity-0 bg-white/10 backdrop-blur-xl rounded-xl p-8 border border-white/20 hover:border-blue-400/50 transition">
                    <div class="text-4xl mb-4">📊</div>
                    <h3 class="text-2xl font-bold mb-3">Prediction</h3>
                    <p class="text-white/70">Forecast future rental rates, occupancy, and demand with unprecedented accuracy.</p>
                </div>
                <div class="dark-feature-card opacity-0 bg-white/10 backdrop-blur-xl rounded-xl p-8 border border-white/20 hover:border-blue-400/50 transition">
                    <div class="text-4xl mb-4">📈</div>
                    <h3 class="text-2xl font-bold mb-3">Forecasting</h3>
                    <p class="text-white/70">Plan ahead with multi-quarter revenue forecasts tailored to your portfolio.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Light Section: Unified Ecosystem -->
    <section class="light-section py-24 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <!-- Header -->
            <div class="text-center mb-16 ecosystem-header opacity-0">
                <h2 class="text-5xl font-bold text-gray-900 mb-4">
                    Built for Every Role
                </h2>
                <p class="text-xl text-gray-600">
                    Unified ecosystem tailored for owners, managers, and tenants
                </p>
            </div>

            <!-- Vertical Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Visionary Owner -->
                <div class="ecosystem-card opacity-0">
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-8 border border-blue-200">
                        <div class="text-4xl mb-4">👑</div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">The Visionary Owner</h3>
                        <p class="text-gray-600 mb-6">Master your portfolio with intelligent insights and data-driven decisions.</p>

                        <!-- Checklist -->
                        <ul class="space-y-3 ecosystem-checklist">
                            <li class="ecosystem-checklist-item opacity-0 flex items-start gap-3">
                                <span class="text-blue-600 text-xl">✓</span>
                                <span class="text-gray-700">Portfolio performance tracking</span>
                            </li>
                            <li class="ecosystem-checklist-item opacity-0 flex items-start gap-3">
                                <span class="text-blue-600 text-xl">✓</span>
                                <span class="text-gray-700">Demand forecasting tools</span>
                            </li>
                            <li class="ecosystem-checklist-item opacity-0 flex items-start gap-3">
                                <span class="text-blue-600 text-xl">✓</span>
                                <span class="text-gray-700">Revenue optimization</span>
                            </li>
                            <li class="ecosystem-checklist-item opacity-0 flex items-start gap-3">
                                <span class="text-blue-600 text-xl">✓</span>
                                <span class="text-gray-700">Market comparison analytics</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Strategic Manager -->
                <div class="ecosystem-card opacity-0">
                    <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl p-8 border border-emerald-200">
                        <div class="text-4xl mb-4">🎯</div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">The Strategic Manager</h3>
                        <p class="text-gray-600 mb-6">Streamline operations and maximize efficiency across your properties.</p>

                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <span class="text-emerald-600 text-xl">✓</span>
                                <span class="text-gray-700">Tenant matching system</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-emerald-600 text-xl">✓</span>
                                <span class="text-gray-700">Maintenance scheduling</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-emerald-600 text-xl">✓</span>
                                <span class="text-gray-700">Communication hub</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-emerald-600 text-xl">✓</span>
                                <span class="text-gray-700">Automated reporting</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Empowered Tenant -->
                <div class="ecosystem-card opacity-0">
                    <div class="bg-gradient-to-br from-orange-50 to-rose-50 rounded-2xl p-8 border border-orange-200">
                        <div class="text-4xl mb-4">🏠</div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">The Empowered Tenant</h3>
                        <p class="text-gray-600 mb-6">Find your perfect space with confidence and transparency.</p>

                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <span class="text-orange-600 text-xl">✓</span>
                                <span class="text-gray-700">Smart property matching</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-orange-600 text-xl">✓</span>
                                <span class="text-gray-700">Lease transparency</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-orange-600 text-xl">✓</span>
                                <span class="text-gray-700">Easy rent payments</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-orange-600 text-xl">✓</span>
                                <span class="text-gray-700">Support access</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Grid Section: Why ForeRent -->
    <section class="grid-section py-24 bg-gradient-to-br from-slate-50 to-blue-50">
        <div class="max-w-7xl mx-auto px-6">
            <!-- Header -->
            <div class="text-center mb-16 grid-header opacity-0">
                <h2 class="text-5xl font-bold text-gray-900 mb-4">
                    Why ForeRent?
                </h2>
                <p class="text-xl text-gray-600">
                    The platform built on innovation and intelligence
                </p>
            </div>

            <!-- 6-Card Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="grid-card opacity-0 bg-white rounded-xl shadow-lg hover:shadow-2xl transition p-8 border border-gray-100">
                    <div class="text-5xl mb-4">🤖</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">AI-Powered Predictions</h3>
                    <p class="text-gray-600">Advanced machine learning algorithms provide market forecasts with exceptional accuracy.</p>
                </div>

                <!-- Card 2 -->
                <div class="grid-card opacity-0 bg-white rounded-xl shadow-lg hover:shadow-2xl transition p-8 border border-gray-100">
                    <div class="text-5xl mb-4">⚡</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Real-Time Insights</h3>
                    <p class="text-gray-600">Live dashboards and alerts keep you informed of market changes as they happen.</p>
                </div>

                <!-- Card 3 -->
                <div class="grid-card opacity-0 bg-white rounded-xl shadow-lg hover:shadow-2xl transition p-8 border border-gray-100">
                    <div class="text-5xl mb-4">🔒</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Enterprise Security</h3>
                    <p class="text-gray-600">Bank-level encryption and compliance meet modern infrastructure standards.</p>
                </div>

                <!-- Card 4 -->
                <div class="grid-card opacity-0 bg-white rounded-xl shadow-lg hover:shadow-2xl transition p-8 border border-gray-100">
                    <div class="text-5xl mb-4">🌍</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Global Coverage</h3>
                    <p class="text-gray-600">Analyze properties across multiple markets with unified intelligence.</p>
                </div>

                <!-- Card 5 -->
                <div class="grid-card opacity-0 bg-white rounded-xl shadow-lg hover:shadow-2xl transition p-8 border border-gray-100">
                    <div class="text-5xl mb-4">💡</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Smart Automation</h3>
                    <p class="text-gray-600">Automated workflows handle routine tasks, freeing you for strategic decisions.</p>
                </div>

                <!-- Card 6 -->
                <div class="grid-card opacity-0 bg-white rounded-xl shadow-lg hover:shadow-2xl transition p-8 border border-gray-100">
                    <div class="text-5xl mb-4">📞</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">24/7 Support</h3>
                    <p class="text-gray-600">Expert support team ready to help you maximize platform value anytime.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Dark Footer -->
    <footer class="footer-section bg-slate-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 footer-columns opacity-0">
                <!-- Platform -->
                <div class="footer-column opacity-0">
                    <h4 class="text-lg font-bold mb-4">Platform</h4>
                    <ul class="space-y-2 text-white/70">
                        <li><a href="#" class="hover:text-white transition">Features</a></li>
                        <li><a href="#" class="hover:text-white transition">Pricing</a></li>
                        <li><a href="#" class="hover:text-white transition">Security</a></li>
                        <li><a href="#" class="hover:text-white transition">Documentation</a></li>
                    </ul>
                </div>

                <!-- Company -->
                <div class="footer-column opacity-0">
                    <h4 class="text-lg font-bold mb-4">Company</h4>
                    <ul class="space-y-2 text-white/70">
                        <li><a href="#" class="hover:text-white transition">About</a></li>
                        <li><a href="#" class="hover:text-white transition">Blog</a></li>
                        <li><a href="#" class="hover:text-white transition">Careers</a></li>
                        <li><a href="#" class="hover:text-white transition">Press</a></li>
                    </ul>
                </div>

                <!-- Enterprise -->
                <div class="footer-column opacity-0">
                    <h4 class="text-lg font-bold mb-4">Enterprise</h4>
                    <ul class="space-y-2 text-white/70">
                        <li><a href="#" class="hover:text-white transition">Solutions</a></li>
                        <li><a href="#" class="hover:text-white transition">Contact Sales</a></li>
                        <li><a href="#" class="hover:text-white transition">Partners</a></li>
                        <li><a href="#" class="hover:text-white transition">Custom Plans</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div class="footer-column opacity-0">
                    <h4 class="text-lg font-bold mb-4">Legal</h4>
                    <ul class="space-y-2 text-white/70">
                        <li><a href="#" class="hover:text-white transition">Privacy</a></li>
                        <li><a href="#" class="hover:text-white transition">Terms</a></li>
                        <li><a href="#" class="hover:text-white transition">Cookies</a></li>
                        <li><a href="#" class="hover:text-white transition">Compliance</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-white/20 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center text-white/60 text-sm">
                <p>&copy; 2026 ForeRent. All rights reserved.</p>
                <div class="flex gap-6 mt-4 md:mt-0">
                    <a href="#" class="hover:text-white transition">Twitter</a>
                    <a href="#" class="hover:text-white transition">LinkedIn</a>
                    <a href="#" class="hover:text-white transition">GitHub</a>
                </div>
            </div>
        </div>
    </footer>
</div>

@vite('resources/js/app.js')
@endsection
