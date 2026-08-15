<script setup>
import { ref, watch } from 'vue'
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

defineProps({
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
const tahunList = ref([])
const flatKategori = ref([])
const loading = ref(false)
const loadError = ref('')

const maxYear = new Date().getFullYear()

const flattenKategori = (list, depth = 0) => {
    for (const k of list) {
        flatKategori.value.push({ id: k.id, name: k.name, kode: k.kode, depth })
        if (k.children?.length) flattenKategori(k.children, depth + 1)
    }
}

const form = useForm({
    files: [],
    judul: [],
    nomor_arsip: [],
    nomor_surat: '',
    deskripsi: '',
    kategori_id: '',
    divisi_id: '',
    study_program_id: '',
    tahun: '',
    tanggal_dokumen: '',
    tanggal_diterima: '',
    pengirim: '',
    penerima: '',
    lokasi_fisik: '',
    retention_date: '',
    status_publikasi: 'Internal',
    tags: '',
})

const onFilesChange = (files) => {
    form.files = files
    form.judul = files.map((f) => {
        const base = f.name.replace(/\.[^.]+$/, '')
        return base.replace(/[-_]+/g, ' ')
    })
    form.nomor_arsip = files.map(() => '')
}

const updateJudul = (index, value) => {
    form.judul[index] = value
}

const updateNomorArsip = (index, value) => {
    form.nomor_arsip[index] = value
}

const submit = () => {
    feedbackStart('Mengunggah dokumen...')
    form.post(routeFn('arsip.store'), {
        forceFormData: true,
        onSuccess: () => {
            feedbackSuccess('Upload berhasil')
            form.reset()
            emit('close')
        },
        onError: () => {
            feedbackError('Upload gagal')
        },
    })
}

watch(
    () => props.show,
    async (open) => {
        if (!open) return

        form.reset()
        loadError.value = ''
        divisiTree.value = []
        kategoriTree.value = []
        tahunList.value = []
        flatKategori.value = []
        loading.value = true

        try {
            const { data } = await axios.get(routeFn('arsip.meta'))
            divisiTree.value = data.divisiTree ?? []
            kategoriTree.value = data.kategoriTree ?? []
            studyPrograms.value = data.studyPrograms ?? []
            tahunList.value = data.tahunList ?? []
            flattenKategori(data.kategoriTree ?? [])
        } catch (e) {
            loadError.value = 'Gagal memuat data form. Silakan coba lagi.'
        } finally {
            loading.value = false
        }
    },
)
</script>

<template>
    <Modal :show="show" max-width="5xl" @close="emit('close')">
        <div class="flex max-h-[88vh] flex-col overflow-hidden rounded-xl">
            <!-- Header -->
            <div class="relative shrink-0 bg-gradient-to-r from-navy via-navy-dark to-[#0A1120] px-6 py-5">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(212,175,55,0.15),transparent_60%)]"></div>
                <div class="relative flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-gold to-gold-light text-navy shadow-lg shadow-black/30">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-lg font-extrabold text-white">Unggah Dokumen</h3>
                            <p class="text-xs text-blue-200">Lengkapi detail arsip &mdash; tanpa meninggalkan halaman</p>
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

            <!-- Body -->
            <div class="flex-1 overflow-y-auto bg-gray-50 px-6 py-5">
                <div v-if="loading" class="flex flex-col items-center justify-center py-20">
                    <div class="h-12 w-12 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600"></div>
                    <p class="mt-4 text-sm font-semibold text-gray-500">Memuat data form...</p>
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
                        <p class="mt-1 text-xs text-gray-500">Pastikan Anda masih terhubung ke server.</p>
                    </div>
                    <SecondaryButton type="button" @click="emit('close')">Tutup</SecondaryButton>
                </div>

                <form v-else id="arsip-upload-form" class="space-y-5" @submit.prevent="submit">
                    <!-- Section: Berkas -->
                    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </span>
                            <h4 class="text-sm font-bold text-gray-800">Unggah Berkas</h4>
                            <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-600">
                                Maks. 10 berkas
                            </span>
                        </div>

                        <UploadDropzone v-model="form.files" @update:modelValue="onFilesChange" />
                        <InputError :message="form.errors.files" />

                        <div v-if="form.files.length" class="mt-4 space-y-3">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <span class="text-xs font-bold uppercase tracking-wide text-gray-500">
                                    Detail per Berkas ({{ form.files.length }})
                                </span>
                            </div>
                            <div
                                v-for="(file, index) in form.files"
                                :key="`${file.name}-${file.size}-${index}`"
                                class="rounded-xl border border-gray-200 bg-gray-50 p-3.5"
                            >
                                <p class="mb-3 truncate text-xs font-semibold text-gray-600">{{ file.name }}</p>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">Judul Dokumen</label>
                                        <Input
                                            v-model="form.judul[index]"
                                            type="text"
                                            @input="updateJudul(index, $event.target.value)"
                                        />
                                        <InputError :message="form.errors[`judul.${index}`]" />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                                            Nomor Arsip <span class="font-normal text-gray-400">(opsional)</span>
                                        </label>
                                        <Input
                                            :value="form.nomor_arsip[index]"
                                            type="text"
                                            placeholder="Otomatis jika kosong"
                                            @input="updateNomorArsip(index, $event.target.value)"
                                        />
                                        <InputError :message="form.errors[`nomor_arsip.${index}`]" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Section: Metadata -->
                    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                </svg>
                            </span>
                            <h4 class="text-sm font-bold text-gray-800">Metadata</h4>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                                    Nomor Surat <span class="font-normal text-gray-400">(opsional)</span>
                                </label>
                                <Input v-model="form.nomor_surat" type="text" />
                                <InputError :message="form.errors.nomor_surat" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Kategori</label>
                                <Select v-model="form.kategori_id">
                                    <option value="">-- Pilih Kategori --</option>
                                    <template v-for="k in flatKategori" :key="k.id">
                                        <option :value="k.id">{{ k.depth ? '&nbsp;&nbsp;&mdash; ' : '' }}{{ k.name }}</option>
                                    </template>
                                </Select>
                                <InputError :message="form.errors.kategori_id" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Divisi</label>
                                <Select v-model="form.divisi_id">
                                    <option value="">-- Pilih Divisi --</option>
                                    <optgroup v-for="f in divisiTree" :key="f.id" :label="f.name">
                                        <option v-if="!f.children?.length" :value="f.id">{{ f.name }}</option>
                                        <option v-for="c in f.children" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </optgroup>
                                </Select>
                                <InputError :message="form.errors.divisi_id" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Program Studi</label>
                                <Select v-model="form.study_program_id">
                                    <option value="">-- Pilih Program Studi --</option>
                                    <option v-for="sp in studyPrograms" :key="sp.id" :value="sp.id">{{ sp.name }}</option>
                                </Select>
                                <InputError :message="form.errors.study_program_id" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Tahun Arsip</label>
                                <Select v-model="form.tahun">
                                    <option value="">{{ maxYear }}</option>
                                    <option v-for="t in tahunList" :key="t" :value="t">{{ t }}</option>
                                </Select>
                                <InputError :message="form.errors.tahun" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Tanggal Dokumen</label>
                                <Input v-model="form.tanggal_dokumen" type="date" />
                                <InputError :message="form.errors.tanggal_dokumen" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Tanggal Diterima</label>
                                <Input v-model="form.tanggal_diterima" type="date" />
                                <InputError :message="form.errors.tanggal_diterima" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Tanggal Retensi</label>
                                <Input v-model="form.retention_date" type="date" />
                                <InputError :message="form.errors.retention_date" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Status Publikasi</label>
                                <Select v-model="form.status_publikasi">
                                    <option value="Public">Public</option>
                                    <option value="Internal">Internal</option>
                                    <option value="Confidential">Confidential</option>
                                </Select>
                                <InputError :message="form.errors.status_publikasi" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Pengirim</label>
                                <Input v-model="form.pengirim" type="text" />
                                <InputError :message="form.errors.pengirim" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Penerima</label>
                                <Input v-model="form.penerima" type="text" />
                                <InputError :message="form.errors.penerima" />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Lokasi Fisik</label>
                                <Input v-model="form.lokasi_fisik" type="text" placeholder="contoh: Rak 2, Boks 4A" />
                                <InputError :message="form.errors.lokasi_fisik" />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                                    Tags <span class="font-normal text-gray-400">(pisahkan dengan koma)</span>
                                </label>
                                <Input v-model="form.tags" type="text" placeholder="contoh: surat, keputusan, 2026" />
                                <InputError :message="form.errors.tags" />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                                    Deskripsi <span class="font-normal text-gray-400">(opsional)</span>
                                </label>
                                <textarea
                                    v-model="form.deskripsi"
                                    rows="3"
                                    class="block w-full rounded-lg border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                                />
                                <InputError :message="form.errors.deskripsi" />
                            </div>
                        </div>
                    </section>
                </form>
            </div>

            <!-- Footer -->
            <div class="flex shrink-0 flex-wrap items-center justify-between gap-3 border-t border-gray-200 bg-white px-6 py-4">
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span class="rounded-full bg-gray-100 px-2.5 py-1 font-bold text-gray-700">{{ form.files.length }}</span>
                    <span>/ 10 berkas terpilih</span>
                </div>
                <div class="flex items-center gap-2">
                    <SecondaryButton type="button" @click="form.reset()">Reset</SecondaryButton>
                    <PrimaryButton form="arsip-upload-form" type="submit" :disabled="form.processing || form.files.length === 0">
                        <span v-if="!form.processing" class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Simpan Arsip
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
