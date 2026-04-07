<div class="relative w-full h-40 bg-gradient-to-r from-blue-950 via-blue-800 to-blue-600 rounded-xl sm:rounded-2xl shadow-xl overflow-hidden max-sm:h-auto">

    <!-- Circular ring effects (right side decoration) with even ripple spacing -->
    <div class="absolute max-sm:hidden" style="top: 50%; right: 230px; transform: translate(50%, -50%);">
        <div class="absolute rounded-full" style="width: 944px; height: 944px; left: 50%; top: 50%; transform: translate(-50%, -50%); border: 56px solid rgba(255,255,255,0.10);"></div>
        <div class="absolute rounded-full" style="width: 736px; height: 736px; left: 50%; top: 50%; transform: translate(-50%, -50%); border: 56px solid rgba(255,255,255,0.10);"></div>
        <div class="absolute rounded-full" style="width: 528px; height: 528px; left: 50%; top: 50%; transform: translate(-50%, -50%); border: 56px solid rgba(255,255,255,0.10);"></div>
        <div class="absolute rounded-full" style="width: 320px; height: 320px; left: 50%; top: 50%; transform: translate(-50%, -50%); border: 56px solid rgba(255,255,255,0.10);"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 px-8 flex items-center justify-between h-full w-full max-sm:flex-col max-sm:items-start max-sm:justify-center max-sm:px-4 max-sm:py-3 max-sm:gap-1">

        <!-- Left: Greeting -->
        <div class="flex flex-col justify-center">
            @php
                $user = auth()->user();
                $isTenant = $user && $user->role === 'tenant';
                $tenantInfo = null;
                if ($isTenant) {
                    $lease = $user->leases()->with('bed.unit.property')->where('status', 'active')->latest()->first();
                    if ($lease && $lease->bed && $lease->bed->unit) {
                        $bed = $lease->bed;
                        $unit = $bed->unit;
                        $property = $unit->property;
                        $tenantInfo = collect([
                            $unit->bed_type ?? null,
                            $bed->bed_number ? 'Bed ' . $bed->bed_number : null,
                            $unit->unit_number ? 'Unit ' . $unit->unit_number : null,
                        ])->filter()->implode(' • ');
                        $locationInfo = collect([
                            $property->building_name ?? null,
                            $property->address ?? null,
                        ])->filter()->implode(', ');
                        if ($locationInfo) {
                            $tenantInfo .= ' — ' . $locationInfo;
                        }
                    }
                }
            @endphp
            <p class="text-blue-300 text-[10px] sm:text-xs font-semibold uppercase tracking-widest mb-0.5 sm:mb-1">
                {{ $isTenant ? 'Tenant' : 'Property Owner' }}
            </p>
            <h1 class="text-white text-base sm:text-xl lg:text-3xl font-bold leading-tight">
                Welcome Back,
                <span class="text-cyan-400">{{ auth()->check() ? strtoupper(auth()->user()->first_name) : 'GUEST' }}!</span>
            </h1>
            <p class="text-blue-200 text-[11px] sm:text-xs lg:text-sm mt-0.5 sm:mt-1">
                @if($isTenant && $tenantInfo)
                    {{ $tenantInfo }}
                @else
                    Here's what's happening with your properties today
                @endif
            </p>
        </div>

        <!-- Right: Time & Date -->
        <div class="flex flex-col items-end justify-center max-sm:items-start">
            <p class="text-white text-sm sm:text-xl lg:text-4xl font-bold" id="greeting-time"></p>
            <p class="text-blue-200 text-[10px] sm:text-xs lg:text-sm mt-0.5 sm:mt-1" id="greeting-date"></p>
        </div>

    </div>
</div>

<!-- Live clock script -->
<script>
    function updateClock() {
        const now = new Date();
        const time = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
        const date = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
        document.getElementById('greeting-time').textContent = time;
        document.getElementById('greeting-date').textContent = date;
    }
    updateClock();
    setInterval(updateClock, 1000);
</script>
