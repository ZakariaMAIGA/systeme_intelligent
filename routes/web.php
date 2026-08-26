<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QueueController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth'])->group(function () {
    Route::get('/', [QueueController::class, 'index'])->name('dashboard');
    Route::post('/ticket/take', [QueueController::class, 'takeTicket'])->name('ticket.take');
    Route::post('/desk/{desk_id}/call', [QueueController::class, 'callNext'])->name('desk.call');
    Route::post('/ticket/{ticket_id}/status/{status}', [QueueController::class, 'updateStatus'])->name('ticket.status');
    Route::post('/ticket/{ticket_id}/transfer', [QueueController::class, 'transfer'])->name('ticket.transfer');
    Route::post('/desk/{desk_id}/configure', [QueueController::class, 'configureDesk'])->name('desk.configure');
    Route::get('/ia/recommendation/{action}/{service_id}', [QueueController::class, 'applyRecommendation'])->name('ia.apply');
    Route::post('/simulate/progress', [QueueController::class, 'simulateProgress'])->name('simulate.progress');
    Route::post('/system/reset', [QueueController::class, 'reset'])->name('system.reset');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
