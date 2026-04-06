<?php

use App\Http\Controllers\TicketPurchaseController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::get('/tickets/new', [TicketPurchaseController::class, 'create'])->name('tickets.new');
    Route::post('/tickets', [TicketPurchaseController::class, 'store'])->name('tickets.store');
});

require __DIR__.'/settings.php';
