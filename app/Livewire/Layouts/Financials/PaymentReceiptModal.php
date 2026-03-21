<?php

namespace App\Livewire\Layouts\Financials;

use Livewire\Component;
use Livewire\Attributes\On;

class PaymentReceiptModal extends Component
{
    public $isOpen = false;
    public $data = [];


    #[On('open-payment-receipt')]
    public function open($data)
    {
        $this->data = $data;
        $this->isOpen = true;
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function render()
    {
        return view('livewire.layouts.financials.payment-receipt-modal');
    }
}
