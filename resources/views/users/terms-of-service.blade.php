<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service – ForeRent</title>
    <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Open Sans', 'sans-serif'] } } } }
    </script>
</head>
<body class="font-sans bg-[#f8faff] text-gray-700">

    {{-- Hero header --}}
    <div class="bg-[linear-gradient(135deg,#0b1f6b_0%,#1a3fbf_100%)] text-white">
        <div class="max-w-[1100px] mx-auto px-8">
            <nav class="flex items-center justify-between py-5">
                <a href="/" class="flex items-center no-underline">
                    <img src="/images/white_logo.svg" alt="ForeRent Logo" class="h-11 w-auto">
                </a>
                <a href="/" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white/10 border border-white/20
                           text-white text-[0.85rem] font-semibold no-underline
                           hover:bg-white/20 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Home
                </a>
            </nav>
            <div class="pt-12 pb-20">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/15 text-[0.78rem] font-semibold tracking-wide mb-6">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Legal Document
                </span>
                <h1 class="text-[2.8rem] font-extrabold leading-tight mb-4">Terms of Service</h1>
                <p class="text-white/60 text-[0.95rem] max-w-[500px]">The rules and guidelines that govern your use of the ForeRent platform.</p>
                <p class="text-white/40 text-[0.82rem] mt-6">Last updated: {{ date('F d, Y') }}</p>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="max-w-[1100px] mx-auto px-8 -mt-8 pb-24">
        <div class="grid grid-cols-2 gap-6">

            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Acceptance of Terms</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]">By accessing or using the ForeRent platform, you agree to be bound by these Terms of Service. If you do not agree with any part of these terms, you may not use the platform. These terms apply to all users, including property owners, managers, and tenants.</p>
            </div>

            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Account Registration</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]">To use ForeRent, you must create an account with accurate and complete information. You are responsible for maintaining the confidentiality of your login credentials and for all activities that occur under your account. You must notify us immediately of any unauthorized use.</p>
            </div>

            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">User Roles and Responsibilities</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]"><strong>Property Owners</strong> are responsible for providing accurate property and unit information. <strong>Managers</strong> must handle tenant data responsibly and process maintenance requests in a timely manner. <strong>Tenants</strong> are expected to use the platform for legitimate purposes related to their tenancy.</p>
            </div>

            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">AI Predictions Disclaimer</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]">ForeRent's AI-powered rental price predictions and financial forecasts are provided for informational purposes only. They are generated using statistical models and historical data, and should not be considered as financial advice. ForeRent is not liable for any decisions made based on these predictions.</p>
            </div>

            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Prohibited Conduct</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]">Users may not use ForeRent to post false or misleading property listings, harass other users, attempt to gain unauthorized access to other accounts, or use the platform for any illegal purpose. Violation of these terms may result in immediate account suspension or termination.</p>
            </div>

            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Intellectual Property</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]">All content, features, and functionality of the ForeRent platform — including its AI models, design, logos, and software — are the property of ForeRent and are protected by intellectual property laws. You may not reproduce, distribute, or create derivative works without our written permission.</p>
            </div>

        </div>

        {{-- Full-width cards --}}
        <div class="grid grid-cols-2 gap-6 mt-6">
            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Limitation of Liability</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]">ForeRent is provided on an "as is" basis. We do not guarantee uninterrupted or error-free service. To the fullest extent permitted by law, ForeRent shall not be liable for any indirect, incidental, or consequential damages arising from the use of the platform.</p>
            </div>

            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Termination</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]">We reserve the right to suspend or terminate your account at our discretion if you violate these terms. Upon termination, your right to use the platform ceases immediately. You may also delete your account at any time through your account settings.</p>
            </div>
        </div>

        {{-- Note --}}
        <div class="mt-6 bg-[#eef2ff] border border-[#d6dff7] rounded-2xl p-8 flex items-start gap-4">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#1a3fbf] text-white shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <div>
                <h3 class="text-[1rem] font-bold text-[#0b1f6b] mb-1">Changes to Terms</h3>
                <p class="text-[0.88rem] text-[#1a3fbf]/70 leading-[1.75]">ForeRent reserves the right to modify these terms at any time. Updated terms will be posted on this page. Continued use of the platform after changes constitutes acceptance of the revised terms.</p>
            </div>
        </div>
    </div>

</body>
</html>
