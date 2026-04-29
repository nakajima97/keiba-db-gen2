<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HorseController;
use App\Http\Controllers\HorseNoteController;
use App\Http\Controllers\RaceController;
use App\Http\Controllers\RaceEntryController;
use App\Http\Controllers\RaceMarkColumnController;
use App\Http\Controllers\RaceMarkController;
use App\Http\Controllers\RaceMarkMemoController;
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

    Route::get('/races', [RaceController::class, 'index'])->name('races.index');
    Route::get('/races/new', [RaceController::class, 'create'])->name('races.create');
    Route::post('/races', [RaceController::class, 'store'])->name('races.store');
    Route::get('/races/{race:uid}', [RaceController::class, 'show'])->name('races.show');

    Route::get('/races/{race:uid}/entries/new', [RaceEntryController::class, 'create'])->name('races.entries.create');
    Route::post('/races/{race:uid}/entries', [RaceEntryController::class, 'store'])->name('races.entries.store');

    Route::get('/races/{uid}/result/new', [RaceResultController::class, 'create'])->name('races.result.create');
    Route::post('/races/{uid}/result', [RaceResultController::class, 'store'])->name('races.result.store');
    Route::get('/races/{uid}/result/edit', [RaceResultController::class, 'edit'])->name('races.result.edit');

    Route::get('/horses/{horse}', [HorseController::class, 'show'])->name('horses.show');

    Route::prefix('api')->group(function () {
        Route::get('/races/{race:uid}/mark-columns', [RaceMarkColumnController::class, 'index'])->name('api.races.mark-columns.index');
        Route::post('/races/{race:uid}/mark-columns', [RaceMarkColumnController::class, 'store'])->name('api.races.mark-columns.store');
        Route::patch('/races/{race:uid}/mark-columns/{id}', [RaceMarkColumnController::class, 'update'])->name('api.races.mark-columns.update');
        Route::delete('/races/{race:uid}/mark-columns/{id}', [RaceMarkColumnController::class, 'destroy'])->name('api.races.mark-columns.destroy');
        Route::put('/races/{race:uid}/mark-columns/{column_id}/entries/{race_entry_id}/mark', [RaceMarkController::class, 'upsert'])->name('api.races.mark-columns.entries.mark.upsert');
        Route::put('/races/{race:uid}/mark-columns/{column_id}/entries/{race_entry_id}/memo', [RaceMarkMemoController::class, 'upsert'])->name('api.races.mark-columns.entries.memo.upsert');
        Route::delete('/races/{race:uid}/mark-columns/{column_id}/entries/{race_entry_id}/memo', [RaceMarkMemoController::class, 'destroy'])->name('api.races.mark-columns.entries.memo.destroy');

        Route::get('/horses/{horse}/notes', [HorseNoteController::class, 'index'])->name('api.horses.notes.index');
        Route::post('/horses/{horse}/notes', [HorseNoteController::class, 'store'])->name('api.horses.notes.store');
        Route::put('/horse-notes/{note}', [HorseNoteController::class, 'update'])->name('api.horse-notes.update');
    });
});

require __DIR__.'/settings.php';
