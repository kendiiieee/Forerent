<?php

namespace App\Livewire\Layouts\Units;

use App\Livewire\Concerns\InspectionConfig;
use App\Livewire\Concerns\WithContractData;
use App\Models\Lease;
use Livewire\Component;
use Livewire\Attributes\On;

class LandlordContractViewer extends Component
{
    use WithContractData;

    public $showModal = false;
    public $contractType = 'move-in';
    public $contractData = null;
    public $leaseId = null;

    // Move-in signature display
    public $tenantSignature = null;
    public $ownerSignature = null;
    public $tenantSignedAt = null;
    public $ownerSignedAt = null;
    public $contractAgreed = false;

    // Move-out signature display
    public $moveOutTenantSignature = null;
    public $moveOutOwnerSignature = null;
    public $moveOutTenantSignedAt = null;
    public $moveOutOwnerSignedAt = null;
    public $moveOutContractAgreed = false;

    // Inspection data
    public $inspectionChecklist = [];
    public $itemsReceived = [];
    public $moveOutChecklist = [];
    public $itemsReturned = [];
    public $inspectionSaved = false;
    public $moveOutInspectionSaved = false;

    #[On('open-landlord-contract-viewer')]
    public function openViewer(int $leaseId, string $contractType = 'move-in'): void
    {
        $this->contractType = $contractType;
        $this->leaseId = $leaseId;

        $lease = Lease::with([
            'tenant',
            'bed.unit.property.owner',
            'billings',
            'moveInInspections',
            'moveOutInspections',
        ])->find($leaseId);

        if (!$lease || !$lease->tenant) {
            return;
        }

        $this->contractData = $this->buildContractDataArray($lease->tenant, $lease);
        $this->loadSignatureState($lease);

        // Load move-in inspection
        $this->loadInspection(
            $lease,
            'moveInInspections',
            'inspectionChecklist',
            'itemsReceived',
            'inspectionSaved',
            'item_received',
            InspectionConfig::RECEIVED_ITEMS
        );

        // Load move-out inspection
        $this->loadInspection(
            $lease,
            'moveOutInspections',
            'moveOutChecklist',
            'itemsReturned',
            'moveOutInspectionSaved',
            'item_returned',
            InspectionConfig::RETURNED_ITEMS
        );

        $this->showModal = true;
    }

    public function switchTab(string $type): void
    {
        $this->contractType = $type;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->contractData = null;
    }

    public function render()
    {
        return view('livewire.layouts.units.landlord-contract-viewer');
    }
}
