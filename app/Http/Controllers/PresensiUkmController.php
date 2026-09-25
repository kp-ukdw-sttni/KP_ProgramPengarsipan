<?php

namespace App\Http\Controllers;

use App\Models\PresensiUkm;
use App\Models\Ukm;
use App\Services\PresensiUkmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PresensiUkmController extends Controller
{
    public function __construct(private PresensiUkmService $presensiService) {}

    /**
     * List presensi sessions.
     */
    public function index(Request $request)
    {
        return Inertia::render('Presensi/Index', $this->presensiService->getIndexData(Auth::user(), $request));
    }

    /**
     * Show the field attendance form.
     */
    public function create()
    {
        return Inertia::render('Presensi/Create', $this->presensiService->getCreateData());
    }

    /**
     * Store attendance and generate the archive rekap.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ukm_id' => ['required', 'exists:ukm,id'],
            'judul_kegiatan' => ['nullable', 'string', 'max:255'],
            'tanggal_kegiatan' => ['required', 'date'],
            'pertemuan_ke' => ['nullable', 'integer', 'min:1'],
            'catatan_pengisi' => ['nullable', 'string'],
            'anggota' => ['required', 'array', 'min:1'],
            'anggota.*.anggota_id' => ['required', 'exists:ukm_anggota,id'],
            'anggota.*.status_kehadiran' => ['required', Rule::in(['Hadir', 'Izin', 'Sakit', 'Alpha'])],
            'anggota.*.keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $presensi = $this->presensiService->store($request, Auth::user());

        return redirect()->route('presensi.show', $presensi)
            ->with('success', 'Presensi berhasil disimpan. Rekap PDF & Excel telah dibuat dan arsip berstatus Menunggu Verifikasi.');
    }

    /**
     * Show a single session rekap.
     */
    public function show(PresensiUkm $presensi)
    {
        abort_unless($this->presensiService->canAccess($presensi, Auth::user()), 403, 'Anda tidak diizinkan melihat presensi ini.');

        return Inertia::render('Presensi/Show', $this->presensiService->getShowData($presensi));
    }

    /**
     * JSON list of active members for the chosen UKM.
     */
    public function anggotaByUkm(Ukm $ukm)
    {
        return response()->json($this->presensiService->anggotaByUkm($ukm));
    }

    /**
     * Review queue (Admin Pengarsipan / Superadmin).
     */
    public function review()
    {
        abort_unless(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Superadmin'), 403, 'Hanya Admin Pengarsipan yang diizinkan.');

        return Inertia::render('Presensi/Review', $this->presensiService->getReviewData());
    }

    /**
     * Approve the archive generated from a session.
     */
    public function approve(Request $request, PresensiUkm $presensi)
    {
        abort_unless(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Superadmin'), 403, 'Hanya Admin Pengarsipan yang diizinkan.');

        $request->validate([
            'catatan_reviewer' => ['nullable', 'string', 'max:255'],
        ]);

        $this->presensiService->approve($request, $presensi, Auth::user());

        return back()->with('success', 'Arsip presensi disetujui & ditandai Terverifikasi.');
    }

    /**
     * Reject the archive generated from a session.
     */
    public function reject(Request $request, PresensiUkm $presensi)
    {
        abort_unless(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Superadmin'), 403, 'Hanya Admin Pengarsipan yang diizinkan.');

        $request->validate([
            'catatan_reviewer' => ['required', 'string', 'max:255'],
        ]);

        $this->presensiService->reject($request, $presensi, Auth::user());

        return back()->with('success', 'Arsip presensi ditolak.');
    }

    /**
     * Download the generated PDF rekap.
     */
    public function downloadPdf(PresensiUkm $presensi)
    {
        abort_unless($this->presensiService->canAccess($presensi, Auth::user()), 403, 'Anda tidak diizinkan mengunduh presensi ini.');

        return $this->presensiService->downloadPdf($presensi);
    }

    /**
     * Download the generated Excel rekap.
     */
    public function downloadExcel(PresensiUkm $presensi)
    {
        abort_unless($this->presensiService->canAccess($presensi, Auth::user()), 403, 'Anda tidak diizinkan mengunduh presensi ini.');

        return $this->presensiService->downloadExcel($presensi);
    }
}
