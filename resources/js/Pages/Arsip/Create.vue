<script setup>
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import Input from '../../Components/ui/Input.vue'
import Select from '../../Components/ui/Select.vue'
import InputError from '../../Components/ui/InputError.vue'
import PageHero from '../../Components/ui/PageHero.vue'
import PrimaryButton from '../../Components/ui/PrimaryButton.vue'
import SecondaryButton from '../../Components/ui/SecondaryButton.vue'
import UploadDropzone from '../../Components/UploadDropzone.vue'
import { useRoute } from '../../Composables/useRoute'
import { useUploadFeedback } from '../../Composables/useUploadFeedback'
import { ref } from 'vue'

const props = defineProps({
    divisiTree: {
        type: Array,
        default: () => [],
    },
    kategoriTree: {
        type: Array,
        default: () => [],
    },
    studyPrograms: {
        type: Array,
        default: () => [],
    },
    tahunList: {
        type: Array,
        default: () => [],
    },
})

const routeFn = useRoute()
const { start: feedbackStart, success: feedbackSuccess, error: feedbackError } = useUploadFeedback()

const errorAttempt = ref(0)

const maxYear = new Date().getFullYear()

const flatKategori = []
const flattenKategori = (list, depth = 0) => {
    for (const k of list) {
        flatKategori.push({ id: k.id, name: k.name, kode: k.kode, depth })
        if (k.children?.length) flattenKategori(k.children, depth + 1)
    }
}
flattenKategori(props.kategoriTree)

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
        },
        onError: () => {
            feedbackError('Upload gagal')
            errorAttempt.value++
        },
    })
}
</script>

<template>
    <AuthenticatedLayout title="Arsipkan Dokumen">
        <FlashMessages />

        <PageHero
            eyebrow="Arsip Dokumen"
            title="Arsipkan Dokumen"
            subtitle="Unggah berkas baru beserta metadata klasifikasinya."
        >
            <template #actions>
                <Link
                    :href="routeFn('arsip.index')"
                    class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Daftar
                </Link>
            </template>
        </PageHero>

        <div class="mt-6">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="form.hasErrors"
                :key="errorAttempt"
                class="mb-4 flex items-start gap-3 rounded-xl border border-red-300 bg-red-50 p-4 text-red-700 animate-shake"
            >
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
                    />
                </svg>
                <div class="text-sm">
                    <p class="font-semibold">Upload gagal</p>
                    <p class="mt-0.5 text-red-600">
                        Periksa kembali data yang disorot di bawah sebelum mengunggah ulang.
                    </p>
                </div>
            </div>
        </Transition>

        <form @submit.prevent="submit">
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                <!-- Left: upload area -->
                <div class="space-y-5 lg:col-span-2">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-gray-700">
                            Unggah Berkas
                        </h3>

                        <UploadDropzone v-model="form.files" @update:modelValue="onFilesChange" />
                        <InputError :message="form.errors.files" />

                        <div v-if="form.files.length" class="mt-5 space-y-3">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <span class="text-xs font-bold uppercase tracking-wide text-gray-500">
                                    Detail per Berkas ({{ form.files.length }})
                                </span>
                            </div>
                            <div
                                v-for="(file, index) in form.files"
                                :key="`${file.name}-${file.size}-${index}`"
                                class="rounded-lg border border-gray-200 bg-gray-50 p-3"
                            >
                                <p class="mb-2 truncate text-xs font-semibold text-gray-600">{{ file.name }}</p>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                                            Judul Dokumen
                                        </label>
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
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-gray-700">
                            Metadata Bersama
                        </h3>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                                    Nomor Surat <span class="font-normal text-gray-400">(opsional)</span>
                                </label>
                                <Input v-model="form.nomor_surat" type="text" />
                                <InputError :message="form.errors.nomor_surat" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                                    Kategori
                                </label>
                                <Select v-model="form.kategori_id">
                                    <option value="">-- Pilih Kategori --</option>
                                    <template v-for="k in flatKategori" :key="k.id">
                                        <option :value="k.id">
                                            {{ k.depth ? `&nbsp;&nbsp;&mdash; ` : '' }}{{ k.name }}
                                        </option>
                                    </template>
                                </Select>
                                <InputError :message="form.errors.kategori_id" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                                    Divisi
                                </label>
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
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                                    Program Studi
                                </label>
                                <Select v-model="form.study_program_id">
                                    <option value="">-- Pilih Program Studi --</option>
                                    <option v-for="sp in studyPrograms" :key="sp.id" :value="sp.id">{{ sp.name }}</option>
                                </Select>
                                <InputError :message="form.errors.study_program_id" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                                    Tahun Arsip
                                </label>
                                <Select v-model="form.tahun">
                                    <option value="">{{ maxYear }}</option>
                                    <option v-for="t in tahunList" :key="t" :value="t">{{ t }}</option>
                                </Select>
                                <InputError :message="form.errors.tahun" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                                    Tanggal Dokumen
                                </label>
                                <Input v-model="form.tanggal_dokumen" type="date" />
                                <InputError :message="form.errors.tanggal_dokumen" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                                    Tanggal Diterima
                                </label>
                                <Input v-model="form.tanggal_diterima" type="date" />
                                <InputError :message="form.errors.tanggal_diterima" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                                    Tanggal Retensi
                                </label>
                                <Input v-model="form.retention_date" type="date" />
                                <InputError :message="form.errors.retention_date" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                                    Status Publikasi
                                </label>
                                <Select v-model="form.status_publikasi">
                                    <option value="Public">Public</option>
                                    <option value="Internal">Internal</option>
                                    <option value="Confidential">Confidential</option>
                                </Select>
                                <InputError :message="form.errors.status_publikasi" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                                    Pengirim <span class="font-normal text-gray-400">(opsional)</span>
                                </label>
                                <Input v-model="form.pengirim" type="text" />
                                <InputError :message="form.errors.pengirim" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                                    Penerima <span class="font-normal text-gray-400">(opsional)</span>
                                </label>
                                <Input v-model="form.penerima" type="text" />
                                <InputError :message="form.errors.penerima" />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                                    Lokasi Fisik <span class="font-normal text-gray-400">(contoh: Rak 2, Boks 4A)</span>
                                </label>
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
                                    class="block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <InputError :message="form.errors.deskripsi" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: submit summary -->
                <div class="space-y-5">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-gray-700">
                            Ringkasan
                        </h3>
                        <dl class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-500">Berkas</dt>
                                <dd class="font-semibold text-gray-900">{{ form.files.length }} / 10</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-500">Total Ukuran</dt>
                                <dd class="font-semibold text-gray-900">
                                    {{
                                        form.files.reduce((sum, f) => sum + f.size, 0) > 0
                                            ? `${(form.files.reduce((sum, f) => sum + f.size, 0) / 1024 / 1024).toFixed(1)} MB`
                                            : '-'
                                    }}
                                </dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-500">Penyimpanan</dt>
                                <dd class="font-semibold text-gray-900">Aman (Private)</dd>
                            </div>
                        </dl>

                        <div class="mt-5 flex items-center gap-2">
                            <PrimaryButton :disabled="form.processing || form.files.length === 0">
                                Simpan Arsip
                            </PrimaryButton>
                            <SecondaryButton type="button" @click="form.reset()">
                                Reset
                            </SecondaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes shake {
    0%,
    100% {
        transform: translateX(0);
    }
    20% {
        transform: translateX(-8px);
    }
    40% {
        transform: translateX(8px);
    }
    60% {
        transform: translateX(-5px);
    }
    80% {
        transform: translateX(5px);
    }
}

.animate-shake {
    animation: shake 0.5s ease-in-out 0.3s;
}
</style>
