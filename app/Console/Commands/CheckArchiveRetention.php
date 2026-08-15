<?php

namespace App\Console\Commands;

use App\Models\Arsip;
use App\Models\Peminjaman;
use App\Models\AuditLog;
use Illuminate\Console\Command;

class CheckArchiveRetention extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-arsip-retention';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa masa retensi arsip (JRA). Jika melewati batas, ubah status menjadi Inaktif dan batalkan izin peminjaman aktif.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengecekan Jadwal Retensi Arsip (JRA)...');

        $expiredArsips = Arsip::where('retention_date', '<', now()->toDateString())
            ->where('status', 'Aktif')
            ->get();

        $count = $expiredArsips->count();

        if ($count === 0) {
            $this->info('Tidak ada arsip yang melewati masa retensi saat ini.');
            return 0;
        }

        foreach ($expiredArsips as $arsip) {
            // Update Arsip Status
            $arsip->update(['status' => 'Inaktif']);

            // Revoke active borrowings for this archive
            Peminjaman::where('arsip_id', $arsip->id)
                ->where('status_approval', 'Approved')
                ->update(['status_approval' => 'Expired']);

            // Create Audit Log
            AuditLog::create([
                'user_id' => null, // null represents system/automated task
                'action' => 'System Expired',
                'arsip_id' => $arsip->id,
                'ip_address' => '127.0.0.1',
                'details' => "Masa retensi arsip habis. Sistem mengubah status arsip '{$arsip->judul}' (Nomor: {$arsip->nomor_arsip}) menjadi Inaktif.",
            ]);

            $this->line("- Arsip ID {$arsip->id} [{$arsip->nomor_arsip}] - '{$arsip->judul}' telah diubah ke status INAKTIF.");
        }

        $this->info("Pengecekan JRA selesai. Total {$count} dokumen berhasil diubah statusnya menjadi Inaktif.");
        return 0;
    }
}
