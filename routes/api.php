<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\CashbackController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\AdminWithdrawalController;
use App\Http\Controllers\AdminReferralController;
use App\Http\Middleware\AdminMiddleware;

// Auth — throttle: 10 tentativas por minuto por IP
Route::prefix('auth')->middleware(['throttle:10,1'])->group(function () {
    Route::post('/register',        [AuthController::class, 'register']);
    Route::post('/login',           [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password',  [AuthController::class, 'resetPassword']);

});

Route::middleware('auth:api')->group(function () {
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    // Usuário autenticado
    Route::get('/users/me',  [UserController::class, 'me']);
    Route::put('/users/me',  [UserController::class, 'update']);

    // Indicações do afiliado
    Route::prefix('referrals')->group(function () {
        Route::post('/',       [ReferralController::class, 'store']);
        Route::get('/',        [ReferralController::class, 'index']);
        Route::get('/stats',   [ReferralController::class, 'stats']);
    });

    // Cashback
    Route::prefix('cashback')->group(function () {
        Route::get('/balance', [CashbackController::class, 'balance']);
        Route::get('/history', [CashbackController::class, 'history']);
    });

    // Saques
    Route::prefix('withdrawals')->group(function () {
        Route::post('/request', [WithdrawalController::class, 'requestWithdrawal']);
        Route::get('/history',  [WithdrawalController::class, 'history']);
        Route::get('/stats',    [WithdrawalController::class, 'stats']);
    });

    // Área Admin
    Route::middleware(AdminMiddleware::class)->prefix('admin')->group(function () {
        // Indicações (admin)
        Route::get('/referrals',                      [AdminReferralController::class, 'index']);
        Route::get('/referrals/stats',                [AdminReferralController::class, 'stats']);
        Route::patch('/referrals/{id}/status',        [AdminReferralController::class, 'updateStatus']);
        Route::post('/referrals/{id}/reject',         [AdminReferralController::class, 'reject']);

        // Saques (admin)
        Route::get('/withdrawals',                    [AdminWithdrawalController::class, 'index']);
        Route::get('/withdrawals/stats',              [AdminWithdrawalController::class, 'stats']);
        Route::post('/withdrawals/{id}/approve',      [AdminWithdrawalController::class, 'approve']);
        Route::post('/withdrawals/{id}/reject',       [AdminWithdrawalController::class, 'reject']);
        Route::post('/withdrawals/{id}/mark-paid',    [AdminWithdrawalController::class, 'markPaid']);

        // Usuários (admin)
        Route::get('/users/{id}',                     [AdminWithdrawalController::class, 'showUser']);
    });
});
