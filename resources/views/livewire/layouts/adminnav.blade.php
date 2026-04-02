<aside id="sidebar-multi-level-sidebar"
       class="h-full bg-white border-r border-gray-200"
       aria-label="Sidebar">
    <div class="h-full px-3 py-6 overflow-y-auto flex flex-col">
        <!-- Logo -->
        <a href="#" class="flex items-center justify-center mb-8">
            <img src="{{ asset('images/forerent-logo.svg') }}" alt="ForeRent" class="h-8" />
        </a>

        <!-- Main Navigation -->
        <ul class="space-y-2 font-medium flex-1">
            <li>
                <a href="#"
                   class="flex items-center p-3 text-gray-700 rounded-lg hover:bg-[#DFE8FC] hover:text-[#070642] group">
                    <x-icons.dashboard />
                    <span class="ms-3">Dashboard</span>
                </a>
            </li>

            <li>
                <a href="#"
                   class="flex items-center p-3 text-gray-700 rounded-lg hover:bg-[#DFE8FC] hover:text-[#070642] group">
                    <x-icons.property />
                    <span class="ms-3">Properties</span>
                </a>
            </li>

            <li>
                <a href="#"
                   class="flex items-center p-3 text-gray-700 rounded-lg hover:bg-[#DFE8FC] hover:text-[#070642] group">
                    <x-icons.payments />
                    <span class="ms-3">Payments</span>
                </a>
            </li>

            <li>
                <a href="/revenue"
                   class="flex items-center p-3 text-gray-700 rounded-lg hover:bg-[#DFE8FC] hover:text-[#070642] group">
                    <x-icons.revenue />
                    <span class="ms-3">Revenue</span>
                </a>
            </li>

            <li>
                <a href="#"
                   class="flex items-center p-3 text-gray-700 rounded-lg hover:bg-[#DFE8FC] hover:text-[#070642] group">
                    <x-icons.maintenance />
                    <span class="ms-3">Maintenance</span>
                </a>
            </li>

            <li>
                <a href="#"
                   class="flex items-center p-3 text-gray-700 rounded-lg hover:bg-[#DFE8FC] hover:text-[#070642] group">
                    <x-icons.messages />
                    <span class="ms-3">Messages</span>
                </a>
            </li>
        </ul>

        <!-- Divider -->
        <div class="border-t border-gray-200 my-4"></div>

        <!-- Others Section -->
        <p class="px-3 mb-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">
            Others
        </p>

        <ul class="space-y-2 font-medium">
            <li>
                <a href="{{ route('settings') }}" class="flex items-center p-3 text-gray-700 rounded-lg hover:bg-[#DFE8FC] hover:text-[#070642] group">
                    <x-icons.settings />
                    <span class="ms-3">Settings</span>
                </a>
            </li>
            <li>
                <a href="{{ route('logout') }}" data-logout-trigger class="flex items-center p-3 text-gray-700 rounded-lg hover:bg-[#DFE8FC] hover:text-[#070642] group">
                    <x-icons.logout />
                    <span class="ms-3">Log Out</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
