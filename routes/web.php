<?php

use Illuminate\Support\Facades\Route;

// Import Controller berdasarkan struktur folder (Sub-namespace)
use App\Http\Controllers\LoginController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\AntrianController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\PetugasController as AdminPetugas;

/*
|--------------------------------------------------------------------------
| Public Routes (Akses Tanpa Login)
|--------------------------------------------------------------------------
*/
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Route Masyarakat untuk Ambil Antrian
Route::get('/ambil-antrian', [UserDashboardController::class, 'index'])->name('user.dashboard');
Route::post('/store-antrian', [UserDashboardController::class, 'store'])->name('user.store');

// Route Display Monitor TV (Akses Publik/TV)
Route::get('/display', [DisplayController::class, 'index'])->name('display');
Route::get('/api/display-data', [DisplayController::class, 'getDisplayData']); // API JSON untuk AJAX Display

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'index'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/api/lokets', [DisplayController::class, 'getLokets']);

/*
|--------------------------------------------------------------------------
| Dashboard Petugas (Proteksi Auth & Role Petugas)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->group(function () {
    // Halaman Utama Petugas
    Route::get('/', [AntrianController::class, 'index'])->name('petugas.dashboard');

    // Aksi Panggil Antrian Baru
    Route::post('/panggil', [AntrianController::class, 'panggil'])->name('petugas.panggil');

    // Aksi Panggil Ulang (Recall)
    Route::post('/panggil-ulang/{id}', [AntrianController::class, 'panggilUlang'])->name('petugas.panggilUlang');

    // Route Aksi (Selesai/Lewati) - Disinkronkan menjadi 'petugas.aksi' agar sesuai dengan Blade
    Route::post('/aksi/{id}/{status}', [AntrianController::class, 'aksi'])->name('petugas.aksi');
});

/*
|--------------------------------------------------------------------------
| Dashboard Admin (Proteksi Auth & Role Admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    
    // Dashboard Ringkasan Statistik
    Route::get('/', [AdminDashboard::class, 'index'])->name('admin.dashboard');

    // --- Kelola Petugas ---
    Route::get('/petugas', [AdminPetugas::class, 'index'])->name('admin.petugas');
    Route::post('/petugas/store', [AdminPetugas::class, 'store'])->name('admin.petugas.store');
    Route::post('/petugas/update/{id}', [AdminPetugas::class, 'update'])->name('admin.petugas.update');
    Route::post('/petugas/delete/{id}', [AdminPetugas::class, 'destroy'])->name('admin.petugas.delete');

    // --- Kelola Loket ---
    Route::post('/loket/update/{id}', [AdminPetugas::class, 'updateLoket'])->name('admin.loket.update');

    // --- KELOLA LAYANAN (Integrasi Baru) ---
    Route::get('/layanan', [AdminDashboard::class, 'layananIndex'])->name('admin.layanan');
    Route::post('/layanan/store', [AdminDashboard::class, 'layananStore'])->name('admin.layanan.store');
    Route::put('/layanan/update/{id}', [AdminDashboard::class, 'layananUpdate'])->name('admin.layanan.update');
    Route::delete('/layanan/delete/{id}', [AdminDashboard::class, 'layananDestroy'])->name('admin.layanan.destroy');

    // --- Kelola Data Antrian (Log/Riwayat) ---
    Route::get('/antrian', [AdminDashboard::class, 'antrianIndex'])->name('admin.antrian');
    Route::post('/antrian/update/{id}', [AdminDashboard::class, 'updateAntrian'])->name('admin.antrian.update');
    Route::delete('/antrian/delete/{id}', [AdminDashboard::class, 'deleteAntrian'])->name('admin.antrian.delete');
    
    // Reset Display & Realtime Stats
    Route::get('/reset-display', [AdminDashboard::class, 'resetDisplay'])->name('admin.reset-display');
    Route::get('/realtime-stats', [AdminDashboard::class, 'getRealtimeStats'])->name('admin.realtime-stats');

    // Recall Khusus Petugas/Admin
    Route::post('/petugas/recall/{id}', [AntrianController::class, 'panggilUlang'])->name('petugas.panggilUlang');

    // --- Fitur Export ---
    Route::get('/export-masyarakat', [AdminDashboard::class, 'exportCSV'])->name('admin.export');
});