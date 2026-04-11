<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RaceResultController;
use App\Http\Controllers\TicketPurchaseController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'show'])->name('dashboard');
    Route::get('/tickets', [TicketPurchaseController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/new', [TicketPurchaseController::class, 'create'])->name('tickets.new');
    Route::post('/tickets', [TicketPurchaseController::class, 'store'])->name('tickets.store');

    Route::get('/races/{uid}/result/new', [RaceResultController::class, 'create'])->name('races.result.create');
    Route::post('/races/{uid}/result', [RaceResultController::class, 'store'])->name('races.result.store');
    Route::get('/races/{uid}/result/edit', [RaceResultController::class, 'edit'])->name('races.result.edit');
});

require __DIR__.'/settings.php';
