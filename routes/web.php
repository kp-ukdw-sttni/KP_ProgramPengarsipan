<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ArsipController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\AuditLogController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard (guest is handled by auth middleware, which redirects to login)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authenticated & Verified Routes
Route::middleware(['auth'])->group(function () {
    
    // Dashboard Route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Routes (Breeze default)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Archive (Arsip) Management
    Route::get('/arsip', [ArsipController::class, 'index'])->name('arsip.index');
    Route::get('/arsip/create', [ArsipController::class, 'create'])->name('arsip.create')->middleware('role:Superadmin|Operator');
    Route::post('/arsip', [ArsipController::class, 'store'])->name('arsip.store')->middleware('role:Superadmin|Operator');
    Route::get('/arsip/{arsip}/edit', [ArsipController::class, 'edit'])->name('arsip.edit')->middleware('role:Superadmin|Operator');
    Route::put('/arsip/{arsip}', [ArsipController::class, 'update'])->name('arsip.update')->middleware('role:Superadmin|Operator');
    Route::delete('/arsip/{arsip}', [ArsipController::class, 'destroy'])->name('arsip.destroy')->middleware('role:Superadmin|Operator');

    // Secure Streaming and View routes
    Route::get('/arsip/{arsip}/stream', [ArsipController::class, 'streamFile'])->name('arsip.stream');
    Route::get('/arsip/{arsip}/download', [ArsipController::class, 'downloadFile'])->name('arsip.download');
    Route::get('/arsip/{arsip}/view', [ArsipController::class, 'viewDocument'])->name('arsip.view');

    // Borrowing (Peminjaman) Management
    Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
    Route::post('/peminjaman/{arsip}/request', [PeminjamanController::class, 'requestAccess'])->name('peminjaman.request');
    
    // Approval routes (Operator & Superadmin only)
    Route::get('/peminjaman/manage', [PeminjamanController::class, 'manage'])->name('peminjaman.manage')->middleware('role:Superadmin|Operator');
    Route::post('/peminjaman/{peminjaman}/approve', [PeminjamanController::class, 'approve'])->name('peminjaman.approve')->middleware('role:Superadmin|Operator');
    Route::post('/peminjaman/{peminjaman}/reject', [PeminjamanController::class, 'reject'])->name('peminjaman.reject')->middleware('role:Superadmin|Operator');

    // Audit Logs (Superadmin only)
    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit.index')->middleware('role:Superadmin');
});

require __DIR__.'/auth.php';
