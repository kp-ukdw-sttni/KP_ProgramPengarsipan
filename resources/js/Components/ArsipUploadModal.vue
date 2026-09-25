<script setup>
import { ref, watch, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Modal from './ui/Modal.vue'
import Input from './ui/Input.vue'
import Select from './ui/Select.vue'
import InputError from './ui/InputError.vue'
import PrimaryButton from './ui/PrimaryButton.vue'
import SecondaryButton from './ui/SecondaryButton.vue'
import UploadDropzone from './UploadDropzone.vue'
import { useRoute } from '../Composables/useRoute'
import { useUploadFeedback } from '../Composables/useUploadFeedback'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['close'])

const routeFn = useRoute()
const { start: feedbackStart, success: feedbackSuccess, error: feedbackError } = useUploadFeedback()

const divisiTree = ref([])
const kategoriTree = ref([])
const studyPrograms = ref([])
const loading = ref(false)
const loadError = ref('')

const form = useForm({
    files: [],
    judul_utama: '',
    judul: [],
    nomor_surat: '',
    tanpa_nomor_surat: false,
    deskripsi: '',
    kategori_id: '',
    divisi_id: '',
    study_program_id: '',
    tanggal_dokumen: new Date().toISOString().slice(0, 10),
    status_publikasi: 'Internal',
    tags: '',
})

// Definition of category rules
const groups = {
    // Kelompok A: Membutuhkan Program Studi
    // Mandatory Prodi: RPS, MODUL, YUDISIUM, TA, AKRED
    // Optional/Enabled Prodi: BEASISWA, SDM
    A: ['RPS', 'MODUL', 'YUDISIUM', 'TA', 'AKRED', 'BEASISWA', 'SDM'],
    AMandatory: ['RPS', 'MODUL', 'YUDISIUM', 'TA', 'AKRED'],
    
    // Kelompok B: Membutuhkan Nomor Surat (Wajib)
    B: ['SM-MASUK', 'SM-KELUAR', 'SK', 'KERJASAMA', 'SOP', 'AKRED', 'YUDISIUM'],
    
    // Kelompok C: Kategori Non-Surat (Default Tanpa Nomor Surat & Disabled)
    C: ['NOTULEN', 'PRESENSI', 'ASET', 'LAPKEU', 'KEGIATAN-MHS'],
}

// Helper untuk mencari kode kategori dari ID
const getKategoriKode = (id) => {
    if (!id) return ''
    const targetId = String(id)
    for (const parent of kategoriTree.value) {
        if (String(parent.id) === targetId) return parent.kode
        if (parent.children) {
            const found = parent.children.find(c => String(c.id) === targetId)
            if (found) return found.kode
        }
    }
    return ''
}

// Kode kategori aktif
const activeKategoriKode = computed(() => getKategoriKode(form.kategori_id))

// Aturan Visibilitas & Mandatory
const showStudyProgram = computed(() => groups.A.includes(activeKategoriKode.value))
const isStudyProgramMandatory = computed(() => groups.AMandatory.includes(activeKategoriKode.value))

const showNomorSurat = computed(() => {
    const kode = activeKategoriKode.value
    return groups.B.includes(kode) || groups.A.includes(kode) || !groups.C.includes(kode)
})
const isNomorSuratMandatory = computed(() => groups.B.includes(activeKategoriKode.value))

// Watcher untuk perubahan Jenis Dokumen (Kategori)
watch(() => form.kategori_id, (newId) => {
    const kode = getKategoriKode(newId)
    
    // Kategori B (Membutuhkan Nomor Surat): Otomatis uncheck & enable input
    if (groups.B.includes(kode)) {
        form.tanpa_nomor_surat = false
        if (form.nomor_surat === 'Tanpa Nomor') {
            form.nomor_surat = ''
        }
    } 
    // Kategori C (Non-Surat): Otomatis check "Tanpa Nomor Surat", set "Tanpa Nomor" & disable
    else if (groups.C.includes(kode)) {
        form.tanpa_nomor_surat = true
        form.nomor_surat = 'Tanpa Nomor'
    } 
    // Default / Lainnya
    else {
        if (form.tanpa_nomor_surat) {
            form.nomor_surat = 'Tanpa Nomor'
        }
    }

    // Reset prodi jika tidak masuk dalam kelompok A
    if (!groups.A.includes(kode)) {
        form.study_program_id = ''
    }
})

// Toggle Checkbox "Tanpa Nomor Surat" secara Manual
const toggleTanpaNomor = () => {
    if (form.tanpa_nomor_surat) {
        form.nomor_surat = 'Tanpa Nomor'
    } else {
        if (form.nomor_surat === 'Tanpa Nomor') {
            form.nomor_surat = ''
        }
    }
}

// Format Tampilan Tanggal Indonesia (DD MMMM YYYY)
const formattedTanggalIndonesia = computed(() => {
    if (!form.tanggal_dokumen) return ''
    try {
        const dateObj = new Date(form.tanggal_dokumen + 'T00:00:00')
        return new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        }).format(dateObj)
    } catch (e) {
        return form.tanggal_dokumen
    }
})

const onFilesChange = (files) => {
    form.files = files
    form.judul = files.map((f) => {
        const base = f.name.replace(/\.[^.]+$/, '')
        return base.replace(/[-_]+/g, ' ')
    })
    if (files.length === 1 && !form.judul_utama) {
        form.judul_utama = form.judul[0]
    }
}

const updateJudul = (index, value) => {
    form.judul[index] = value
}

// Sync judul_utama ke individual file titles saat submit
// judul_utama selalu menimpa judul[0] untuk file tunggal
const submit = () => {
    if (form.files.length > 0 && form.judul_utama) {
        if (form.files.length === 1) {
            // File tunggal: judul_utama selalu jadi judul dokumen
            form.judul[0] = form.judul_utama
        } else {
            // Multi-file: isi yang masih kosong saja
            for (let i = 0; i < form.files.length; i++) {
                if (!form.judul[i]) {
                    form.judul[i] = `${form.judul_utama} - Berkas ${i + 1}`
                }
            }
        }
    }

    feedbackStart('Mengunggah dokumen...')
    form.post(routeFn('arsip.store'), {
        forceFormData: true,
        onSuccess: () => {
            feedbackSuccess('Upload berhasil')
            form.reset()
            emit('close')
        },
        onError: () => {
            feedbackError('Upload gagal. Mohon periksa kembali inputan Anda.')
        },
    })
}

watch(
    () => props.show,
    async (open) => {
        if (!open) return

        form.reset()
        form.tanggal_dokumen = new Date().toISOString().slice(0, 10)
        loadError.value = ''
        divisiTree.value = []
        kategoriTree.value = []
        studyPrograms.value = []
        loading.value = true

        try {
            const { data } = await axios.get(routeFn('arsip.meta'))
            divisiTree.value = data.divisiTree ?? []
            kategoriTree.value = data.kategoriTree ?? []
            studyPrograms.value = data.studyPrograms ?? []
        } catch (e) {
            loadError.value = 'Gagal memuat data form. Silakan coba lagi.'
        } finally {
            loading.value = false
        }
    },
)
</script>

<template>
    <Modal :show="show" max-width="4xl" @close="emit('close')">
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-xl">
            <!-- Header Modal -->
            <div class="relative shrink-0 bg-gradient-to-r from-navy via-navy-dark to-[#0A1120] px-6 py-5">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(212,175,55,0.15),transparent_60%)]"></div>
                <div class="relative flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-gold to-gold-light text-navy shadow-lg shadow-black/30">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-lg font-extrabold text-white">Formulir Unggah Dokumen</h3>
                            <p class="text-xs text-blue-200">Sistem Pengarsipan Dokumen Internal Kampus STTNI</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="rounded-xl p-2 text-blue-200 transition hover:bg-white/10 hover:text-white"
                        @click="emit('close')"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Body Modal -->
            <div class="flex-1 overflow-y-auto bg-gray-50 px-6 py-5">
                <div v-if="loading" class="flex flex-col items-center justify-center py-20">
                    <div class="h-12 w-12 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600"></div>
                    <p class="mt-4 text-sm font-semibold text-gray-500">Memuat data formulir...</p>
                </div>

                <div
                    v-else-if="loadError"
                    class="flex flex-col items-center justify-center gap-4 py-20 text-center"
                >
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-red-50">
                        <svg class="h-8 w-8 text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-700">{{ loadError }}</p>
                    </div>
                    <SecondaryButton type="button" @click="emit('close')">Tutup</SecondaryButton>
                </div>

                <form v-else id="arsip-upload-form" class="space-y-6" @submit.prevent="submit">
                    <!-- SECTION 1: FIELD WAJIB -->
                    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">1</span>
                                <h4 class="text-sm font-bold text-gray-800">Field Wajib (Mandatory)</h4>
                            </div>
                            <span class="text-xs font-medium text-red-500">* Harus diisi</span>
                        </div>

                        <!-- 1. Judul Dokumen Utama (Paling Atas) -->
                        <div class="mb-4">
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-700">
                                Judul Dokumen <span class="text-red-500">*</span>
                            </label>
                            <Input
                                v-model="form.judul_utama"
                                type="text"
                                placeholder="Masukkan Judul Dokumen (Contoh: Laporan Keg. 2026)"
                                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/20': form.errors.judul_utama || form.errors['judul.0'] }"
                            />
                            <InputError :message="form.errors.judul_utama || form.errors['judul.0']" class="mt-1" />
                        </div>

                        <!-- 2. File Dokumen -->
                        <div class="space-y-3">
                            <label class="block text-xs font-bold uppercase tracking-wide text-gray-700">
                                File Dokumen <span class="text-red-500">*</span>
                            </label>
                            <UploadDropzone v-model="form.files" @update:modelValue="onFilesChange" />
                            <InputError :message="form.errors.files" class="mt-1" />
                        </div>

                        <!-- Judul Spesifik per File (jika > 1 berkas) -->
                        <div v-if="form.files.length > 1" class="mt-4 space-y-3">
                            <p class="text-xs font-semibold text-gray-600">Judul Masing-masing Berkas:</p>
                            <div v-for="(file, index) in form.files" :key="`${file.name}-${index}`" class="rounded-xl border border-blue-100 bg-blue-50/50 p-3.5">
                                <p class="mb-1 truncate text-xs font-semibold text-blue-900">
                                    Berkas {{ index + 1 }}: {{ file.name }}
                                </p>
                                <div>
                                    <Input
                                        v-model="form.judul[index]"
                                        type="text"
                                        placeholder="Masukkan Judul Spesifik Berkas Ini"
                                        @input="updateJudul(index, $event.target.value)"
                                        :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/20': form.errors[`judul.${index}`] }"
                                    />
                                    <InputError :message="form.errors[`judul.${index}`]" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- Grid Form Field Wajib -->
                        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- 3. Jenis Dokumen -->
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-gray-700">
                                    Jenis Dokumen <span class="text-red-500">*</span>
                                </label>
                                <Select
                                    v-model="form.kategori_id"
                                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/20': form.errors.kategori_id }"
                                >
                                    <option value="">-- Pilih Jenis Dokumen --</option>
                                    <template v-for="parent in kategoriTree" :key="parent.id">
                                        <optgroup v-if="parent.children?.length" :label="parent.name">
                                            <option v-for="c in parent.children" :key="c.id" :value="c.id">{{ c.name }}</option>
                                        </optgroup>
                                        <option v-else :value="parent.id">{{ parent.name }}</option>
                                    </template>
                                </Select>
                                <InputError :message="form.errors.kategori_id" class="mt-1" />
                            </div>

                            <!-- 4. Unit Kerja -->
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-gray-700">
                                    Unit Kerja <span class="text-red-500">*</span>
                                </label>
                                <Select
                                    v-model="form.divisi_id"
                                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/20': form.errors.divisi_id }"
                                >
                                    <option value="">-- Pilih Unit Kerja --</option>
                                    <template v-for="f in divisiTree" :key="f.id">
                                        <optgroup v-if="f.children?.length" :label="f.name">
                                            <option v-for="c in f.children" :key="c.id" :value="c.id">{{ c.name }}</option>
                                        </optgroup>
                                        <option v-else :value="f.id">{{ f.name }}</option>
                                    </template>
                                </Select>
                                <InputError :message="form.errors.divisi_id" class="mt-1" />
                            </div>

                            <!-- 5. Program Studi (Conditional: hanya tampil jika Kelompok A) -->
                            <Transition
                                enter-active-class="transition-all duration-300 ease-out"
                                enter-from-class="opacity-0 -translate-y-2"
                                enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition-all duration-200 ease-in"
                                leave-from-class="opacity-100 translate-y-0"
                                leave-to-class="opacity-0 -translate-y-2"
                            >
                                <div v-if="showStudyProgram" class="sm:col-span-2">
                                    <label class="mb-1 block text-xs font-semibold text-gray-700">
                                        Program Studi
                                        <span v-if="isStudyProgramMandatory" class="text-red-500">*</span>
                                        <span v-else class="ml-1 text-[11px] font-normal text-gray-400">(Opsional)</span>
                                    </label>
                                    <Select
                                        v-model="form.study_program_id"
                                        :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/20': form.errors.study_program_id }"
                                    >
                                        <option value="">-- Pilih Program Studi --</option>
                                        <option v-for="sp in studyPrograms" :key="sp.id" :value="sp.id">{{ sp.name }}</option>
                                    </Select>
                                    <InputError :message="form.errors.study_program_id" class="mt-1" />
                                </div>
                            </Transition>

                            <!-- 6. Nomor Surat / Dokumen -->
                            <!-- Selalu tampil; mandatory jika Kelompok B, auto-disabled jika Kelompok C -->
                            <div class="sm:col-span-2 rounded-xl border p-3.5 transition-colors duration-200"
                                :class="isNomorSuratMandatory
                                    ? 'border-blue-200 bg-blue-50/30'
                                    : form.tanpa_nomor_surat
                                        ? 'border-gray-100 bg-gray-100/60'
                                        : 'border-gray-100 bg-gray-50/50'"
                            >
                                <div class="mb-1.5 flex flex-wrap items-center justify-between gap-2">
                                    <label class="text-xs font-semibold text-gray-700">
                                        Nomor Surat / Dokumen
                                        <span v-if="isNomorSuratMandatory" class="text-red-500">*</span>
                                        <span v-else class="ml-1 text-[11px] font-normal text-gray-400">(Opsional)</span>
                                    </label>
                                    <label
                                        class="flex cursor-pointer items-center gap-1.5 text-xs font-medium"
                                        :class="isNomorSuratMandatory ? 'cursor-not-allowed text-gray-400' : 'text-indigo-600'"
                                    >
                                        <input
                                            v-model="form.tanpa_nomor_surat"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            :disabled="isNomorSuratMandatory"
                                            @change="toggleTanpaNomor"
                                        />
                                        Tanpa Nomor Surat
                                    </label>
                                </div>
                                <Input
                                    v-model="form.nomor_surat"
                                    type="text"
                                    :disabled="form.tanpa_nomor_surat"
                                    :placeholder="isNomorSuratMandatory ? 'Wajib diisi — Contoh: 001/STTNI/SK/2026' : 'Contoh: 001/STTNI/SK/2026'"
                                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/20': form.errors.nomor_surat, 'bg-gray-100 text-gray-400': form.tanpa_nomor_surat }"
                                />
                                <InputError :message="form.errors.nomor_surat" class="mt-1" />
                            </div>

                            <!-- 7. Tanggal Dokumen (Format Indonesia) -->
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-semibold text-gray-700">
                                    Tanggal Dokumen <span class="text-red-500">*</span>
                                </label>
                                <div class="flex flex-wrap items-center gap-3">
                                    <div class="flex-1">
                                        <Input
                                            v-model="form.tanggal_dokumen"
                                            type="date"
                                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/20': form.errors.tanggal_dokumen }"
                                        />
                                    </div>
                                    <div v-if="formattedTanggalIndonesia" class="rounded-xl border border-blue-200 bg-blue-50/70 px-3.5 py-2 text-xs font-semibold text-blue-900 shadow-sm">
                                        📅 {{ formattedTanggalIndonesia }}
                                    </div>
                                </div>
                                <InputError :message="form.errors.tanggal_dokumen" class="mt-1" />
                            </div>

                            <!-- 8. Tingkat Akses / Visibilitas -->
                            <div class="sm:col-span-2 rounded-xl border border-gray-200 bg-gray-50/60 p-4">
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-800">
                                    Tingkat Akses / Visibilitas Dokumen <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <label
                                        class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition"
                                        :class="form.status_publikasi === 'Internal' ? 'border-blue-500 bg-blue-50/40 ring-2 ring-blue-500/20' : 'border-gray-200 bg-white hover:bg-gray-50'"
                                    >
                                        <input
                                            v-model="form.status_publikasi"
                                            type="radio"
                                            value="Internal"
                                            class="mt-0.5 text-blue-600 focus:ring-blue-500"
                                        />
                                        <div>
                                            <span class="block text-xs font-bold text-gray-900">Internal</span>
                                            <span class="block text-[11px] text-gray-500">Dapat dilihat &amp; diunduh oleh seluruh Dosen/Staf yang login.</span>
                                        </div>
                                    </label>

                                    <label
                                        class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition"
                                        :class="form.status_publikasi === 'Confidential' ? 'border-red-500 bg-red-50/40 ring-2 ring-red-500/20' : 'border-gray-200 bg-white hover:bg-gray-50'"
                                    >
                                        <input
                                            v-model="form.status_publikasi"
                                            type="radio"
                                            value="Confidential"
                                            class="mt-0.5 text-red-600 focus:ring-red-500"
                                        />
                                        <div>
                                            <span class="block text-xs font-bold text-gray-900">Confidential (Rahasia)</span>
                                            <span class="block text-[11px] text-gray-500">Hanya dapat diakses oleh pengunggah, pimpinan, dan Admin.</span>
                                        </div>
                                    </label>
                                </div>
                                <InputError :message="form.errors.status_publikasi" class="mt-2" />
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 2: FIELD OPSIONAL -->
                    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-200 text-xs font-bold text-gray-700">2</span>
                                <h4 class="text-sm font-bold text-gray-800">Field Opsional (Optional)</h4>
                            </div>
                            <span class="text-xs font-medium text-gray-400">Boleh dikosongkan</span>
                        </div>

                        <div>
                            <!-- Deskripsi Singkat (Opsional) -->
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-gray-700">Deskripsi Singkat (Opsional)</label>
                                <textarea
                                    v-model="form.deskripsi"
                                    rows="3"
                                    placeholder="Ringkasan singkat isi dokumen..."
                                    class="block w-full rounded-xl border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/20': form.errors.deskripsi }"
                                />
                                <InputError :message="form.errors.deskripsi" class="mt-1" />
                            </div>
                        </div>
                    </section>
                </form>
            </div>

            <!-- Footer Modal -->
            <div class="flex shrink-0 flex-wrap items-center justify-between gap-3 border-t border-gray-200 bg-white px-6 py-4">
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span class="rounded-full bg-blue-50 px-2.5 py-1 font-bold text-blue-700">{{ form.files.length }}</span>
                    <span>berkas dipilih</span>
                </div>
                <div class="flex items-center gap-2">
                    <SecondaryButton type="button" @click="emit('close')">Batal</SecondaryButton>
                    <PrimaryButton form="arsip-upload-form" type="submit" :disabled="form.processing || form.files.length === 0">
                        <span v-if="!form.processing" class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Unggah &amp; Simpan Dokumen
                        </span>
                        <span v-else class="inline-flex items-center gap-2">
                            <span class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            Mengunggah...
                        </span>
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </Modal>
</template>

