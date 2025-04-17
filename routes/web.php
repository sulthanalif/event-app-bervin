<?php

use App\Http\Controllers\LogoutController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return redirect('/login');
});

Route::group(['middleware' => 'guest'], function () {
    Volt::route('/login', 'login')->name('login');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/logout', LogoutController::class)->name('logout');

    Volt::route('/dashboard', 'dashboard')->middleware('can:dashboard')->name('dashboard');

    Volt::route('/dealers', 'pages.dealers.index')->middleware('can:manage-dealers')->name('dealers');

    Volt::route('/products', 'pages.products.index')->middleware('can:manage-products')->name('products');

    Volt::route('/budget-period', 'pages.budget-period.index')->middleware('can:manage-budget-period')->name('budget-period');

    Volt::route('/budget-period/form', 'pages.budget-period.create')->middleware('can:manage-budget-period-create')->name('budget-period-create');

    Volt::route('/users', 'pages.users.index')->middleware('can:manage-users')->name('users');

    Volt::route('/roles', 'settings.roles.index')->middleware('can:manage-roles')->name('roles');

    Volt::route('/permissions', 'settings.permissions.index')->middleware('can:manage-permissions')->name('permissions');
});
