<div>
    @if(count($amenities) > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach($amenities as $amenity)
                <div class="bg-blue-100 text-blue-800 rounded-lg px-3 py-2">
                    <span class="text-sm font-medium">{{ $amenity }}</span>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-4 text-gray-500">
            <p>No amenities available for this unit</p>
        </div>
    @endif
</div>
