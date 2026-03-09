<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\PropertyController;

// Import the Forgot Password Component
use App\Livewire\Auth\ForgotPassword;

// ─── LANDING PAGE (public, no auth required) ────────────────────────────────
Route::get('/', function () {
    return view('users.landing');
})->name('landing');

// ─── HOME (after login, redirects based on role) ────────────────────────────
Route::get('/home', function () {
    $user = Auth::user();

    return match ($user->role) {
        'landlord' => redirect()->route('landlord.dashboard'),
        'manager'  => redirect()->route('manager.dashboard'),
        'tenant'   => redirect()->route('tenant.dashboard'),
        default    => redirect()->route('login'),
    };
})->middleware('auth')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');

    Route::get('/reset-password/{token}', function ($token) {
        return view('auth.reset-password', ['token' => $token]);
    })->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::get('/property', [PropertyController::class, 'index'])->name('properties.index');
    Route::get('/properties/create', [PropertyController::class, 'create'])->name('properties.create');
});

Route::get('/revenue', function () {
    return view('users.admin.owner.revenue');
})->name('revenue');

// Messages
// 1. Landlord
Route::middleware(['auth', 'role:landlord'])->prefix('landlord')->group(function () {
    Route::get('/messages', function () {
        return view('users.message');
    })->name('landlord.messages');
});

// 2. Manager
Route::middleware(['auth', 'role:manager'])->prefix('manager')->group(function () {
    Route::get('/messages', function () {
        return view('users.message');
    })->name('manager.messages');
});

// 3. Tenant
Route::middleware(['auth', 'role:tenant'])->prefix('tenant')->group(function () {
    Route::get('/messages', function () {
        return view('users.message');
    })->name('tenant.messages');
});

// Settings
Route::get('/settings', function () {
    return view('users.settings');
})->middleware('auth')->name('settings');

require __DIR__ . '/auth.php';
require __DIR__ . '/modules/landing.php';
require __DIR__ . '/modules/landlord.php';
require __DIR__ . '/modules/manager.php';
require __DIR__ . '/modules/tenant.php';
