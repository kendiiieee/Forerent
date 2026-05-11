<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$lease = App\Models\Lease::with('bed.unit.property', 'tenant')->find(1071);
$tenant = $lease->tenant;
$component = new class {
    use App\Livewire\Concerns\WithContractData;
    public function build($t, $l) { return $this->buildContractDataArray($t, $l); }
    protected function buildOutstandingBalances($lease) { return []; }
};
$data = $component->build($tenant, $lease);
echo "termination_notice block:\n";
echo json_encode($data['termination_notice'] ?? null, JSON_PRETTY_PRINT) . "\n";
