<?php

namespace App\Livewire\Actions;

use App\Enums\Role;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class LoginForm extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    // Terms acceptance step
    public bool $showTermsStep = false;
    public bool $hasReadTerms = false;
    public bool $hasReadPrivacy = false;
    public bool $hasReadDataCollection = false;
    public bool $termsAccepted = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function updated($propertyName)
    {
        if (!$this->showTermsStep) {
            $this->validateOnly($propertyName);
        }
    }

    public function mount()
    {
        if (Cookie::has('email')) {
            $this->email = Cookie::get('email');
            $this->remember = true;
        }

        // Check if returning from reading terms/privacy pages
        if (session()->has('terms_pending_user_id')) {
            $this->showTermsStep = true;
            $this->email = session('terms_pending_email', '');
            $this->password = session('terms_pending_password', '');
            $this->remember = session('terms_pending_remember', false);
            $this->hasReadTerms = session('terms_has_read_terms', false);
            $this->hasReadPrivacy = session('terms_has_read_privacy', false);
            $this->hasReadDataCollection = session('terms_has_read_data_collection', false);
        }
    }

    public function login()
    {
        $this->validate();

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
        ];

        if (Auth::attempt($credentials, $this->remember)) {
            if ($this->remember) {
                Cookie::queue('email', $this->email, 43200);
            } else {
                Cookie::queue(Cookie::forget('email'));
            }

            $user = auth()->user();

            // If tenant hasn't accepted terms yet, show terms step
            if ($user->role === Role::Tenant->value && is_null($user->terms_accepted_at)) {
                // Store state in session so it survives navigation
                session([
                    'terms_pending_user_id' => $user->user_id,
                    'terms_pending_email' => $this->email,
                    'terms_pending_password' => $this->password,
                    'terms_pending_remember' => $this->remember,
                    'terms_has_read_terms' => false,
                    'terms_has_read_privacy' => false,
                    'terms_has_read_data_collection' => false,
                ]);

                Auth::logout();
                $this->showTermsStep = true;
                return;
            }

            session()->flash('success', 'Login successful!');

            return match ($user->role) {
                Role::Landlord->value => redirect()->route('landlord.dashboard'),
                Role::Manager->value => redirect()->route('manager.dashboard'),
                Role::Tenant->value => redirect()->route('tenant.dashboard'),
                default => redirect()->route('landing.home'),
            };
        }

        session()->flash('error', 'Invalid email or password.');
    }

    public function acceptTerms()
    {
        if (!$this->hasReadTerms || !$this->hasReadPrivacy || !$this->hasReadDataCollection) {
            $this->addError('terms', 'Please read the Terms of Service, Privacy Policy, and Data Collection Policy first.');
            return;
        }

        $email = session('terms_pending_email', $this->email);
        $password = session('terms_pending_password', $this->password);
        $remember = session('terms_pending_remember', $this->remember);

        // Re-authenticate and save terms acceptance
        if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            $user = auth()->user();
            $user->terms_accepted_at = now();
            $user->save();

            // Clear terms session data
            session()->forget([
                'terms_pending_user_id',
                'terms_pending_email',
                'terms_pending_password',
                'terms_pending_remember',
                'terms_has_read_terms',
                'terms_has_read_privacy',
                'terms_has_read_data_collection',
            ]);

            session()->flash('success', 'Login successful!');
            return redirect()->route('tenant.dashboard');
        }

        session()->flash('error', 'Something went wrong. Please try again.');
        $this->showTermsStep = false;
    }

    public function backToLogin()
    {
        session()->forget([
            'terms_pending_user_id',
            'terms_pending_email',
            'terms_pending_password',
            'terms_pending_remember',
            'terms_has_read_terms',
            'terms_has_read_privacy',
        ]);

        $this->showTermsStep = false;
        $this->hasReadTerms = false;
        $this->hasReadPrivacy = false;
        $this->hasReadDataCollection = false;
        $this->termsAccepted = false;
    }

    public function render()
    {
        return view('livewire.forms.login');
    }
}
