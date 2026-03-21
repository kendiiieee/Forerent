<div x-data="{ show: @entangle('isOpen').live }">
    {{-- Custom Scrollbar Styles for a cleaner look --}}
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; margin-bottom: 10px; margin-top: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Hide everything first */
            body * { visibility: hidden !important; }

            /* Show only receipt */
            #receipt-content, #receipt-content * { visibility: visible !important; }

            #receipt-content {
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                max-height: none !important;
                overflow: visible !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif !important;
            }

            /* Hide close button and download button */
            #receipt-content .no-print { display: none !important; }

            /* Show print-only elements */
            #receipt-content .print-only { display: block !important; }

            /* Make scrollable area fully visible */
            #receipt-content .custom-scrollbar {
                overflow: visible !important;
                max-height: none !important;
                flex: none !important;
                margin: 0 !important;
            }

            /* Keep 2-column grid layout */
            #receipt-content .md\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }

            /* ===== HEADER ===== */
            #receipt-content .receipt-header-svg { display: none !important; }
            #receipt-content .receipt-header {
                background: linear-gradient(90deg, #1E42B1 0%, #224BBA 28%, #2757C8 56%, #4A83E6 92%) !important;
                border-radius: 0 !important;
            }
            #receipt-content .text-white,
            #receipt-content .text-white * {
                color: #ffffff !important;
            }

            /* ===== SECTION HEADERS (TENANT, RECIPIENT, PAYMENT DETAILS) ===== */
            #receipt-content .section-header {
                font-size: 11px !important;
                font-weight: 700 !important;
                color: #AEAEB2 !important;
                letter-spacing: 1px !important;
                text-transform: uppercase !important;
                margin-bottom: 16px !important;
            }

            /* ===== DETAIL ROWS ===== */
            #receipt-content .detail-row {
                padding: 9px 0 !important;
                margin: 0 !important;
                border-bottom: 1px solid rgba(0,0,0,0.03) !important;
            }
            #receipt-content .detail-row:last-child {
                border-bottom: none !important;
            }

            /* ===== DETAIL LABELS (Name, Unit, Building...) ===== */
            #receipt-content .detail-label {
                font-size: 14px !important;
                font-weight: 400 !important;
                color: #6E6E73 !important;
            }

            /* ===== DETAIL VALUES (Aidan Lang, 0802...) ===== */
            #receipt-content .detail-value {
                font-size: 14px !important;
                font-weight: 600 !important;
                color: #1D1D1F !important;
            }

            /* ===== STATUS TEXT (Pending) ===== */
            #receipt-content .detail-value.status-pending {
                color: #FF9F0A !important;
            }

            /* ===== FINANCIAL TABLE — keep container, white text in header ===== */
            #receipt-content .financial-header,
            #receipt-content .financial-header * {
                color: #ffffff !important;
            }

            /* ===== TOTAL AMOUNT ===== */
            #receipt-content .total-section {
                background: linear-gradient(90deg, #0071E3, #2997FF) !important;
                border-radius: 16px !important;
                padding: 18px 22px !important;
                box-shadow: 0 4px 16px rgba(0, 113, 227, 0.3) !important;
                text-align: left !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
            }
            #receipt-content .total-section .total-label {
                font-size: 13px !important;
                font-weight: 600 !important;
                color: rgba(255, 255, 255, 0.85) !important;
                letter-spacing: 0.5px !important;
                text-transform: uppercase !important;
                opacity: 1 !important;
                margin-bottom: 0 !important;
            }
            #receipt-content .total-section .total-amount {
                font-size: 22px !important;
                font-weight: 700 !important;
                color: #ffffff !important;
            }

            /* ===== PRINT FOOTER ===== */
            #receipt-content .print-footer {
                margin-top: 32px !important;
                padding-top: 20px !important;
                border-top: 1px solid rgba(0,0,0,0.06) !important;
                text-align: center !important;
            }
            #receipt-content .print-footer .footer-dates {
                display: flex !important;
                justify-content: center !important;
                gap: 48px !important;
                margin-bottom: 12px !important;
            }
            #receipt-content .print-footer .footer-date-label {
                font-size: 10px !important;
                font-weight: 700 !important;
                color: #AEAEB2 !important;
                letter-spacing: 1px !important;
                text-transform: uppercase !important;
                margin-bottom: 4px !important;
            }
            #receipt-content .print-footer .footer-date-value {
                font-size: 13px !important;
                font-weight: 600 !important;
                color: #1D1D1F !important;
            }
            #receipt-content .print-footer .footer-note {
                font-size: 11px !important;
                color: #AEAEB2 !important;
                font-weight: 400 !important;
                line-height: 1.5 !important;
            }
        }
    </style>

    <div
        x-show="show"
        x-cloak
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="display: none;"
    >
        @if(!empty($data))
        <div
            id="receipt-content"
            class="relative w-full max-w-[850px] bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
        >
            {{-- Header Section --}}
            <div class="receipt-header relative shrink-0 w-full" style="height: 180px;">
                <div class="receipt-header-svg absolute inset-0 z-0">
                    <svg class="w-full h-full" viewBox="0 0 850 180" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="header_gradient" x1="0" y1="90" x2="850" y2="90" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#1E42B1"/>
                                <stop offset="0.278859" stop-color="#224BBA"/>
                                <stop offset="0.562514" stop-color="#2757C8"/>
                                <stop offset="0.923093" stop-color="#4A83E6"/>
                            </linearGradient>
                        </defs>

                        {{-- Base Dark Blue Gradient --}}
                        <path d="M0 24C0 10.7452 10.7452 0 24 0H826C839.255 0 850 10.7452 850 24V180H0V24Z" fill="url(#header_gradient)"/>

                        {{-- Wave 1 (Leftmost wave) --}}
                        <path d="M825.997 0C839.252 0 849.997 10.7452 849.997 24V180H577.87C555.964 154.48 543.141 123.571 543.141 90.2627C543.141 56.7259 556.138 25.6204 578.319 0H825.997Z" fill="white" fill-opacity="0.08"/>

                        {{-- Wave 2 (Rightmost wave) --}}
                        <path d="M826 0C839.255 0 850 10.7452 850 24V180H596.262C575.836 154.48 563.879 123.571 563.879 90.2627C563.879 56.726 575.998 25.6204 596.681 0H826Z" fill="white" fill-opacity="0.08"/>
                    </svg>
                </div>

                <div class="relative z-10 px-8 pt-8 h-full flex justify-between items-center text-white pb-4">
                    <div>
                        <h1 class="text-[32px] font-extrabold tracking-tight uppercase">PAYMENT RECEIPT</h1>
                    </div>

                    <div class="flex gap-8 text-[13px] mr-4 mt-2">
                        <div class="space-y-1.5 font-medium opacity-90">
                            <p>Invoice Number</p>
                            <p>Issued Date</p>
                            <p>Due Date</p>
                        </div>
                        <div class="space-y-1.5 font-bold">
                            <p>{{ $data['invoice_no'] }}</p>
                            <p>{{ $data['issued_date'] }}</p>
                            <p>{{ $data['due_date'] }}</p>
                        </div>
                    </div>

                    <button @click="show = false" class="no-print absolute top-6 right-8 text-white/80 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Main Content Section --}}
            <div class="flex-1 overflow-y-auto custom-scrollbar mb-4 mt-2">
                <div class="px-8 pb-4 pt-6 font-['Open_Sans']">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

                        {{-- Left Column: Tenant Info --}}
                        <div>
                            <div class="info-card border border-[#E2E2E2] rounded-2xl p-6 shadow-sm">
                                <h3 class="section-header text-[11px] font-bold uppercase tracking-[1px] mb-5" style="color: #AEAEB2;">Tenant Information</h3>
                                <div class="flex flex-col">
                                    <div class="detail-row flex items-center pb-3 mb-3 border-b border-[#E2E2E2]">
                                        <span class="detail-label text-[14px] font-normal" style="color: #6E6E73;">Name</span>
                                        <span class="detail-value flex-1 font-semibold text-[14px] text-right" style="color: #1D1D1F;">{{ $data['tenant']['name'] }}</span>
                                    </div>
                                    <div class="detail-row flex items-center pb-3 mb-3 border-b border-[#E2E2E2]">
                                        <span class="detail-label text-[14px] font-normal" style="color: #6E6E73;">Unit</span>
                                        <span class="detail-value flex-1 font-semibold text-[14px] text-right" style="color: #1D1D1F;">{{ $data['tenant']['unit'] }}</span>
                                    </div>
                                    <div class="detail-row flex items-center pb-3 mb-3 border-b border-[#E2E2E2]">
                                        <span class="detail-label text-[14px] font-normal" style="color: #6E6E73;">Building</span>
                                        <span class="detail-value flex-1 font-semibold text-[14px] text-right" style="color: #1D1D1F;">{{ $data['tenant']['building'] }}</span>
                                    </div>
                                    <div class="detail-row flex items-start pb-3 mb-3 border-b border-[#E2E2E2]">
                                        <span class="detail-label text-[14px] font-normal pt-0.5" style="color: #6E6E73;">Address</span>
                                        <span class="detail-value flex-1 font-semibold text-[14px] text-right leading-tight max-w-[200px] ml-auto" style="color: #1D1D1F;">{{ $data['tenant']['address'] }}</span>
                                    </div>
                                    <div class="detail-row flex items-center">
                                        <span class="detail-label text-[14px] font-normal" style="color: #6E6E73;">Contact</span>
                                        <span class="detail-value flex-1 font-semibold text-[14px] text-right" style="color: #1D1D1F;">{{ $data['tenant']['contact'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Payment Details --}}
                        <div>
                            <div class="info-card border border-[#E2E2E2] rounded-2xl p-6 shadow-sm h-fit">
                                <h3 class="section-header text-[11px] font-bold uppercase tracking-[1px] mb-5" style="color: #AEAEB2;">Payment Details</h3>
                                <div class="flex flex-col">
                                    <div class="detail-row flex items-center pb-3 mb-3 border-b border-[#E2E2E2]">
                                        <span class="detail-label text-[14px] font-normal" style="color: #6E6E73;">Date Paid</span>
                                        <span class="detail-value flex-1 font-semibold text-[14px] text-right" style="color: {{ $data['payment']['date_paid'] === 'Pending' ? '#FF9F0A' : '#1D1D1F' }};">{{ $data['payment']['date_paid'] }}</span>
                                    </div>
                                    <div class="detail-row flex items-center pb-3 mb-3 border-b border-[#E2E2E2]">
                                        <span class="detail-label text-[14px] font-normal" style="color: #6E6E73;">Transaction ID</span>
                                        <span class="detail-value flex-1 font-semibold text-[14px] text-right" style="color: {{ $data['payment']['txn_id'] === 'Pending' ? '#FF9F0A' : '#1D1D1F' }};">{{ $data['payment']['txn_id'] }}</span>
                                    </div>
                                    <div class="detail-row flex items-center pb-3 mb-3 border-b border-[#E2E2E2]">
                                        <span class="detail-label text-[14px] font-normal" style="color: #6E6E73;">Lease Type</span>
                                        <span class="detail-value flex-1 font-semibold text-[14px] text-right" style="color: #1D1D1F;">{{ $data['payment']['lease_type'] }}</span>
                                    </div>
                                    <div class="detail-row flex items-center">
                                        <span class="detail-label text-[14px] font-normal" style="color: #6E6E73;">Period</span>
                                        <span class="detail-value flex-1 font-semibold text-[14px] text-right" style="color: #1D1D1F;">{{ $data['payment']['period'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Recipient Info & Financial Table Row --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        {{-- Left: Recipient Information --}}
                        <div>
                            <div class="info-card border border-[#E2E2E2] rounded-2xl p-6 shadow-sm">
                                <h3 class="section-header text-[11px] font-bold uppercase tracking-[1px] mb-5" style="color: #AEAEB2;">Recipient Information</h3>
                                <div class="flex flex-col">
                                    <div class="detail-row flex items-center pb-3 mb-3 border-b border-[#E2E2E2]">
                                        <span class="detail-label text-[14px] font-normal" style="color: #6E6E73;">Name</span>
                                        <span class="detail-value flex-1 font-semibold text-[14px] text-right" style="color: #1D1D1F;">{{ $data['recipient']['name'] }}</span>
                                    </div>
                                    <div class="detail-row flex items-center">
                                        <span class="detail-label text-[14px] font-normal" style="color: #6E6E73;">Position</span>
                                        <span class="detail-value flex-1 font-semibold text-[14px] text-right" style="color: #1D1D1F;">{{ $data['recipient']['position'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right: Financial Table --}}
                        <div>
                            <div class="financial-table-wrap rounded-xl overflow-hidden border border-gray-200 font-sans">
                                <div class="financial-header bg-[#2563EB] px-6 py-3 flex justify-between items-center">
                                    <span class="text-white text-xs font-bold uppercase tracking-wider">Description</span>
                                    <span class="text-white text-xs font-bold uppercase tracking-wider">Amount</span>
                                </div>
                                <div class="financial-row bg-white px-6 py-4 flex justify-between items-center border-b border-gray-100 min-h-[60px]">
                                    <span class="detail-label text-sm font-medium text-gray-800">{{ $data['financials']['description'] }}</span>
                                    <span class="detail-value text-sm font-bold text-gray-800">₱ {{ number_format($data['financials']['amount'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Total Amount Summary --}}
                    <div class="total-section w-full bg-gradient-to-r from-[#1D4ED8] to-[#3B82F6] rounded-xl p-6 text-center text-white shadow-lg font-sans">
                        <p class="total-label text-xs font-semibold uppercase tracking-widest opacity-90 mb-1">Total Amount Paid</p>
                        <p class="total-amount text-4xl font-extrabold tracking-tight">₱ {{ number_format($data['financials']['amount'], 2) }}</p>
                    </div>

                    {{-- Print-only Footer --}}
                    <div class="print-only print-footer" style="display: none;">
                        <div class="footer-dates">
                            <div>
                                <div class="footer-date-label">Issued</div>
                                <div class="footer-date-value">{{ $data['issued_date'] }}</div>
                            </div>
                            <div>
                                <div class="footer-date-label">Due</div>
                                <div class="footer-date-value">{{ $data['due_date'] }}</div>
                            </div>
                        </div>
                        <p class="footer-note">This receipt was generated automatically.<br>Please retain for your records.</p>
                    </div>

                </div>
            </div>

            {{-- Download PDF Button (sticky footer) --}}
            <div class="no-print shrink-0 px-8 py-4 border-t border-gray-200 bg-white flex justify-end rounded-b-3xl">
                <button
                    onclick="var t=document.title; document.title='{{ str_replace("'", "", $data['tenant']['name']) }} - {{ $data['tenant']['unit'] }} - {{ $data['payment']['period'] }}'; window.print(); document.title=t;"
                    style="background-color: #1D4ED8; color: #ffffff;"
                    class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold rounded-xl shadow-md transition-all duration-200 hover:shadow-lg cursor-pointer"
                    onmouseover="this.style.backgroundColor='#1E3A8A'"
                    onmouseout="this.style.backgroundColor='#1D4ED8'"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a2 2 0 002 2h14a2 2 0 002-2v-3" />
                    </svg>
                    Download PDF
                </button>
            </div>
        </div>
        @endif
    </div>
</div>
