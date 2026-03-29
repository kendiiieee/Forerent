<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy – ForeRent</title>
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
            {{-- Nav --}}
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

            {{-- Hero content --}}
            <div class="pt-12 pb-20">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/15 text-[0.78rem] font-semibold tracking-wide mb-6">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Legal Document
                </span>
                <h1 class="text-[2.8rem] font-extrabold leading-tight mb-4">Privacy Policy</h1>
                <p class="text-white/60 text-[0.95rem] max-w-[500px]">How we collect, use, and protect your personal information on the ForeRent platform.</p>
                <p class="text-white/40 text-[0.82rem] mt-6">Last updated: {{ date('F d, Y') }}</p>
            </div>
        </div>
    </div>

    {{-- Content cards --}}
    <div class="max-w-[1100px] mx-auto px-8 -mt-8 pb-24">
        <div class="grid grid-cols-2 gap-6">

            {{-- Card 1 --}}
            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Information We Collect</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]">ForeRent collects personal information that you provide when creating an account, including your full name, email address, phone number, and role (property owner, manager, or tenant). We also collect property-related data such as building addresses, unit details, lease agreements, and financial records that you input into the platform.</p>
            </div>

            {{-- Card 2 --}}
            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">How We Use Your Information</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]">We use your information to provide and improve our property management services, including AI-powered rental price predictions, financial forecasting, maintenance request processing, and communication between owners, managers, and tenants. Your data helps us deliver accurate predictions and personalized dashboards.</p>
            </div>

            {{-- Card 3 --}}
            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Data Storage and Security</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]">All personal and financial data is stored on secure servers with encryption at rest and in transit. We implement role-based access controls to ensure that users can only access information relevant to their role. Property documents are stored in encrypted vaults accessible only to authorized users.</p>
            </div>

            {{-- Card 4 --}}
            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Data Sharing</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]">ForeRent does not sell, trade, or rent your personal information to third parties. We may share data with service providers who assist in platform operations (such as payment processors), but only under strict confidentiality agreements. We may disclose information when required by law or to protect the rights and safety of our users.</p>
            </div>

            {{-- Card 5 --}}
            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Cookies and Analytics</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]">We use cookies and similar technologies to enhance your experience, remember preferences, and analyze platform usage. You can manage cookie settings through your browser. Analytics data is collected in aggregate form and does not personally identify individual users.</p>
            </div>

            {{-- Card 6 --}}
            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Your Rights</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]">You have the right to access, update, or delete your personal information at any time through your account settings. You may also request a copy of your data or ask us to restrict processing. To exercise these rights, contact us at <span class="text-[#1a3fbf] font-semibold">privacy@forerent.com</span>.</p>
            </div>

        </div>

        {{-- Full-width note --}}
        <div class="mt-6 bg-[#eef2ff] border border-[#d6dff7] rounded-2xl p-8 flex items-start gap-4">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#1a3fbf] text-white shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <div>
                <h3 class="text-[1rem] font-bold text-[#0b1f6b] mb-1">Changes to This Policy</h3>
                <p class="text-[0.88rem] text-[#1a3fbf]/70 leading-[1.75]">We may update this Privacy Policy from time to time. Any changes will be posted on this page with an updated revision date. We encourage you to review this policy periodically to stay informed about how we protect your information.</p>
            </div>
        </div>
    </div>

</body>
</html>
