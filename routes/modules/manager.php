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


Route::prefix('manager')->middleware(['auth', 'role:manager'])->group(function () {
    // Dashboard
    Route::get('/', function () {
        return view('users.admin.manager.dashboard');
    })->name('manager.dashboard');

    // Properties
    Route::get('/property', function () {
        return view('users.admin.manager.property');
    })->name('manager.property');

    // Tenants
    Route::get('/tenant', function () {
        return view('users.admin.manager.tenant');
    })->name('manager.tenant');

    // Payment
    Route::get('/payment', function () {
        return view('users.admin.manager.payment');
    })->name('manager.payment');

    // Maintenance
    Route::get('/maintenance', function () {
        return view('users.admin.manager.maintenance'); // TO DO
    })->name('manager.maintenance');

});

