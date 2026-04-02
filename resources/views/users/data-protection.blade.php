<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Protection – ForeRent</title>
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Legal Document
                </span>
                <h1 class="text-[2.8rem] font-extrabold leading-tight mb-4">Data Protection</h1>
                <p class="text-white/60 text-[0.95rem] max-w-[500px]">The security measures and practices we implement to keep your data safe.</p>
                <p class="text-white/40 text-[0.82rem] mt-6">Last updated: {{ date('F d, Y') }}</p>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="max-w-[1100px] mx-auto px-8 -mt-8 pb-24">

        {{-- Top highlight card --}}
        <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)] mb-6">
            <div class="flex items-center gap-3 mb-5">
                <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </span>
                <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Our Commitment</h2>
            </div>
            <p class="text-[0.88rem] text-gray-500 leading-[1.75]">ForeRent is committed to protecting the personal data of all users — property owners, managers, and tenants. We comply with the Data Privacy Act of 2012 (Republic Act No. 10173) and its implementing rules and regulations. This page outlines the measures we take to safeguard your data.</p>
        </div>

        {{-- Security measures card with list --}}
        <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)] mb-6">
            <div class="flex items-center gap-3 mb-5">
                <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </span>
                <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Security Measures</h2>
            </div>
            <p class="text-[0.88rem] text-gray-500 leading-[1.75] mb-5">ForeRent implements industry-standard security measures to protect your data:</p>
            <div class="grid grid-cols-2 gap-4">
                <div class="flex items-start gap-3 bg-[#f8faff] rounded-xl p-4 border border-[#e8edf7]">
                    <span class="inline-flex items-center justify-center w-7 h-7 min-w-[28px] bg-[#1a3fbf] rounded-lg text-white text-[0.7rem] font-bold mt-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <div>
                        <span class="text-[0.85rem] font-bold text-[#0b1f6b]">Encryption</span>
                        <p class="text-[0.82rem] text-gray-400 leading-snug mt-0.5">All data encrypted in transit (TLS/SSL) and at rest (AES-256).</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 bg-[#f8faff] rounded-xl p-4 border border-[#e8edf7]">
                    <span class="inline-flex items-center justify-center w-7 h-7 min-w-[28px] bg-[#1a3fbf] rounded-lg text-white text-[0.7rem] font-bold mt-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <div>
                        <span class="text-[0.85rem] font-bold text-[#0b1f6b]">Access Control</span>
                        <p class="text-[0.82rem] text-gray-400 leading-snug mt-0.5">Role-based access ensures users only see relevant data.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 bg-[#f8faff] rounded-xl p-4 border border-[#e8edf7]">
                    <span class="inline-flex items-center justify-center w-7 h-7 min-w-[28px] bg-[#1a3fbf] rounded-lg text-white text-[0.7rem] font-bold mt-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <div>
                        <span class="text-[0.85rem] font-bold text-[#0b1f6b]">Authentication</span>
                        <p class="text-[0.82rem] text-gray-400 leading-snug mt-0.5">Secure login with hashed passwords and session management.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 bg-[#f8faff] rounded-xl p-4 border border-[#e8edf7]">
                    <span class="inline-flex items-center justify-center w-7 h-7 min-w-[28px] bg-[#1a3fbf] rounded-lg text-white text-[0.7rem] font-bold mt-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <div>
                        <span class="text-[0.85rem] font-bold text-[#0b1f6b]">Regular Backups</span>
                        <p class="text-[0.82rem] text-gray-400 leading-snug mt-0.5">Automated daily backups with encrypted storage.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2-col grid --}}
        <div class="grid grid-cols-2 gap-6">

            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Data We Protect</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]">We protect all personal information collected through the platform, including names, email addresses, contact numbers, government IDs submitted for verification, lease agreements, payment records, maintenance request history, and property documents stored in owner vaults.</p>
            </div>

            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Data Retention</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]">We retain your personal data only for as long as necessary to fulfill the purposes for which it was collected. When a user account is deleted, personal data is purged from our active systems within 30 days. Backup copies are removed within 90 days.</p>
            </div>

            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">AI Data Processing</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]">Our AI models for rental price prediction and financial forecasting process anonymized and aggregated property data. Personal tenant information is never used in model training. AI predictions are generated using property attributes without any personally identifiable information.</p>
            </div>

            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Third-Party Processors</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]">We work with trusted third-party service providers for hosting, payment processing, and email delivery. All third-party processors are bound by data processing agreements. We do not share personal data with advertisers or data brokers.</p>
            </div>

        </div>

        {{-- Breach notification --}}
        <div class="mt-6 bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
            <div class="flex items-center gap-3 mb-5">
                <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
                <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Breach Notification</h2>
            </div>
            <p class="text-[0.88rem] text-gray-500 leading-[1.75]">In the event of a data breach that poses a risk to your rights and freedoms, we will notify affected users and the National Privacy Commission within 72 hours of becoming aware of the breach, as required by law. The notification will include the nature of the breach, data affected, and steps being taken.</p>
        </div>

        {{-- Contact DPO --}}
        <div class="mt-6 bg-[#eef2ff] border border-[#d6dff7] rounded-2xl p-8 flex items-start gap-4">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#1a3fbf] text-white shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </span>
            <div>
                <h3 class="text-[1rem] font-bold text-[#0b1f6b] mb-1">Contact Our Data Protection Officer</h3>
                <p class="text-[0.88rem] text-[#1a3fbf]/70 leading-[1.75]">If you have any questions or concerns about how your data is handled, or if you wish to exercise your data privacy rights, you may contact our Data Protection Officer at <span class="text-[#1a3fbf] font-semibold">dpo@forerent.com</span>.</p>
            </div>
        </div>
    </div>

</body>
</html>
