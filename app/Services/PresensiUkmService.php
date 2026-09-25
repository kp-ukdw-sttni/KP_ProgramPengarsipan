<?php

namespace App\Services;

use App\Models\Arsip;
use App\Models\AuditLog;
use App\Models\Divisi;
use App\Models\KategoriArsip;
use App\Models\PresensiUkm;
use App\Models\Ukm;
use App\Models\UkmAnggota;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PresensiUkmService
{
    /**
     * List presensi sessions with their status.
     */
    public function getIndexData(User $user, ?Request $request = null): array
    {
        $query = PresensiUkm::with(['ukm', 'pengisi', 'arsip'])
            ->withCount('details')
            ->latest();

        if ($request && $request->filled('ukm_id')) {
            $query->where('ukm_id', $request->ukm_id);
        }

        if ($user->hasRole('Sie Kesiswaan')) {
            $query->where('pengisi_id', $user->id);
        }

        return [
            'presensi' => $query->paginate(10)->withQueryString(),
            'ukmList' => Ukm::orderBy('name')->get(['id', 'name']),
            'selectedUkmId' => $request?->ukm_id ?? '',
        ];
    }

    /**
     * Data for the attendance form (UKM + active members).
     */
    public function getCreateData(): array
    {
        return [
            'ukmList' => Ukm::where('status', 'Aktif')
                ->with(['anggota' => fn ($q) => $q->where('status_keanggotaan', 'Aktif')->orderBy('nama')])
                ->orderBy('name')
                ->get(),
        ];
    }

    /**
     * Active members of a UKM (for the JSON anggota endpoint).
     */
    public function anggotaByUkm(Ukm $ukm): array
    {
        return $ukm->anggota()
            ->where('status_keanggotaan', 'Aktif')
            ->orderBy('nama')
            ->get(['id', 'nama', 'nim', 'jabatan'])
            ->toArray();
    }

    /**
     * Persist the attendance session, generate rekap PDF + Excel, then create the archive record.
     */
    public function store(Request $request, User $user): PresensiUkm
    {
        $ukm = Ukm::with('divisi')->findOrFail($request->ukm_id);
        $tanggal = $request->tanggal_kegiatan;
        $tahun = (int) date('Y', strtotime($tanggal));

        $divisiId = $ukm->divisi_id ?? $user->divisi_id;
        if (! $divisiId) {
            $divisiId = Divisi::where('kode', 'BAAK')->value('id');
        }

        $presensi = PresensiUkm::create([
            'ukm_id' => $ukm->id,
            'judul_kegiatan' => $request->judul_kegiatan,
            'tanggal_kegiatan' => $tanggal,
            'pertemuan_ke' => $request->pertemuan_ke,
            'pengisi_id' => $user->id,
            'status_arsip' => 'Terverifikasi',
            'catatan_pengisi' => $request->catatan_pengisi,
        ]);

        foreach ($request->anggota as $row) {
            $anggota = UkmAnggota::findOrFail($row['anggota_id']);
            $presensi->details()->create([
                'anggota_id' => $anggota->id,
                'nama' => $anggota->nama,
                'nim' => $anggota->nim,
                'status_kehadiran' => $row['status_kehadiran'],
                'keterangan' => $row['keterangan'] ?? null,
            ]);
        }

        $presensi->load(['ukm', 'details', 'pengisi']);

        $nomorArsip = $this->generateNomorArsip($divisiId, $tahun);
        $judul = $this->buildJudul($presensi);
        $rekap = $this->buildRekap($presensi, $nomorArsip);

        $subPath = 'private/archives/'.$divisiId.'/'.$tahun.'/presensi';
        $baseName = 'Rekap-Presensi-'.Str::slug($ukm->name).'-'.date('Ymd', strtotime($tanggal)).'-'.uniqid();

        $pdfOutput = $this->generatePdf($rekap);
        $excelOutput = $this->generateExcel($rekap);

        $pdfPath = $subPath.'/'.$baseName.'.pdf';
        $excelPath = $subPath.'/'.$baseName.'.xlsx';
        Storage::disk('local')->put($pdfPath, $pdfOutput);
        Storage::disk('local')->put($excelPath, $excelOutput);

        $kategori = KategoriArsip::where('kode', 'PRESENSI')->first() ?? KategoriArsip::first();

        $arsip = Arsip::create([
            'nomor_arsip' => $nomorArsip,
            'judul' => $judul,
            'deskripsi' => $request->catatan_pengisi,
            'kategori_id' => $kategori?->id,
            'divisi_id' => $divisiId,
            'tahun' => $tahun,
            'tanggal_dokumen' => $tanggal,
            'tanggal_diterima' => now()->toDateString(),
            'pengirim' => 'Sie Kesiswaan STTNI',
            'penerima' => 'Admin Pengarsipan STTNI',
            'file_path' => $pdfPath,
            'file_path_excel' => $excelPath,
            'file_size' => strlen($pdfOutput),
            'file_mime' => 'application/pdf',
            'retention_date' => now()->addYears(5)->toDateString(),
            'status' => 'Aktif',
            'verification_status' => 'Terverifikasi',
            'status_publikasi' => 'Internal',
            'tags' => 'presensi, ukm, rekap, pertemuan-'.$presensi->pertemuan_ke,
            'uploader_id' => $user->id,
            'presensi_ukm_id' => $presensi->id,
        ]);

        $presensi->update(['arsip_id' => $arsip->id]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Submit',
            'arsip_id' => $arsip->id,
            'ip_address' => request()->ip(),
            'details' => "Mengirim presensi '{$judul}' (Nomor: {$nomorArsip}) untuk verifikasi arsip.",
        ]);

        return $presensi->fresh(['ukm', 'details', 'arsip', 'pengisi']);
    }

    /**
     * Show data for a single session (rekap preview).
     */
    public function getShowData(PresensiUkm $presensi): array
    {
        $presensi->load(['ukm', 'details', 'pengisi', 'arsip', 'reviewer']);

        return [
            'presensi' => $presensi,
            'rekap' => $this->buildRekap($presensi, $presensi->arsip?->nomor_arsip),
        ];
    }

    /**
     * Data for the review queue (Superadmin only).
     */
    public function getReviewData(): array
    {
        return [
            'presensi' => PresensiUkm::with(['ukm', 'pengisi', 'arsip'])
                ->withCount('details')
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ];
    }

    /**
     * Approve the archive generated from this attendance session.
     */
    public function approve(Request $request, PresensiUkm $presensi, User $user): void
    {
        $presensi->update([
            'status_arsip' => 'Terverifikasi',
            'catatan_reviewer' => $request->catatan_reviewer,
            'reviewer_id' => $user->id,
            'reviewed_at' => now(),
        ]);

        $presensi->arsip?->update(['verification_status' => 'Terverifikasi']);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Verifikasi',
            'arsip_id' => $presensi->arsip_id,
            'ip_address' => request()->ip(),
            'details' => "Menyetujui (verifikasi) arsip presensi '{$presensi->ukm->name}' tanggal {$presensi->tanggal_kegiatan->format('d-m-Y')}.",
        ]);
    }

    /**
     * Reject the archive generated from this attendance session.
     */
    public function reject(Request $request, PresensiUkm $presensi, User $user): void
    {
        $presensi->update([
            'status_arsip' => 'Ditolak',
            'catatan_reviewer' => $request->catatan_reviewer,
            'reviewer_id' => $user->id,
            'reviewed_at' => now(),
        ]);

        $presensi->arsip?->update(['verification_status' => 'Ditolak']);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Tolak',
            'arsip_id' => $presensi->arsip_id,
            'ip_address' => request()->ip(),
            'details' => "Menolak arsip presensi '{$presensi->ukm->name}' tanggal {$presensi->tanggal_kegiatan->format('d-m-Y')}.",
        ]);
    }

    /**
     * Download the generated PDF rekap.
     */
    public function downloadPdf(PresensiUkm $presensi)
    {
        $path = $presensi->arsip?->file_path;

        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404, 'File rekap PDF tidak ditemukan.');
        }

        return Storage::disk('local')->download($path, basename($path));
    }

    /**
     * Download the generated Excel rekap.
     */
    public function downloadExcel(PresensiUkm $presensi)
    {
        $path = $presensi->arsip?->file_path_excel;

        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404, 'File rekap Excel tidak ditemukan.');
        }

        return Storage::disk('local')->download($path, basename($path));
    }

    /**
     * Whether the user may view/download this session.
     */
    public function canAccess(PresensiUkm $presensi, User $user): bool
    {
        return $user->hasRole('Admin')
            || $user->hasRole('Superadmin')
            || $user->hasRole('Sie Kesiswaan')
            || $presensi->pengisi_id === $user->id;
    }

    /**
     * Build the archive title.
     */
    private function buildJudul(PresensiUkm $presensi): string
    {
        $pertemuan = $presensi->pertemuan_ke ? ' - Pertemuan '.$presensi->pertemuan_ke : '';

        return 'Rekap Presensi '.$presensi->ukm->name.$pertemuan.' - '.$presensi->tanggal_kegiatan->format('d-m-Y');
    }

    /**
     * Build the rekap array used by the PDF, Excel and preview.
     */
    private function buildRekap(PresensiUkm $presensi, ?string $nomorArsip): array
    {
        Carbon::setLocale('id');

        $statuses = ['Hadir', 'Izin', 'Sakit', 'Alpha'];
        $counts = array_fill_keys($statuses, 0);
        $total = 0;

        $details = $presensi->details->map(function ($detail, $i) use (&$counts, &$total) {
            $counts[$detail->status_kehadiran]++;
            $total++;

            return [
                'no' => $i + 1,
                'nim' => $detail->nim ?: '-',
                'nama' => $detail->nama,
                'status_kehadiran' => $detail->status_kehadiran,
                'keterangan' => $detail->keterangan,
            ];
        });
        $counts['total'] = $total;

        return [
            'nomor_arsip' => $nomorArsip,
            'ukm' => [
                'name' => $presensi->ukm->name,
                'kode' => $presensi->ukm->kode,
                'pembina' => $presensi->ukm->pembina,
                'ketua' => $presensi->ukm->ketua,
            ],
            'judul_kegiatan' => $presensi->judul_kegiatan,
            'tanggal_kegiatan' => $presensi->tanggal_kegiatan->translatedFormat('l, d F Y'),
            'pertemuan_ke' => $presensi->pertemuan_ke,
            'pengisi' => $presensi->pengisi?->name ?? 'Sie Kesiswaan STTNI',
            'catatan' => $presensi->catatan_pengisi,
            'details' => $details,
            'rekap' => $counts,
            'status_arsip' => $presensi->status_arsip,
        ];
    }

    /**
     * Render the PDF rekap.
     */
    private function generatePdf(array $rekap): string
    {
        return Pdf::loadView('pdf.presensi_rekap', compact('rekap'))->output();
    }

    /**
     * Generate the Excel rekap (.xlsx).
     */
    private function generateExcel(array $rekap): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Presensi');

        $sheet->setCellValue('A1', 'REKAP PRESENSI KEGIATAN UKM');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', $rekap['ukm']['name']);
        $sheet->mergeCells('A2:E2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $meta = [
            ['Tanggal Kegiatan', $rekap['tanggal_kegiatan']],
            ['Judul Kegiatan', $rekap['judul_kegiatan'] ?: '-'],
            ['Pertemuan Ke', $rekap['pertemuan_ke'] ?: '-'],
            ['Nomor Arsip', $rekap['nomor_arsip'] ?: '-'],
            ['Diisi Oleh', $rekap['pengisi']],
        ];

        $row = 4;
        foreach ($meta as [$label, $value]) {
            $sheet->setCellValue('A'.$row, $label);
            $sheet->setCellValue('B'.$row, ': '.$value);
            $sheet->mergeCells('B'.$row.':E'.$row);
            $sheet->getStyle('A'.$row)->getFont()->setBold(true);
            $row++;
        }

        $headerRow = $row + 1;
        $headers = ['No', 'NIM', 'Nama', 'Status', 'Keterangan'];
        $columns = ['A', 'B', 'C', 'D', 'E'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValue($columns[$i].$headerRow, $header);
        }
        $sheet->getStyle('A'.$headerRow.':E'.$headerRow)
            ->getFont()->setBold(true);
        $sheet->getStyle('A'.$headerRow.':E'.$headerRow)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E2F3');
        $sheet->getStyle('A'.$headerRow.':E'.$headerRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $r = $headerRow + 1;
        foreach ($rekap['details'] as $detail) {
            $sheet->setCellValue('A'.$r, $detail['no']);
            $sheet->setCellValue('B'.$r, $detail['nim']);
            $sheet->setCellValue('C'.$r, $detail['nama']);
            $sheet->setCellValue('D'.$r, $detail['status_kehadiran']);
            $sheet->setCellValue('E'.$r, $detail['keterangan']);
            $r++;
        }

        $lastRow = $r - 1;
        $sheet->getStyle('A'.$headerRow.':E'.$lastRow)
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $r += 1;
        $sheet->setCellValue('A'.$r, 'Rekapitulasi');
        $sheet->getStyle('A'.$r)->getFont()->setBold(true);
        $r++;
        foreach (['Hadir', 'Izin', 'Sakit', 'Alpha', 'Total'] as $label) {
            $sheet->setCellValue('A'.$r, $label);
            $sheet->setCellValue('B'.$r, $rekap['rekap'][$label] ?? $rekap['rekap']['total']);
            $sheet->getStyle('A'.$r)->getFont()->setBold(true);
            $r++;
        }

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');

        return ob_get_clean();
    }

    /**
     * Generate a unique auto nomor_arsip (PRS-{KODE}-{tahun}-{random}).
     */
    private function generateNomorArsip(?int $divisiId, int $tahun): string
    {
        $divisi = $divisiId ? Divisi::find($divisiId) : null;
        $kode = $divisi?->kode ?: 'PRS';

        do {
            $nomor = 'PRS-'.strtoupper($kode).'-'.$tahun.'-'.strtoupper(Str::random(6));
        } while (Arsip::where('nomor_arsip', $nomor)->exists());

        return $nomor;
    }
}
