<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\AdminUserController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth'])->group(function () {
    Route::get('/', [QueueController::class, 'index'])->name('dashboard');
    Route::post('/ticket/payment', [QueueController::class, 'startTicketPayment'])->name('ticket.payment.start');
    Route::get('/ticket/payment', [QueueController::class, 'showTicketPayment'])->name('ticket.payment');
    Route::post('/ticket/payment/confirm', [QueueController::class, 'confirmTicketPayment'])->name('ticket.payment.confirm');
    Route::post('/ticket/take', [QueueController::class, 'takeTicket'])->name('ticket.take');
    Route::post('/desk/{desk_id}/call', [QueueController::class, 'callNext'])->name('desk.call');
    Route::post('/ticket/{ticket_id}/status/{status}', [QueueController::class, 'updateStatus'])->name('ticket.status');
    Route::post('/ticket/{ticket_id}/transfer', [QueueController::class, 'transfer'])->name('ticket.transfer');
    Route::post('/desk/{desk_id}/configure', [QueueController::class, 'configureDesk'])->name('desk.configure');
    Route::post('/services', [QueueController::class, 'storeService'])->name('service.store');
    Route::post('/desks', [QueueController::class, 'storeDesk'])->name('desk.store');
    Route::get('/ia/recommendation/{action}/{service_id}', [QueueController::class, 'applyRecommendation'])->name('ia.apply');
    Route::post('/simulate/progress', [QueueController::class, 'simulateProgress'])->name('simulate.progress');
    Route::post('/system/reset', [QueueController::class, 'reset'])->name('system.reset');
    Route::post('/user/{user_id}/role', [QueueController::class, 'updateUserRole'])->name('user.role');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    });
});

require __DIR__.'/auth.php';
