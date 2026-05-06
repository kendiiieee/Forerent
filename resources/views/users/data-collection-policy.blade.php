<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Collection Policy – ForeRent</title>
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
                <a href="{{ session()->has('terms_pending_user_id') ? route('login') : '/' }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white/10 border border-white/20
                           text-white text-[0.85rem] font-semibold no-underline
                           hover:bg-white/20 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ session()->has('terms_pending_user_id') ? 'Back to Login' : 'Back to Home' }}
                </a>
            </nav>
            <div class="pt-12 pb-20">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/15 text-[0.78rem] font-semibold tracking-wide mb-6">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                    Legal Document
                </span>
                <h1 class="text-[2.8rem] font-extrabold leading-tight mb-4">Data Collection Policy</h1>
                <p class="text-white/60 text-[0.95rem] max-w-[560px]">The specific information ForeRent collects when a tenant signs in to the platform and when a tenant signs a lease contract — and who is allowed to view it.</p>
                <p class="text-white/40 text-[0.82rem] mt-6">Last updated: {{ date('F d, Y') }}</p>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="max-w-[1100px] mx-auto px-8 -mt-8 pb-24">

        {{-- Scope card --}}
        <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)] mb-6">
            <div class="flex items-center gap-3 mb-5">
                <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </span>
                <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Scope of This Policy</h2>
            </div>
            <p class="text-[0.88rem] text-gray-500 leading-[1.75]">This policy explains, in plain language, exactly what personal information ForeRent records during two specific events on the tenant side of the platform: (1) when you sign in for the first time and accept our terms, and (2) when you sign a lease contract for a unit. It is published in compliance with the Data Privacy Act of 2012 (Republic Act No. 10173) and complements our Privacy Policy and Data Protection page.</p>
        </div>

        {{-- 2-col: collected at login + collected at signing --}}
        <div class="grid grid-cols-2 gap-6">

            {{-- Card: Login data --}}
            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Collected at Tenant Login</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75] mb-4">When a tenant signs in for the first time, ForeRent records the following:</p>
                <ul class="space-y-2 text-[0.85rem] text-gray-600">
                    <li class="flex items-start gap-2"><span class="text-[#1a3fbf] mt-1">•</span><span><strong class="text-[#0b1f6b]">Account credentials</strong> — your email address and a securely hashed password.</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#1a3fbf] mt-1">•</span><span><strong class="text-[#0b1f6b]">Profile details</strong> — full name, contact number, and assigned role (tenant).</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#1a3fbf] mt-1">•</span><span><strong class="text-[#0b1f6b]">Terms acceptance timestamp</strong> — the exact date and time you confirmed reading the Terms of Service, Privacy Policy, and this Data Collection Policy.</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#1a3fbf] mt-1">•</span><span><strong class="text-[#0b1f6b]">Session metadata</strong> — login state and the "remember me" preference, if selected.</span></li>
                </ul>
            </div>

            {{-- Card: Contract signing data --}}
            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Collected at Contract Signing</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75] mb-4">When a tenant signs a lease contract, ForeRent records the following:</p>
                <ul class="space-y-2 text-[0.85rem] text-gray-600">
                    <li class="flex items-start gap-2"><span class="text-[#1a3fbf] mt-1">•</span><span><strong class="text-[#0b1f6b]">Digital signature image</strong> — the signature you draw on the signing pad.</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#1a3fbf] mt-1">•</span><span><strong class="text-[#0b1f6b]">Signed timestamp</strong> — the exact date and time the signature was captured.</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#1a3fbf] mt-1">•</span><span><strong class="text-[#0b1f6b]">Contract agreement flag</strong> — confirmation that you read and agreed to the contract before signing.</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#1a3fbf] mt-1">•</span><span><strong class="text-[#0b1f6b]">Audit trail entry</strong> — an immutable log entry recording the action, the field that changed, and the user who performed the action.</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#1a3fbf] mt-1">•</span><span><strong class="text-[#0b1f6b]">Lease references</strong> — the unit, property, and lease record this signature is bound to.</span></li>
                </ul>
            </div>

        </div>

        {{-- Who can view the contract (the "kineme" requirement) --}}
        <div class="mt-6 bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
            <div class="flex items-center gap-3 mb-5">
                <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </span>
                <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Who Can View Your Contract</h2>
            </div>
            <p class="text-[0.88rem] text-gray-500 leading-[1.75] mb-5">Once a contract is signed, access to the signed document is strictly limited to the parties involved in the lease. ForeRent enforces role-based access control so that no other user — including other tenants on the platform — can view your contract.</p>

            <div class="grid grid-cols-3 gap-4">
                <div class="bg-[#f8faff] border border-[#e8edf7] rounded-xl p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center bg-[#1a3fbf] text-white">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        </span>
                        <h3 class="text-[0.9rem] font-bold text-[#0b1f6b]">Property Owner</h3>
                    </div>
                    <p class="text-[0.8rem] text-gray-500 leading-snug">The landlord who owns the unit being leased. Required to view, counter-sign, and approve the contract.</p>
                </div>
                <div class="bg-[#f8faff] border border-[#e8edf7] rounded-xl p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center bg-[#1a3fbf] text-white">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </span>
                        <h3 class="text-[0.9rem] font-bold text-[#0b1f6b]">Property Manager</h3>
                    </div>
                    <p class="text-[0.8rem] text-gray-500 leading-snug">The manager assigned by the owner to handle the unit. Reviews and counter-signs the contract on the owner's behalf.</p>
                </div>
                <div class="bg-[#f8faff] border border-[#e8edf7] rounded-xl p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center bg-[#1a3fbf] text-white">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        <h3 class="text-[0.9rem] font-bold text-[#0b1f6b]">The Tenant</h3>
                    </div>
                    <p class="text-[0.8rem] text-gray-500 leading-snug">You — the tenant whose name appears on the lease. You retain the right to view your own contract at any time from your dashboard.</p>
                </div>
            </div>

            <p class="text-[0.82rem] text-gray-400 leading-[1.75] mt-5 italic">No advertiser, third-party service, or other tenant on the platform can access your signed contract. Disclosure to authorities is made only when legally compelled.</p>
        </div>

        {{-- Reminder after signing (the "reminder for client once they sign" requirement) --}}
        <div class="mt-6 bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
            <div class="flex items-center gap-3 mb-5">
                <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </span>
                <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Reminder &amp; Notification After Signing</h2>
            </div>
            <p class="text-[0.88rem] text-gray-500 leading-[1.75] mb-4">As soon as your signature is captured, ForeRent automatically performs the following so that you, and only the relevant parties, are kept informed:</p>
            <div class="grid grid-cols-2 gap-4">
                <div class="flex items-start gap-3 bg-[#f8faff] rounded-xl p-4 border border-[#e8edf7]">
                    <span class="inline-flex items-center justify-center w-7 h-7 min-w-[28px] bg-[#1a3fbf] rounded-lg text-white mt-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <div>
                        <span class="text-[0.85rem] font-bold text-[#0b1f6b]">Confirmation reminder to the tenant</span>
                        <p class="text-[0.82rem] text-gray-400 leading-snug mt-0.5">An in-app notification is delivered to your account confirming the signature, the time it was captured, and a direct link back to the signed contract for your reference.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 bg-[#f8faff] rounded-xl p-4 border border-[#e8edf7]">
                    <span class="inline-flex items-center justify-center w-7 h-7 min-w-[28px] bg-[#1a3fbf] rounded-lg text-white mt-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <div>
                        <span class="text-[0.85rem] font-bold text-[#0b1f6b]">Notification to owner &amp; manager</span>
                        <p class="text-[0.82rem] text-gray-400 leading-snug mt-0.5">The property owner and the assigned manager receive a notification that the tenant has signed, prompting them to review and counter-sign.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 bg-[#f8faff] rounded-xl p-4 border border-[#e8edf7]">
                    <span class="inline-flex items-center justify-center w-7 h-7 min-w-[28px] bg-[#1a3fbf] rounded-lg text-white mt-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <div>
                        <span class="text-[0.85rem] font-bold text-[#0b1f6b]">Audit log written</span>
                        <p class="text-[0.82rem] text-gray-400 leading-snug mt-0.5">A tamper-evident audit entry records the action so that the signing event can be verified later if disputed.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 bg-[#f8faff] rounded-xl p-4 border border-[#e8edf7]">
                    <span class="inline-flex items-center justify-center w-7 h-7 min-w-[28px] bg-[#1a3fbf] rounded-lg text-white mt-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <div>
                        <span class="text-[0.85rem] font-bold text-[#0b1f6b]">Status update on the dashboard</span>
                        <p class="text-[0.82rem] text-gray-400 leading-snug mt-0.5">Your tenant dashboard updates in real time to reflect the new contract status (pending owner, pending manager, or executed).</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Purpose & Retention --}}
        <div class="mt-6 grid grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">Why We Collect This Data</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]">Login data is collected to authenticate you and to prove that you reviewed the platform's policies. Contract-signing data is collected to make the lease legally binding, to compute rent and utility billing, and to maintain a verifiable record of the agreement between you, the property owner, and the manager.</p>
            </div>

            <div class="bg-white rounded-2xl border border-[#e8edf7] p-8 shadow-[0_2px_12px_rgba(11,31,107,0.06)]">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#eef2ff] text-[#1a3fbf]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <h2 class="text-[1.05rem] font-bold text-[#0b1f6b]">How Long We Keep It</h2>
                </div>
                <p class="text-[0.88rem] text-gray-500 leading-[1.75]">Login records are kept for as long as your account is active. Signed contracts and their audit trails are retained for the entire duration of the lease and for the period required by Philippine law afterwards, in case the agreement needs to be referenced for tax, regulatory, or dispute-resolution purposes.</p>
            </div>
        </div>

        {{-- Tenant rights --}}
        <div class="mt-6 bg-[#eef2ff] border border-[#d6dff7] rounded-2xl p-8 flex items-start gap-4">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#1a3fbf] text-white shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </span>
            <div>
                <h3 class="text-[1rem] font-bold text-[#0b1f6b] mb-1">Your Rights as a Data Subject</h3>
                <p class="text-[0.88rem] text-[#1a3fbf]/70 leading-[1.75]">Under the Data Privacy Act of 2012 (RA 10173), you have the right to be informed about, access, correct, and object to the processing of your personal data. You may also request a copy of the data we hold about you. To exercise these rights, contact our Data Protection Officer at <span class="text-[#1a3fbf] font-semibold">dpo@forerent.com</span>.</p>
            </div>
        </div>
    </div>

</body>
</html>
