<?php

namespace App\Livewire\Layouts\Financials;

use Livewire\Component;

class RevenueContainer extends Component
{
    public $currentView = 'reports';

    protected $queryString = ['currentView' => ['as' => 'view']];

    public function mount()
    {
        $this->currentView = request()->query('view', 'reports');
    }

    public function switchView($view)
    {
        $this->currentView = $view;
    }

    public function render()
    {
        return view('livewire.layouts.financials.revenue-container');
    }
}
