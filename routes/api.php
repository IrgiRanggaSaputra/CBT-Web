<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\PesertaController;
use App\Http\Middleware\EnsureTestSession;
use App\Http\Middleware\PreventCheating;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('admin')->controller(AdminController::class)->group(function () {
        Route::get('/stats', 'stats');
        Route::get('/monitoring', 'monitoring');

        Route::get('/participants', 'participants');
        Route::post('/participants', 'storeParticipant');
        Route::post('/participants/import', 'importParticipants');
        Route::put('/participants/{peserta}', 'updateParticipant');
        Route::delete('/participants/{peserta}', 'destroyParticipant');

        Route::get('/categories', 'categories');
        Route::post('/categories', 'storeCategory');
        Route::put('/categories/{kategori}', 'updateCategory');
        Route::delete('/categories/{kategori}', 'destroyCategory');

        Route::get('/questions', 'questions');
        Route::post('/questions', 'storeQuestion');
        Route::put('/questions/{soal}', 'updateQuestion');
        Route::delete('/questions/{soal}', 'destroyQuestion');
        Route::post('/questions/import', 'importQuestions');

        Route::get('/schedules', 'schedules');
        Route::post('/schedules', 'storeSchedule');
        Route::put('/schedules/{jadwal}', 'updateSchedule');
        Route::delete('/schedules/{jadwal}', 'destroySchedule');
        Route::get('/schedules/{jadwal}/participants', 'scheduleParticipants');
        Route::post('/schedules/{jadwal}/participants', 'storeScheduleParticipant');
        Route::post('/schedules/{jadwal}/participants/all', 'storeAllScheduleParticipants');
        Route::delete('/schedules/{jadwal}/participants/{peserta}', 'destroyScheduleParticipant');

        Route::get('/reports', 'reports');
        Route::get('/reports/{test}/export-csv', 'exportParticipantCsv');
    });

    Route::prefix('peserta')->controller(PesertaController::class)->group(function () {
        Route::get('/dashboard', 'dashboard');
        Route::get('/tests', 'myTests');
        Route::get('/history', 'history');

        // Exam session routes with security middleware
        Route::middleware([EnsureTestSession::class])->group(function () {
            Route::post('/tests/{test}/start', 'startTest');
            Route::get('/tests/{test}/questions', 'getQuestions');

            // Answer submission with anti-cheating rate-limiting
            Route::post('/tests/{test}/answer', 'submitAnswer')
                ->middleware(PreventCheating::class);

            Route::post('/tests/{test}/finish', 'finishTest');
        });
    });
});
