<div class="flex items-center justify-end gap-4 px-6 py-4">


    @unless(
        request()->is('tenant') ||
        request()->is('landlord') ||
        request()->is('manager') ||
        request()->is('admin') ||
        request()->is('*dashboard*')
    )
        <div class="relative flex-1 max-w-xl">
            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <input
                type="text"
                wire:model.live.debounce.300ms="searchQuery"
                autocomplete="off"
                class="w-full pl-12 pr-10 py-3 text-gray-900 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm"
                placeholder="Search properties, units, tenants...">

            @if($searchQuery)
            <button
                wire:click="clearSearch"
                class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            @endif
        </div>
    @endunless
  

</div>
