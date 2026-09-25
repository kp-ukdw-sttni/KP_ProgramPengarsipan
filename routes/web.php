<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ArsipController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UkmController;
use App\Http\Controllers\PresensiUkmController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authenticated Routes (Pure Internal Campus: Admin & Dosen)
Route::middleware(['auth', 'check.status'])->group(function () {

    // 1. Dashboard Operasional
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Profil Pengguna
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ============================================================
    // DAFTAR BERKAS (Pengarsipan Dokumen Digital)
    // ============================================================
    Route::get('/arsip', [ArsipController::class, 'index'])->name('arsip.index');
    Route::get('/arsip/meta', [ArsipController::class, 'createMeta'])->name('arsip.meta');
    Route::get('/arsip/create', [ArsipController::class, 'create'])->name('arsip.create');
    Route::post('/arsip', [ArsipController::class, 'store'])->name('arsip.store');
    Route::get('/arsip/{arsip}/stream', [ArsipController::class, 'streamFile'])->name('arsip.stream');
    Route::get('/arsip/{arsip}/download', [ArsipController::class, 'downloadFile'])->name('arsip.download');
    Route::get('/arsip/{arsip}/edit', [ArsipController::class, 'edit'])->name('arsip.edit');
    Route::put('/arsip/{arsip}', [ArsipController::class, 'update'])->name('arsip.update');
    Route::delete('/arsip/{arsip}', [ArsipController::class, 'destroy'])->name('arsip.destroy');

    // Recycle Bin / Trash (Admin)
    Route::middleware(['role:Admin'])->group(function () {
        Route::get('/trash', [ArsipController::class, 'trashIndex'])->name('arsip.trash');
        Route::post('/trash/{id}/restore', [ArsipController::class, 'restore'])->name('arsip.restore');
        Route::delete('/trash/{id}/force-delete', [ArsipController::class, 'forceDelete'])->name('arsip.force_delete');
    });

    // ============================================================
    // JENIS DOKUMEN (Kategori & Klasifikasi Dokumen)
    // ============================================================
    Route::middleware(['role:Admin'])->group(function () {
        Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
        Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
        Route::get('/kategori/{kategori}/edit', [KategoriController::class, 'edit'])->name('kategori.edit');
        Route::put('/kategori/{kategori}', [KategoriController::class, 'update'])->name('kategori.update');
        Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy'])->name('kategori.destroy');
    });

    // ============================================================
    // PERSETUJUAN AKSES DOKUMEN (Access Approval Workflow)
    // ============================================================
    Route::post('/akses-dokumen/{arsip}/request', [PeminjamanController::class, 'requestAccess'])->name('peminjaman.request');
    Route::delete('/akses-dokumen/{peminjaman}/cancel', [PeminjamanController::class, 'cancelRequest'])->name('peminjaman.cancel');

    Route::middleware(['role:Admin'])->group(function () {
        Route::get('/akses-dokumen', [PeminjamanController::class, 'manage'])->name('peminjaman.manage');
        Route::post('/akses-dokumen/{peminjaman}/approve', [PeminjamanController::class, 'approve'])->name('peminjaman.approve');
        Route::post('/akses-dokumen/{peminjaman}/reject', [PeminjamanController::class, 'reject'])->name('peminjaman.reject');
    });

    // ============================================================
    // KELOLA AKUN (Manajemen Pengguna Dosen & Staf Internal)
    // ============================================================
    Route::middleware(['role:Admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // ============================================================
    // PRESENSI UKM (UKM Attendance Workflow)
    // ============================================================
    Route::get('/ukm', [UkmController::class, 'index'])->name('ukm.index');
    Route::get('/ukm/create', [UkmController::class, 'create'])->name('ukm.create');
    Route::post('/ukm', [UkmController::class, 'store'])->name('ukm.store');
    Route::get('/ukm/{ukm}/edit', [UkmController::class, 'edit'])->name('ukm.edit');
    Route::put('/ukm/{ukm}', [UkmController::class, 'update'])->name('ukm.update');
    Route::delete('/ukm/{ukm}', [UkmController::class, 'destroy'])->name('ukm.destroy');
    Route::post('/ukm/{ukm}/anggota', [UkmController::class, 'storeAnggota'])->name('ukm.anggota.store');
    Route::put('/ukm/{ukm}/anggota/{anggota}', [UkmController::class, 'updateAnggota'])->name('ukm.anggota.update');
    Route::delete('/ukm/{ukm}/anggota/{anggota}', [UkmController::class, 'destroyAnggota'])->name('ukm.anggota.destroy');

    Route::get('/presensi', [PresensiUkmController::class, 'index'])->name('presensi.index');
    Route::get('/presensi/create', [PresensiUkmController::class, 'create'])->name('presensi.create');
    Route::post('/presensi', [PresensiUkmController::class, 'store'])->name('presensi.store');
    Route::get('/presensi/review', [PresensiUkmController::class, 'review'])->name('presensi.review');
    Route::get('/presensi/anggota/{ukm}', [PresensiUkmController::class, 'anggotaByUkm'])->name('presensi.anggota');
    Route::get('/presensi/{presensi}', [PresensiUkmController::class, 'show'])->name('presensi.show');
    Route::get('/presensi/{presensi}/pdf', [PresensiUkmController::class, 'downloadPdf'])->name('presensi.pdf');
    Route::get('/presensi/{presensi}/excel', [PresensiUkmController::class, 'downloadExcel'])->name('presensi.excel');

    // Finalisasi & Verifikasi Presensi
    Route::middleware(['role:Admin'])->group(function () {
        Route::post('/presensi/{presensi}/approve', [PresensiUkmController::class, 'approve'])->name('presensi.approve');
        Route::post('/presensi/{presensi}/reject', [PresensiUkmController::class, 'reject'])->name('presensi.reject');
    });

    // ============================================================
    // RIWAYAT AKTIVITAS (Audit Log)
    // ============================================================
    Route::middleware(['role:Admin'])->group(function () {
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit.index');
    });
});

require __DIR__.'/auth.php';
