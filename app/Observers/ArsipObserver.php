<?php

namespace App\Observers;

use App\Models\Arsip;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class ArsipObserver
{
    /**
     * Handle the Arsip "created" event.
     */
    public function created(Arsip $arsip): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Create',
            'arsip_id' => $arsip->id,
            'ip_address' => request()->ip(),
            'details' => "Arsip baru berhasil ditambahkan: '{$arsip->judul}' (Nomor: {$arsip->nomor_arsip}).",
        ]);
    }

    /**
     * Handle the Arsip "updated" event.
     */
    public function updated(Arsip $arsip): void
    {
        $dirty = $arsip->getDirty();
        // Don't log if only updated_at changed
        if (count($dirty) === 1 && isset($dirty['updated_at'])) {
            return;
        }

        $changes = [];
        foreach ($dirty as $key => $value) {
            if ($key !== 'updated_at') {
                $original = $arsip->getOriginal($key);
                $changes[] = "kolom '{$key}' diubah dari '{$original}' menjadi '{$value}'";
            }
        }
        $changesStr = implode(', ', $changes);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Update',
            'arsip_id' => $arsip->id,
            'ip_address' => request()->ip(),
            'details' => "Arsip diupdate: '{$arsip->judul}' (Nomor: {$arsip->nomor_arsip}). Perubahan: {$changesStr}.",
        ]);
    }

    /**
     * Handle the Arsip "deleted" event.
     */
    public function deleted(Arsip $arsip): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Delete',
            'arsip_id' => null, // Set to null since record is deleted
            'ip_address' => request()->ip(),
            'details' => "Arsip dihapus permanen: '{$arsip->judul}' (Nomor: {$arsip->nomor_arsip}, ID: {$arsip->id}).",
        ]);
    }
}
