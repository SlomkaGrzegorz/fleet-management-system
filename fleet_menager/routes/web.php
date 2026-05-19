<?php

declare(strict_types=1);

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Driver;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Manager;
use Illuminate\Support\Facades\Route;

// -----------------------------------------------------------------------------
// Publiczne / autoryzacja
// -----------------------------------------------------------------------------
Route::get('/', fn () => redirect()->route('home'));

Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// -----------------------------------------------------------------------------
// Strona startowa - przekierowuje na właściwy dashboard
// -----------------------------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // -------------------------------------------------------------------------
    // Sekcja KIEROWCY (admin też ma dostęp - middleware EnsureRole)
    // -------------------------------------------------------------------------
    Route::middleware('role:driver')
        ->prefix('driver')
        ->name('driver.')
        ->group(function () {
            Route::get('/dashboard', [Driver\DashboardController::class, 'index'])->name('dashboard');

            Route::get('/incidents',           [Driver\IncidentController::class, 'index'])->name('incidents.index');
            Route::get('/incidents/create',    [Driver\IncidentController::class, 'create'])->name('incidents.create');
            Route::post('/incidents',          [Driver\IncidentController::class, 'store'])->name('incidents.store');
            Route::get('/incidents/{incident}', [Driver\IncidentController::class, 'show'])->name('incidents.show');

            Route::get('/costs',              [Driver\CostController::class, 'index'])->name('costs.index');
            Route::get('/costs/create',       [Driver\CostController::class, 'create'])->name('costs.create');
            Route::post('/costs',             [Driver\CostController::class, 'store'])->name('costs.store');
            Route::get('/costs/{cost}',       [Driver\CostController::class, 'show'])->name('costs.show');
        });

    // -------------------------------------------------------------------------
    // Sekcja MANAGERA (admin też wpada)
    // -------------------------------------------------------------------------
    Route::middleware('role:manager')
        ->prefix('manager')
        ->name('manager.')
        ->group(function () {
            Route::get('/dashboard', [Manager\DashboardController::class, 'index'])->name('dashboard');

            Route::get('/vehicles',                [Manager\VehicleController::class, 'index'])->name('vehicles.index');
            Route::get('/vehicles/{vehicle}',      [Manager\VehicleController::class, 'show'])->name('vehicles.show');

            Route::get('/vehicles/{vehicle}/assign',  [Manager\AssignmentController::class, 'create'])->name('assignments.create');
            Route::post('/vehicles/{vehicle}/assign', [Manager\AssignmentController::class, 'store'])->name('assignments.store');
            Route::delete('/vehicles/{vehicle}/assign', [Manager\AssignmentController::class, 'destroy'])->name('assignments.destroy');

            Route::get('/events',                [Manager\EventController::class, 'index'])->name('events.index');
            Route::get('/events/{event}',        [Manager\EventController::class, 'show'])->name('events.show');
            Route::patch('/events/{event}/status', [Manager\EventController::class, 'updateStatus'])->name('events.status');

            Route::get('/costs',         [Manager\CostController::class, 'index'])->name('costs.index');
            Route::get('/costs/export',  [Manager\CostController::class, 'export'])->name('costs.export');

            Route::get('/alerts',                  [Manager\AlertController::class, 'index'])->name('alerts.index');
            Route::patch('/alerts/{alert}/dismiss',[Manager\AlertController::class, 'dismiss'])->name('alerts.dismiss');
        });

    // -------------------------------------------------------------------------
    // Sekcja ADMINA
    // -------------------------------------------------------------------------
    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            // Dashboard admina = ten sam co manager (admin go widzi)
            Route::get('/dashboard', [Manager\DashboardController::class, 'index'])->name('dashboard');

            // Pojazdy: tworzenie i usuwanie - wyłącznie admin
            Route::get('/vehicles/create',  [Admin\VehicleController::class, 'create'])->name('vehicles.create');
            Route::post('/vehicles',        [Admin\VehicleController::class, 'store'])->name('vehicles.store');
            Route::delete('/vehicles/{vehicle}', [Admin\VehicleController::class, 'destroy'])->name('vehicles.destroy');

            // Usuwanie zgłoszeń
            Route::delete('/events/{event}', [Admin\EventController::class, 'destroy'])->name('events.destroy');

            // Użytkownicy
            Route::get('/users',                [Admin\UserController::class, 'index'])->name('users.index');
            Route::get('/users/create',         [Admin\UserController::class, 'create'])->name('users.create');
            Route::post('/users',               [Admin\UserController::class, 'store'])->name('users.store');
            Route::delete('/users/{user}',      [Admin\UserController::class, 'destroy'])->name('users.destroy');

            // Raporty
            Route::get('/reports',          [Admin\ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/costs.csv',[Admin\ReportController::class, 'exportCosts'])->name('reports.costs.export');
        });
});
