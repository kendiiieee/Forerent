<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\SettingsProfileController;
use App\Models\Property;
use App\Models\Unit;

// Import the Forgot Password Component
use App\Livewire\Auth\ForgotPassword;

// ─── LANDING PAGE (public, no auth required) ────────────────────────────────
Route::get('/', function (Request $request) {
    // Build property data with unit options for cascading dropdowns
    $properties = Property::with(['units' => function ($q) {
        $q->select('unit_id', 'property_id', 'occupants', 'room_cap', 'price');
    }])->get();

    $propertyData = $properties->map(fn($p) => [
        'address'    => $p->address,
        'unitTypes'  => $p->units->pluck('occupants')->unique()->values(),
        'roomTypes'  => $p->units->pluck('room_type')->filter()->unique()->values(),
        'prices'     => $p->units->pluck('price')->sort()->values(),
    ])->values();

    $addresses = $properties->pluck('address')->sort()->values();

    $units = null;
    $hasSearch = $request->hasAny(['address', 'unit_type', 'price', 'furnishing']);

    if ($hasSearch) {
        $query = Unit::query()
            ->whereHas('beds', fn($q) => $q->where('status', 'Vacant'))
            ->with(['property.photos', 'beds' => fn($q) => $q->where('status', 'Vacant')]);

        if ($request->filled('address')) {
            $query->whereHas('property', fn($q) => $q->where('address', $request->address));
        }
        if ($request->filled('unit_type')) {
            $query->where('occupants', $request->unit_type);
        }
        if ($request->filled('price')) {
            $range = $request->price;
            if (str_ends_with($range, '+')) {
                $query->where('price', '>=', (int) rtrim($range, '+'));
            } else {
                [$min, $max] = explode('-', $range);
                $query->whereBetween('price', [(int) $min, (int) $max]);
            }
        }
        if ($request->filled('furnishing')) {
            $query->where('furnishing', $request->furnishing);
        }

        $units = $query->paginate(12)->withQueryString();
    }

    return view('users.landing', compact('addresses', 'units', 'hasSearch', 'propertyData'));
})->name('landing');

Route::get('/privacy-policy', function () {
    return view('users.privacy-policy');
})->name('privacy-policy');

Route::get('/terms-of-service', function () {
    return view('users.terms-of-service');
})->name('terms-of-service');

Route::get('/data-protection', function () {
    return view('users.data-protection');
})->name('data-protection');

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

Route::post('/settings/profile', [SettingsProfileController::class, 'update'])
    ->middleware('auth')
    ->name('settings.profile.update');

require __DIR__ . '/auth.php';
require __DIR__ . '/modules/landing.php';
require __DIR__ . '/modules/landlord.php';
require __DIR__ . '/modules/manager.php';
require __DIR__ . '/modules/tenant.php';
