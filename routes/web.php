<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExtracurricularController;
use App\Http\Controllers\ExtracurricularScoreController;
use App\Http\Controllers\FormativeScoreController;
use App\Http\Controllers\LearningObjectiveController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SummativeScoreController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Aplikasi (semua user login)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    // Data siswa (termasuk import Excel)
    Route::middleware('role:super_admin,kepala_sekolah,guru,wali_kelas')->group(function () {
        Route::get('students/import', [StudentController::class, 'importForm'])->name('students.import');
        Route::post('students/import', [StudentController::class, 'import'])->name('students.import.store');
        Route::resource('students', StudentController::class)->except(['show']);
    });

    // Mata pelajaran & KKM
    Route::middleware('role:super_admin,kepala_sekolah,guru')->group(function () {
        Route::resource('subjects', SubjectController::class);
        Route::resource('learning-objectives', LearningObjectiveController::class);
    });

    // Input nilai formatif & sumatif
    Route::middleware('role:super_admin,kepala_sekolah,guru,wali_kelas')->group(function () {
        Route::get('scores/formative', [FormativeScoreController::class, 'index'])->name('scores.formative');
        Route::post('scores/formative', [FormativeScoreController::class, 'store'])->name('scores.formative.store');
        Route::get('scores/summative', [SummativeScoreController::class, 'index'])->name('scores.summative');
        Route::post('scores/summative', [SummativeScoreController::class, 'store'])->name('scores.summative.store');
        Route::get('scores/attendance', [AttendanceController::class, 'index'])->name('scores.attendance');
        Route::post('scores/attendance', [AttendanceController::class, 'store'])->name('scores.attendance.store');
    });

    // Ekstrakurikuler
    Route::middleware('role:super_admin,kepala_sekolah,guru,wali_kelas')->group(function () {
        Route::get('extracurriculars', [ExtracurricularController::class, 'index'])->name('extracurriculars.index');
        Route::post('extracurriculars', [ExtracurricularController::class, 'store'])->name('extracurriculars.store');
        Route::put('extracurriculars/{extracurricular}', [ExtracurricularController::class, 'update'])->name('extracurriculars.update');
        Route::delete('extracurriculars/{extracurricular}', [ExtracurricularController::class, 'destroy'])->name('extracurriculars.destroy');
        Route::get('scores/extracurricular', [ExtracurricularScoreController::class, 'index'])->name('scores.extracurricular');
        Route::post('scores/extracurricular', [ExtracurricularScoreController::class, 'store'])->name('scores.extracurricular.store');
    });

    // Cetak rapor & rekap (termasuk siswa/orang tua untuk rapor miliknya)
    Route::middleware('role:super_admin,kepala_sekolah,guru,wali_kelas,siswa_orangtua')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/recap', [ReportController::class, 'recap'])->name('reports.recap');
        Route::get('reports/show', [ReportController::class, 'show'])->name('reports.show');
        Route::get('reports/print', [ReportController::class, 'printPdf'])->name('reports.print');
    });

    // Backup & Restore (Super Admin saja)
    Route::middleware('role:super_admin')->prefix('backup')->name('backup.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::post('/download', [BackupController::class, 'download'])->name('download');
        Route::post('/restore', [BackupController::class, 'restore'])->name('restore');
        Route::delete('/{filename}', [BackupController::class, 'destroy'])->name('destroy');
    });
});
