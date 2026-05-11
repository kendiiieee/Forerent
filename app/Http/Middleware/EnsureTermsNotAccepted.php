<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureTermsNotAccepted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // If user is not authenticated, redirect to login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // If user has already accepted terms, redirect to dashboard
        if ($user->terms_and_policy_accepted) {
            return redirect()->route(match ($user->role) {
                'landlord' => 'landlord.dashboard',
                'manager' => 'manager.dashboard',
                'tenant' => 'tenant.dashboard',
                default => 'landing.home',
            });
        }

        return $next($request);
    }
}
