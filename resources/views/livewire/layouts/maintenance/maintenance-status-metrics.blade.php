
<div class="w-full bg-white rounded-2xl shadow-md">

    <div class="bg-gradient-to-r from-blue-700 to-blue-600 text-white p-4 md:p-6 rounded-t-2xl">
        <h3 class="text-xl lg:text-2xl font-bold">
            Maintenance Status
        </h3>
        <p class="text-sm text-blue-100 mt-1">
            Track unit maintenance and readiness
        </p>
    </div>

    <div class="p-4 md:p-6 space-y-4">

        <div class="bg-blue-50 rounded-2xl p-4 flex items-center gap-4">
            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-blue-600 text-white rounded-lg">
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 1.263c.27.27.27.704 0 .974l-1.263 1.263M18 10.5h.75a.75.75 0 000-1.5H18v1.5zM16.5 13.5h.75a.75.75 0 000-1.5H16.5v1.5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12.75H3v-1.5h3.75v1.5zM6.75 16.5H3v-1.5h3.75v1.5zM11.25 12.75H7.5v-1.5h3.75v1.5zM11.25 16.5H7.5v-1.5h3.75v1.5z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-blue-700">Active Maintenance</p>
                <p class="text-2xl font-bold text-blue-900">{{ $activeMaintenance }} {{ Str::plural('Unit', $activeMaintenance) }}</p>
            </div>
        </div>

        <div class="bg-blue-50 rounded-2xl p-4 flex items-center gap-4">
            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-blue-600 text-white rounded-lg">
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.954a.75.75 0 011.06 0l8.954 8.954v4.5a2.25 2.25 0 01-2.25 2.25H4.5A2.25 2.25 0 012.25 16.5v-4.5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-blue-700">Pending Requests</p>
                <p class="text-2xl font-bold text-blue-900">{{ $pendingRequests }} {{ Str::plural('Request', $pendingRequests) }}</p>
            </div>
            <div class="text-sm text-gray-500">
                In Progress
            </div>
        </div>

        <div class="bg-blue-50 rounded-2xl p-4 flex items-center gap-4">
            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-blue-600 text-white rounded-lg">
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-blue-700">Average Turnaround Time</p>
                <p class="text-2xl font-bold text-blue-900">{{ $avgTurnaroundDays }} Days</p>
            </div>
        </div>

    </div>
</div>
