<?php

use App\Livewire\Layouts\Dashboard\Dashboard;
use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;
/*
*--------------------------------------------------------------------------
* Landlord Routes
*--------------------------------------------------------------------------
*
* This file defines all web routes that are specifically accessible to
* users with the "landlord" role. It uses a route prefix ('landlord')
* and a middleware group to restrict access to authenticated landlords only.
*
* Middleware applied:
* - 'auth' ensures only logged-in users can access these routes.
* - 'role:landlord' ensures only users with the 'landlord' role are allowed.
*/


Route::prefix('landlord')->middleware(['auth', 'role:landlord'])->group(function () {
    // Dashboard
    Route::get('/', Dashboard::class)->name('landlord.dashboard');

    // Properties
    Route::prefix('property')->group(function () {
        Route::get('/', [PropertyController::class, 'index'])->name('landlord.property');

        Route::get('/create', [PropertyController::class, 'create'])->name('landlord.property.create');

        Route::get('/add-unit', function () {
            return view('users.admin.owner.addunit');
        })->name('landlord.property.add-unit');
    });

    // Managers
    Route::get('/manager', function () {
        return view('users.admin.owner.managerdetails');
    })->name('landlord.manager');

    // Payments
    Route::get('/payment', function () {
        return view('users.admin.owner.payment');
    })->name('landlord.payment');

    // Revenue
    Route::get('/revenue', function () {
        return view('users.admin.owner.revenue');
    })->name('landlord.revenue');
});

