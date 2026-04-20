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

// API Public untuk Status Sistem (Mencegah Error RouteNotFound)
Route::get('/api/system-status', [AdminDashboard::class, 'getSystemStatusApi'])->name('api.system-status.public');

/*
|--------------------------------------------------------------------------
| Route Display Monitor TV (Akses Publik/TV)
|--------------------------------------------------------------------------
*/
Route::get('/display', [DisplayController::class, 'index'])->name('display');
Route::get('/api/display-data', [DisplayController::class, 'getDisplayData'])->name('api.display-data.public');


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
    // Halaman Utama Petugas (Mendukung AJAX Realtime)
    Route::get('/', [AntrianController::class, 'index'])->name('petugas.dashboard');

    // Aksi Panggil Antrian Baru
    Route::post('/panggil', [AntrianController::class, 'panggil'])->name('petugas.panggil');

    // Aksi Panggil Ulang (Recall)
    Route::post('/panggil-ulang/{id}', [AntrianController::class, 'panggilUlang'])->name('petugas.panggilUlang');

    // Route Aksi (Selesai/Lewati)
    // Menggunakan parameter {status} untuk membedakan antara 'selesai' dan 'lewat'
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

    // --- Fitur Operasional (Buka/Tutup Sistem) ---
    Route::post('/system/toggle', [AdminDashboard::class, 'toggleSystem'])->name('admin.toggle-status');
    Route::get('/api/system-status-admin', [AdminDashboard::class, 'getSystemStatusApi'])->name('api.system-status');

    // --- Kelola Petugas ---
    Route::get('/petugas', [AdminPetugas::class, 'index'])->name('admin.petugas');
    Route::post('/petugas/store', [AdminPetugas::class, 'store'])->name('admin.petugas.store');
    Route::post('/petugas/update/{id}', [AdminPetugas::class, 'update'])->name('admin.petugas.update');
    Route::post('/petugas/delete/{id}', [AdminPetugas::class, 'destroy'])->name('admin.petugas.delete');

    // --- Kelola Loket ---
    Route::post('/loket/update/{id}', [AdminPetugas::class, 'updateLoket'])->name('admin.loket.update');

    // --- KELOLA LAYANAN ---
    Route::get('/layanan', [AdminDashboard::class, 'layananIndex'])->name('admin.layanan');
    Route::post('/layanan/store', [AdminDashboard::class, 'layananStore'])->name('admin.layanan.store');
    Route::put('/layanan/update/{id}', [AdminDashboard::class, 'layananUpdate'])->name('admin.layanan.update');
    Route::post('/layanan/toggle/{id}', [\App\Http\Controllers\Admin\DashboardController::class, 'layananToggle'])->name('admin.layanan.toggle');
    Route::delete('/layanan/delete/{id}', [AdminDashboard::class, 'layananDestroy'])->name('admin.layanan.destroy');

    // --- Kelola Data Antrian (Log/Riwayat) ---
    Route::get('/antrian', [AdminDashboard::class, 'antrianIndex'])->name('admin.antrian');
    Route::post('/antrian/update/{id}', [AdminDashboard::class, 'updateAntrian'])->name('admin.antrian.update');
    Route::delete('/antrian/delete/{id}', [AdminDashboard::class, 'deleteAntrian'])->name('admin.antrian.delete');

    // Reset Display & Realtime Stats
    Route::get('/reset-display', [AdminDashboard::class, 'resetDisplay'])->name('admin.reset-display');
    Route::get('/realtime-stats', [AdminDashboard::class, 'getRealtimeStats'])->name('admin.realtime-stats');

    // Recall Khusus Petugas/Admin (Back-end Support)
    Route::post('/petugas/recall/{id}', [AntrianController::class, 'panggilUlang'])->name('admin.petugas.recall');

    // --- Fitur Export ---
    Route::get('/export-masyarakat', [AdminDashboard::class, 'exportCSV'])->name('admin.export');
});
