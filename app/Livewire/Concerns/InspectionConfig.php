<?php

namespace App\Livewire\Concerns;

/**
 * Centralized inspection checklist and items configuration.
 * Used by TenantDetail and TenantDashboardOverview.
 */
class InspectionConfig
{
    public const CHECKLIST_ITEMS = [
        'Bed Frame & Mattress / Foam',
        'Cabinet / Wardrobe (doors & locks)',
        'Air Conditioning Unit & Remote',
        'Bathroom Fixtures (shower, toilet, faucet, heater)',
        'Electrical Outlets & Light Switches',
        'Windows, Curtains / Blinds',
        'Walls (stains, cracks, holes)',
        'Floor Condition',
        'Door Lock & Keys',
    ];

    public const RECEIVED_ITEMS = [
        'Unit Key(s)',
        'Building Access Card / Fob',
        'Wi-Fi Password / Credentials',
        'Air Conditioning Remote',
        'Cabinet Key',
    ];

    public const RETURNED_ITEMS = [
        'Unit Key(s)',
        'Building Access Card / Fob',
        'Air Conditioning Remote',
        'Cabinet Key',
    ];
}
