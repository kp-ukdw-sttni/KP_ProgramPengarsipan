<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArsipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'files'             => ['required', 'array', 'min:1', 'max:10'],
            'files.*'           => ['file', 'mimes:pdf,docx,xlsx,pptx', 'max:25600'],
            'judul_utama'       => ['required_without:judul.0', 'nullable', 'string', 'max:255'],
            'judul'             => ['required', 'array', 'min:1', 'max:10'],
            'judul.*'           => ['required', 'string', 'max:255'],
            'kategori_id'       => ['required', 'exists:kategori_arsip,id'],
            'divisi_id'         => ['required', 'exists:divisi,id'],
            // study_program_id: validated conditionally in withValidator()
            'study_program_id'  => ['nullable', 'exists:study_programs,id'],
            'status_publikasi'  => ['required', 'string', 'in:Internal,Confidential'],
            'tanggal_dokumen'   => ['required', 'date'],
            // nomor_surat: validated conditionally in withValidator()
            'nomor_surat'       => ['nullable', 'string', 'max:255'],
            'tanpa_nomor_surat' => ['nullable', 'boolean'],
            'deskripsi'         => ['nullable', 'string'],
            'pengirim'          => ['nullable', 'string', 'max:255'],
            'penerima'          => ['nullable', 'string', 'max:255'],
            'lokasi_fisik'      => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Apply conditional validation rules after base rules pass.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $kode = $this->getKategoriKode();

            // ── Kelompok A Mandatory: Program Studi wajib ─────────────────────────
            // Kode: RPS, MODUL, YUDISIUM, TA, AKRED
            $kelompokAMandatory = ['RPS', 'MODUL', 'YUDISIUM', 'TA', 'AKRED'];
            if (in_array($kode, $kelompokAMandatory) && ! $this->study_program_id) {
                $validator->errors()->add(
                    'study_program_id',
                    'Program Studi wajib dipilih untuk jenis dokumen "'.$kode.'".'
                );
            }

            // ── Kelompok B: Nomor Surat wajib (kecuali "Tanpa Nomor Surat") ───────
            // Kode: SM-MASUK, SM-KELUAR, SK, KERJASAMA, SOP, AKRED, YUDISIUM
            $kelompokB = ['SM-MASUK', 'SM-KELUAR', 'SK', 'KERJASAMA', 'SOP', 'AKRED', 'YUDISIUM'];
            if (in_array($kode, $kelompokB) && ! $this->boolean('tanpa_nomor_surat') && ! $this->nomor_surat) {
                $validator->errors()->add(
                    'nomor_surat',
                    'Nomor Surat wajib diisi untuk jenis dokumen "'.$kode.'".'
                );
            }

            // ── Kategori lain: Nomor Surat wajib jika "Tanpa Nomor Surat" tidak dicentang ─
            if (! in_array($kode, $kelompokB) && ! $this->boolean('tanpa_nomor_surat') && ! $this->nomor_surat) {
                $validator->errors()->add(
                    'nomor_surat',
                    'Nomor Surat wajib diisi jika tidak mencentang "Tanpa Nomor Surat".'
                );
            }
        });
    }

    /**
     * Resolve the 'kode' of the selected KategoriArsip from its ID.
     */
    private function getKategoriKode(): string
    {
        if (! $this->kategori_id) {
            return '';
        }

        $kategori = \App\Models\KategoriArsip::find($this->kategori_id);

        return $kategori?->kode ?? '';
    }

    /**
     * Custom human-readable error messages.
     */
    public function messages(): array
    {
        return [
            'files.required'       => 'Minimal satu berkas harus diunggah.',
            'files.max'            => 'Maksimum 10 berkas dapat diunggah sekaligus.',
            'files.*.mimes'        => 'Format berkas yang diizinkan: PDF, DOCX, XLSX, PPTX.',
            'files.*.max'          => 'Ukuran setiap berkas maksimal 25 MB.',
            'judul.*.required'     => 'Judul untuk setiap berkas harus diisi.',
            'kategori_id.required' => 'Jenis Dokumen harus dipilih.',
            'kategori_id.exists'   => 'Jenis Dokumen yang dipilih tidak valid.',
            'divisi_id.required'   => 'Unit Kerja harus dipilih.',
            'divisi_id.exists'     => 'Unit Kerja yang dipilih tidak valid.',
            'study_program_id.exists' => 'Program Studi yang dipilih tidak valid.',
            'status_publikasi.required' => 'Tingkat Akses harus dipilih.',
            'tanggal_dokumen.required'  => 'Tanggal Dokumen harus diisi.',
            'tanggal_dokumen.date'      => 'Format Tanggal Dokumen tidak valid.',
        ];
    }
}
