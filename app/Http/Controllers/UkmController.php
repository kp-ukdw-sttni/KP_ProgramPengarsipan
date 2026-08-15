<?php

namespace App\Http\Controllers;

use App\Models\Ukm;
use App\Models\UkmAnggota;
use App\Services\UkmService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UkmController extends Controller
{
    public function __construct(private UkmService $ukmService) {}

    /**
     * List UKM.
     */
    public function index()
    {
        return Inertia::render('Ukm/Index', $this->ukmService->getIndexData());
    }

    /**
     * Show the create form.
     */
    public function create()
    {
        return Inertia::render('Ukm/Create', $this->ukmService->getCreateData());
    }

    /**
     * Store a new UKM.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode' => ['nullable', 'string', 'max:50', 'unique:ukm,kode'],
            'name' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'pembina' => ['nullable', 'string', 'max:255'],
            'ketua' => ['nullable', 'string', 'max:255'],
            'divisi_id' => ['nullable', 'exists:divisi,id'],
            'status' => ['required', 'in:Aktif,Nonaktif'],
        ]);

        $this->ukmService->store($request);

        return redirect()->route('ukm.index')->with('success', 'UKM berhasil ditambahkan.');
    }

    /**
     * Show the edit form.
     */
    public function edit(Ukm $ukm)
    {
        return Inertia::render('Ukm/Edit', $this->ukmService->getEditData($ukm));
    }

    /**
     * Update an existing UKM.
     */
    public function update(Request $request, Ukm $ukm)
    {
        $request->validate([
            'kode' => ['nullable', 'string', 'max:50', 'unique:ukm,kode,'.$ukm->id],
            'name' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'pembina' => ['nullable', 'string', 'max:255'],
            'ketua' => ['nullable', 'string', 'max:255'],
            'divisi_id' => ['nullable', 'exists:divisi,id'],
            'status' => ['required', 'in:Aktif,Nonaktif'],
        ]);

        $this->ukmService->update($request, $ukm);

        return redirect()->route('ukm.index')->with('success', 'UKM berhasil diperbarui.');
    }

    /**
     * Delete an UKM (cascade removes members & attendance).
     */
    public function destroy(Ukm $ukm)
    {
        $this->ukmService->destroy($ukm);

        return redirect()->route('ukm.index')->with('success', 'UKM berhasil dihapus.');
    }

    /**
     * Add a member to an UKM.
     */
    public function storeAnggota(Request $request, Ukm $ukm)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nim' => ['nullable', 'string', 'max:50'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'status_keanggotaan' => ['nullable', 'in:Aktif,Keluar'],
        ]);

        $this->ukmService->storeAnggota($request, $ukm);

        return back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    /**
     * Update a member.
     */
    public function updateAnggota(Request $request, Ukm $ukm, UkmAnggota $anggota)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nim' => ['nullable', 'string', 'max:50'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'status_keanggotaan' => ['nullable', 'in:Aktif,Keluar'],
        ]);

        $this->ukmService->updateAnggota($request, $anggota);

        return back()->with('success', 'Anggota berhasil diperbarui.');
    }

    /**
     * Remove a member.
     */
    public function destroyAnggota(Ukm $ukm, UkmAnggota $anggota)
    {
        $this->ukmService->destroyAnggota($anggota);

        return back()->with('success', 'Anggota berhasil dihapus.');
    }
}
