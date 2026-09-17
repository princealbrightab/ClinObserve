<?php

use App\Http\Controllers\AiReviewController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EncounterController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:login')->name('login.store');
    Route::get('/forgot-password', [AuthController::class, 'forgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'emailReset'])->middleware('throttle:recovery')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'storeReset'])->middleware('throttle:recovery')->name('password.store');
});
Route::middleware(['auth', 'ready'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/password', [AuthController::class, 'password'])->name('password.edit');
    Route::put('/password', [AuthController::class, 'updatePassword'])->name('password.update');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/students/{student}/avatar', [ProfileController::class, 'avatar'])->name('students.avatar');
    Route::resource('patients', PatientController::class)->except('destroy');
    Route::get('/encounters', [EncounterController::class, 'index'])->name('encounters.index');
    Route::get('/patients/{patient}/encounters/create', [EncounterController::class, 'create'])->name('encounters.create');
    Route::post('/patients/{patient}/encounters', [EncounterController::class, 'store'])->name('encounters.store');
    Route::get('/encounters/{encounter}', [EncounterController::class, 'show'])->name('encounters.show');
    Route::get('/encounters/{encounter}/edit', [EncounterController::class, 'edit'])->name('encounters.edit');
    Route::patch('/encounters/{encounter}', [EncounterController::class, 'update'])->name('encounters.update');
    Route::post('/encounters/{encounter}/images', [ImageController::class, 'store'])->name('encounter-images.store');
    Route::get('/encounter-images/{image}', [ImageController::class, 'show'])->name('encounter-images.show');
    Route::delete('/encounter-images/{image}', [ImageController::class, 'destroy'])->name('encounter-images.destroy');
    Route::get('/calendar', CalendarController::class)->name('calendar.index');
    Route::get('/ai-reviews', [AiReviewController::class, 'index'])->name('ai-reviews.index');
    Route::get('/encounters/{encounter}/ai-review', [AiReviewController::class, 'preview'])->name('ai-reviews.preview');
    Route::post('/encounters/{encounter}/ai-reviews', [AiReviewController::class, 'store'])->middleware('throttle:ai')->name('ai-reviews.store');
    Route::get('/ai-reviews/{aiReview}', [AiReviewController::class, 'show'])->name('ai-reviews.show');
    Route::get('/hod-reviews/{hodReview}', [ReviewController::class, 'show'])->name('hod-reviews.show');
    Route::patch('/hod-reviews/{hodReview}', [ReviewController::class, 'update'])->name('hod-reviews.update');
    Route::prefix('professor')->name('professor.')->middleware('can:professor')->group(function (): void {
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
        Route::post('/encounters/{encounter}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    });
    Route::prefix('hod')->name('hod.')->middleware('can:hod')->group(function (): void {
        Route::resource('professors', ProfessorController::class)->except('destroy');
        Route::patch('/professors/{professor}/status', [ProfessorController::class, 'status'])->name('professors.status.update');
        Route::put('/professors/{professor}/credentials', [ProfessorController::class, 'credentials'])->name('professors.credentials.update');
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::resource('students', StudentController::class)->except('destroy')->parameters(['students' => 'student']);
        Route::patch('/students/{student}/status', [StudentController::class, 'status'])->name('students.status.update');
        Route::put('/students/{student}/credentials', [StudentController::class, 'credentials'])->name('students.credentials.update');
        Route::get('/calendar', CalendarController::class)->name('calendar.index');
        Route::get('/reports', ReportController::class)->name('reports.index');
        Route::get('/ai-reviews', [AiReviewController::class, 'index'])->name('ai-reviews.index');
        Route::post('/encounters/{encounter}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    });
});
