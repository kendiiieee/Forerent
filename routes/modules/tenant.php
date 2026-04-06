<?php

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


Route::prefix('tenant')->middleware(['auth', 'role:tenant'])->group(function () {
    // Dashboard
    Route::get('/', function () {
        return view('users.tenant.dashboard');
    })->name('tenant.dashboard');

    // Payment
    Route::get('/payment', function () {
        return view('users.tenant.payment');
    })->name('tenant.payment');

    // Maintenance
    Route::get('/maintenance', function () {
        return view('users.tenant.maintenance');
    })->name('tenant.maintenance');

});

