<div class="bg-white rounded-3xl shadow-lg px-8 py-12 w-full max-w-3xl max-h-[85vh] overflow-auto">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#0b3a85] mb-2">Terms of Service & Privacy Policy</h1>
        <p class="text-sm text-gray-600">Please read and accept our Terms of Service and Privacy Policy to continue using ForeRent.</p>
    </div>

    {{-- Error Message --}}
    @if ($errors->has('terms'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-700 text-sm font-medium">{{ $errors->first('terms') }}</p>
        </div>
    @endif

    {{-- Content Section - Scrollable --}}
    <div class="relative mb-8">
        {{-- Terms & Policy Content --}}
        <div wire:click="markSectionAsRead" class="bg-gray-50 rounded-lg border border-gray-200 p-6 mb-6" style="max-height: 350px; overflow-y: auto;">
            {{-- Terms Section --}}
            <div class="mb-8">
                <h2 class="text-xl font-bold text-[#070589] mb-4">Terms of Service</h2>
                <div class="text-sm text-gray-700 space-y-3 leading-relaxed">
                    <p>
                        <strong>1. Acceptance of Terms</strong><br>
                        By accessing and using ForeRent, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.
                    </p>
                    <p>
                        <strong>2. Use License</strong><br>
                        Permission is granted to temporarily download one copy of the materials (information or software) on ForeRent for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:
                        <ul class="list-disc list-inside mt-2 ml-4 space-y-1">
                            <li>Modify or copy the materials</li>
                            <li>Use the materials for any commercial purpose or for any public display</li>
                            <li>Attempt to decompile or reverse engineer any software contained on ForeRent</li>
                            <li>Remove any copyright or other proprietary notations from the materials</li>
                            <li>Transfer the materials to another person if you do not own them</li>
                        </ul>
                    </p>
                    <p>
                        <strong>3. Disclaimer</strong><br>
                        The materials on ForeRent are provided on an 'as is' basis. ForeRent makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.
                    </p>
                    <p>
                        <strong>4. Limitations</strong><br>
                        In no event shall ForeRent or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on ForeRent.
                    </p>
                </div>
            </div>

            {{-- Policy Section --}}
            <div class="mb-8 pt-6 border-t border-gray-300">
                <h2 class="text-xl font-bold text-[#070589] mb-4">Privacy Policy</h2>
                <div class="text-sm text-gray-700 space-y-3 leading-relaxed">
                    <p>
                        <strong>1. Information We Collect</strong><br>
                        We collect information you voluntarily provide, such as your name, email, contact number, and profile information. We also collect information about your use of ForeRent, such as the pages you visit and the actions you take.
                    </p>
                    <p>
                        <strong>2. How We Use Your Information</strong><br>
                        We use the information we collect to:
                        <ul class="list-disc list-inside mt-2 ml-4 space-y-1">
                            <li>Provide and improve our services</li>
                            <li>Communicate with you about your account and updates</li>
                            <li>Prevent fraud and enhance security</li>
                            <li>Comply with legal obligations</li>
                        </ul>
                    </p>
                    <p>
                        <strong>3. Data Security</strong><br>
                        We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.
                    </p>
                    <p>
                        <strong>4. Third-Party Sharing</strong><br>
                        We do not sell, trade, or rent your personal information to third parties. We may share information with service providers who assist us in operating our website and conducting our business.
                    </p>
                    <p>
                        <strong>5. Your Rights</strong><br>
                        You have the right to access, update, or delete your personal information by logging into your account or contacting support.
                    </p>
                </div>
            </div>

            {{-- View Full Links --}}
            <div class="pt-6 border-t border-gray-300 text-sm text-gray-600">
                <p>
                    For the complete versions, please visit:
                    <a href="{{ route('terms-of-service') }}" target="_blank" class="text-[var(--color-primary)] hover:underline">Terms of Service</a> |
                    <a href="{{ route('privacy-policy') }}" target="_blank" class="text-[var(--color-primary)] hover:underline">Privacy Policy</a>
                </p>
            </div>
        </div>

        {{-- Acknowledgment Checkbox --}}
        <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6 mb-6">
            <label class="flex items-start gap-3 cursor-pointer">
                <input 
                    type="checkbox" 
                    wire:model="hasReadTerms"
                    class="w-5 h-5 mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2 cursor-pointer"
                    required>
                <span class="text-sm text-gray-700 leading-relaxed">
                    I have read and fully understand the <strong>Terms of Service</strong> and <strong>Privacy Policy</strong> above. I agree to be bound by these terms and acknowledge my responsibility to comply with all applicable laws and regulations while using ForeRent.
                </span>
            </label>
        </div>

        {{-- Information Box --}}
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
            <div class="flex gap-2">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <div>
                    <p class="text-sm text-amber-800 font-medium">Only seen once</p>
                    <p class="text-xs text-amber-700">This screen will only appear on your first login. After acceptance, you'll proceed directly to your dashboard.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex gap-3 pt-6 border-t border-gray-200">
        <button 
            wire:click="acceptTermsAndPolicy"
            :disabled="!hasReadTerms"
            @click="if (!$wire.hasReadTerms) { alert('Please confirm that you have read and agree to the Terms and Policy.'); }"
            class="flex-1 bg-[var(--color-primary)] text-white py-3 px-4 rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            I Accept & Continue
        </button>
        <a 
            href="{{ route('logout') }}"
            class="px-4 py-3 border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
            Logout
        </a>
    </div>

    {{-- Additional Info --}}
    <div class="mt-6 text-xs text-gray-500 text-center">
        <p>By continuing, you agree that you have read, understood, and accepted all terms and policies.</p>
        <p class="mt-2">If you have any questions, please <a href="mailto:support@forerent.com" class="text-[var(--color-primary)] hover:underline">contact our support team</a>.</p>
    </div>
</div>

<script>
    // Optional: Track scrolling in the terms container
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.querySelector('[style*="max-height"]');
        if (container) {
            container.addEventListener('scroll', function() {
                // User is actively engaging with the content
                // The markSectionAsRead is already called on click
            });
        }
    });
</script>
