<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ArsipController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authenticated Routes
Route::middleware(['auth', 'check.status'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ============================================================
    // MANAJEMEN DOKUMEN (ARSIP)
    // ============================================================
    Route::get('/arsip', [ArsipController::class, 'index'])->name('arsip.index');
    Route::get('/arsip/explorer', [ArsipController::class, 'explorer'])->name('arsip.explorer');
    Route::get('/arsip/{arsip}/stream', [ArsipController::class, 'streamFile'])->name('arsip.stream');
    Route::get('/arsip/{arsip}/download', [ArsipController::class, 'downloadFile'])->name('arsip.download');
    Route::get('/arsip/{arsip}/view', [ArsipController::class, 'viewDocument'])->name('arsip.view');

    Route::middleware(['role:Superadmin|Operator|Staf TU'])->group(function () {
        Route::get('/arsip/meta', [ArsipController::class, 'createMeta'])->name('arsip.meta');
        Route::get('/arsip/create', [ArsipController::class, 'create'])->name('arsip.create');
        Route::post('/arsip', [ArsipController::class, 'store'])->name('arsip.store');
        Route::get('/arsip/{arsip}/edit', [ArsipController::class, 'edit'])->name('arsip.edit');
        Route::put('/arsip/{arsip}', [ArsipController::class, 'update'])->name('arsip.update');
        Route::delete('/arsip/{arsip}', [ArsipController::class, 'destroy'])->name('arsip.destroy');
    });

    // ============================================================
    // RECYCLE BIN / TRASH
    // ============================================================
    Route::middleware(['role:Superadmin|Operator|Staf TU'])->group(function () {
        Route::get('/trash', [ArsipController::class, 'trashIndex'])->name('arsip.trash');
        Route::post('/trash/{id}/restore', [ArsipController::class, 'restore'])->name('arsip.restore');
        Route::delete('/trash/{id}/force-delete', [ArsipController::class, 'forceDelete'])->name('arsip.force_delete');
    });

    // ============================================================
    // KATEGORI & KLASIFIKASI DOKUMEN
    // ============================================================
    Route::middleware(['role:Superadmin|Operator|Staf TU'])->group(function () {
        Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
        Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
        Route::get('/kategori/{kategori}/edit', [KategoriController::class, 'edit'])->name('kategori.edit');
        Route::put('/kategori/{kategori}', [KategoriController::class, 'update'])->name('kategori.update');
        Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy'])->name('kategori.destroy');
    });

    // ============================================================
    // PENGAJUAN & PERSETUJUAN DOKUMEN (APPROVAL WORKFLOW)
    // ============================================================
    Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
    Route::post('/peminjaman/{arsip}/request', [PeminjamanController::class, 'requestAccess'])->name('peminjaman.request');
    Route::delete('/peminjaman/{peminjaman}/cancel', [PeminjamanController::class, 'cancelRequest'])->name('peminjaman.cancel');

    Route::middleware(['role:Superadmin|Operator|Staf TU'])->group(function () {
        Route::get('/peminjaman/manage', [PeminjamanController::class, 'manage'])->name('peminjaman.manage');
        Route::post('/peminjaman/{peminjaman}/approve', [PeminjamanController::class, 'approve'])->name('peminjaman.approve');
        Route::post('/peminjaman/{peminjaman}/reject', [PeminjamanController::class, 'reject'])->name('peminjaman.reject');
    });

    // ============================================================
    // MANAJEMEN PENGGUNA & HAK AKSES
    // ============================================================
    Route::middleware(['role:Superadmin'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/import-csv', [UserController::class, 'importCsv'])->name('users.import_csv');
    });

    // ============================================================
    // AUDIT LOG & TRASH
    // ============================================================
    Route::middleware(['role:Superadmin|Operator|Staf TU'])->group(function () {
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit.index');
    });
});

require __DIR__.'/auth.php';
