<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AcceptTermsAndPolicy extends Component
{
    public $hasReadTerms = false;
    public $hasScrolledTerms = false;
    public $hasScrolledPolicy = false;
    
    public function acceptTermsAndPolicy()
    {
        // Validate that user has read both documents
        if (!$this->hasReadTerms) {
            $this->addError('terms', 'You must confirm that you have read and agree to the Terms and Policy.');
            return;
        }

        // Mark as accepted and redirect to dashboard
        $user = Auth::user();
        $user->update([
            'terms_and_policy_accepted' => true,
            'terms_and_policy_accepted_at' => Carbon::now(),
        ]);

        // Redirect to appropriate dashboard based on role
        return redirect()->route(match ($user->role) {
            'landlord' => 'landlord.dashboard',
            'manager' => 'manager.dashboard',
            'tenant' => 'tenant.dashboard',
            default => 'landing.home',
        });
    }

    public function markSectionAsRead()
    {
        // This is called when user scrolls through terms/policy content
        $this->hasReadTerms = true;
    }

    public function render()
    {
        return view('livewire.auth.accept-terms-and-policy')
            ->layout('layouts.guest');
    }
}
