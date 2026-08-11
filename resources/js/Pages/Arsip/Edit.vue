<script setup>
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import Input from '../../Components/ui/Input.vue'
import Select from '../../Components/ui/Select.vue'
import InputError from '../../Components/ui/InputError.vue'
import PrimaryButton from '../../Components/ui/PrimaryButton.vue'
import { useRoute } from '../../Composables/useRoute'
import { useUploadFeedback } from '../../Composables/useUploadFeedback'
import { ref } from 'vue'

const props = defineProps({
    arsip: {
        type: Object,
        required: true,
    },
    divisiTree: {
        type: Array,
        default: () => [],
    },
    kategoriTree: {
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
    nomor_arsip: props.arsip.nomor_arsip ?? '',
    nomor_surat: props.arsip.nomor_surat ?? '',
    judul: props.arsip.judul ?? '',
    deskripsi: props.arsip.deskripsi ?? '',
    kategori_id: props.arsip.kategori_id ?? '',
    divisi_id: props.arsip.divisi_id ?? '',
    tahun: props.arsip.tahun ?? '',
    retention_date: (props.arsip.retention_date ?? '').slice(0, 10),
    status: props.arsip.status ?? 'Aktif',
    status_publikasi: props.arsip.status_publikasi ?? 'Restricted',
    tags: props.arsip.tags ?? '',
    file: null,
    change_note: '',
})

const onFileSelected = (event) => {
    form.file = event.target.files[0] ?? null
}

const submit = () => {
    feedbackStart('Menyimpan perubahan...')
    form.put(routeFn('arsip.update', props.arsip.id), {
        forceFormData: true,
        onSuccess: () => {
            feedbackSuccess('Perubahan disimpan')
        },
        onError: () => {
            feedbackError('Penyimpanan gagal')
            errorAttempt.value++
        },
    })
}
</script>

<template>
    <AuthenticatedLayout title="Edit Arsip">
        <FlashMessages />

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
                    <p class="font-semibold">Penyimpanan gagal</p>
                    <p class="mt-0.5 text-red-600">
                        Periksa kembali data yang disorot di bawah sebelum menyimpan ulang.
                    </p>
                </div>
            </div>
        </Transition>

        <form @submit.prevent="submit">
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                <div class="space-y-5 lg:col-span-2">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-gray-700">
                            Detail Dokumen
                        </h3>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Judul Dokumen</label>
                                <Input v-model="form.judul" type="text" />
                                <InputError :message="form.errors.judul" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Nomor Arsip</label>
                                <Input v-model="form.nomor_arsip" type="text" />
                                <InputError :message="form.errors.nomor_arsip" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Nomor Surat</label>
                                <Input v-model="form.nomor_surat" type="text" />
                                <InputError :message="form.errors.nomor_surat" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Kategori</label>
                                <Select v-model="form.kategori_id">
                                    <option value="">-- Pilih Kategori --</option>
                                    <template v-for="k in flatKategori" :key="k.id">
                                        <option :value="k.id">
                                            {{ k.depth ? '&mdash; ' : '' }}{{ k.name }}
                                        </option>
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
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Tahun Arsip</label>
                                <Select v-model="form.tahun">
                                    <option value="">{{ maxYear }}</option>
                                    <option v-for="t in tahunList" :key="t" :value="t">{{ t }}</option>
                                </Select>
                                <InputError :message="form.errors.tahun" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Tanggal Retensi</label>
                                <Input v-model="form.retention_date" type="date" />
                                <InputError :message="form.errors.retention_date" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Status</label>
                                <Select v-model="form.status">
                                    <option value="Aktif">Aktif</option>
                                    <option value="Expired">Expired</option>
                                    <option value="Dimusnahkan">Dimusnahkan</option>
                                </Select>
                                <InputError :message="form.errors.status" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Status Publikasi</label>
                                <Select v-model="form.status_publikasi">
                                    <option value="Public">Public</option>
                                    <option value="Restricted">Restricted</option>
                                </Select>
                                <InputError :message="form.errors.status_publikasi" />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Tags</label>
                                <Input v-model="form.tags" type="text" placeholder="contoh: surat, keputusan" />
                                <InputError :message="form.errors.tags" />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Deskripsi</label>
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

                <div class="space-y-5">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-gray-700">
                            Ganti Berkas
                        </h3>
                        <p class="mb-3 text-xs text-gray-500">
                            Berkas saat ini: <span class="font-semibold text-gray-700">{{ arsip.file_path?.split('/').pop() }}</span>
                        </p>
                        <label
                            class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center transition hover:border-indigo-300 hover:bg-indigo-50/50"
                        >
                            <svg class="h-8 w-8 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            <span class="mt-2 text-xs font-semibold text-gray-700">
                                {{ form.file ? form.file.name : 'Pilih berkas baru (opsional)' }}
                            </span>
                            <span class="mt-1 text-[10px] text-gray-400">PDF, DOCX, JPG, PNG &mdash; maks 10MB</span>
                            <input type="file" accept=".pdf,.docx,.jpg,.jpeg,.png" class="hidden" @change="onFileSelected" />
                        </label>
                        <InputError :message="form.errors.file" />

                        <div v-if="form.file" class="mt-4">
                            <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                                Catatan Perubahan (versioning)
                            </label>
                            <input
                                v-model="form.change_note"
                                type="text"
                                placeholder="contoh: Revisi penandatanganan"
                                class="block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <InputError :message="form.errors.change_note" />
                        </div>

                        <div class="mt-5">
                            <PrimaryButton :disabled="form.processing">
                                Simpan Perubahan
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
