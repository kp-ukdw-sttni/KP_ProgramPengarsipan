<script setup>
import { Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import Input from '../../Components/ui/Input.vue'
import Select from '../../Components/ui/Select.vue'
import InputError from '../../Components/ui/InputError.vue'
import PageHero from '../../Components/ui/PageHero.vue'
import PrimaryButton from '../../Components/ui/PrimaryButton.vue'
import { useRoute } from '../../Composables/useRoute'
import { reactive } from 'vue'

const props = defineProps({
    ukm: {
        type: Object,
        required: true,
    },
    divisi: {
        type: Array,
        default: () => [],
    },
})

const routeFn = useRoute()

const form = useForm({
    kode: props.ukm.kode,
    name: props.ukm.name,
    deskripsi: props.ukm.deskripsi ?? '',
    pembina: props.ukm.pembina ?? '',
    ketua: props.ukm.ketua ?? '',
    divisi_id: props.ukm.divisi_id ?? '',
    status: props.ukm.status,
})

const submit = () => form.put(routeFn('ukm.update', { ukm: props.ukm.id }))

const addForm = useForm({
    nama: '',
    nim: '',
    jabatan: '',
    status_keanggotaan: 'Aktif',
})

const submitAdd = () =>
    addForm.post(routeFn('ukm.anggota.store', { ukm: props.ukm.id }), {
        onSuccess: () => addForm.reset(),
    })

const anggotaEdits = reactive({})

const getRow = (a) => {
    if (!anggotaEdits[a.id]) {
        anggotaEdits[a.id] = reactive({
            nama: a.nama,
            nim: a.nim ?? '',
            jabatan: a.jabatan ?? '',
            status_keanggotaan: a.status_keanggotaan,
        })
    }
    return anggotaEdits[a.id]
}

const saveAnggota = (a) => {
    router.put(routeFn('ukm.anggota.update', { ukm: props.ukm.id, anggota: a.id }), {
        nama: anggotaEdits[a.id].nama,
        nim: anggotaEdits[a.id].nim,
        jabatan: anggotaEdits[a.id].jabatan,
        status_keanggotaan: anggotaEdits[a.id].status_keanggotaan,
    })
}

const destroyAnggota = (a) => {
    if (!confirm(`Hapus anggota "${a.nama}" dari UKM ini?`)) return
    router.delete(routeFn('ukm.anggota.destroy', { ukm: props.ukm.id, anggota: a.id }))
}
</script>

<template>
    <AuthenticatedLayout title="Kelola UKM">
        <FlashMessages />

        <PageHero
            eyebrow="Master Data UKM"
            :title="`Kelola ${ukm.name}`"
            subtitle="Perbarui informasi UKM dan kelola daftar anggotanya."
        >
            <template #actions>
                <Link
                    :href="routeFn('ukm.index')"
                    class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20"
                >
                    Kembali
                </Link>
            </template>
        </PageHero>

        <!-- Form UKM -->
        <div class="mt-6 max-w-3xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Kode UKM</label>
                    <Input v-model="form.kode" />
                    <InputError :message="form.errors.kode" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama UKM <span class="text-red-500">*</span></label>
                    <Input v-model="form.name" />
                    <InputError :message="form.errors.name" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Pembina</label>
                    <Input v-model="form.pembina" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Ketua UKM</label>
                    <Input v-model="form.ketua" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Unit / Divisi</label>
                    <Select v-model="form.divisi_id">
                        <option value="">-- Pilih divisi --</option>
                        <option v-for="d in divisi" :key="d.id" :value="d.id">{{ d.name }}</option>
                    </Select>
                    <InputError :message="form.errors.divisi_id" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Status</label>
                    <Select v-model="form.status">
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </Select>
                    <InputError :message="form.errors.status" class="mt-1" />
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi</label>
                    <textarea
                        v-model="form.deskripsi"
                        rows="3"
                        class="block w-full rounded-lg border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 focus:outline-none"
                    />
                    <InputError :message="form.errors.deskripsi" class="mt-1" />
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <PrimaryButton type="button" :disabled="form.processing" @click="submit">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </PrimaryButton>
            </div>
        </div>

        <!-- Kelola anggota -->
        <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 px-5 py-4">
                <h3 class="text-base font-bold text-gray-800">Daftar Anggota ({{ ukm.anggota.length }})</h3>
            </div>

            <!-- Add form -->
            <div class="grid gap-3 border-b border-gray-100 bg-gray-50 px-5 py-4 sm:grid-cols-2 lg:grid-cols-5">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600">Nama <span class="text-red-500">*</span></label>
                    <Input v-model="addForm.nama" placeholder="Nama anggota" />
                    <InputError :message="addForm.errors.nama" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600">NIM</label>
                    <Input v-model="addForm.nim" placeholder="NIM" />
                    <InputError :message="addForm.errors.nim" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600">Jabatan</label>
                    <Input v-model="addForm.jabatan" placeholder="Ketua / Anggota / dll" />
                    <InputError :message="addForm.errors.jabatan" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600">Status</label>
                    <Select v-model="addForm.status_keanggotaan">
                        <option value="Aktif">Aktif</option>
                        <option value="Keluar">Keluar</option>
                    </Select>
                </div>
                <div class="flex items-end">
                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50"
                        :disabled="addForm.processing"
                        @click="submitAdd"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah
                    </button>
                </div>
            </div>

            <!-- List -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-navy text-xs uppercase tracking-wide text-gray-300">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Nama</th>
                            <th class="px-4 py-3 font-semibold">NIM</th>
                            <th class="px-4 py-3 font-semibold">Jabatan</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="a in ukm.anggota" :key="a.id" class="align-middle transition hover:bg-gray-50">
                            <td class="px-4 py-2.5">
                                <input
                                    v-model="getRow(a).nama"
                                    class="w-full min-w-[140px] rounded-lg border-gray-300 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 focus:outline-none"
                                />
                            </td>
                            <td class="px-4 py-2.5">
                                <input
                                    v-model="getRow(a).nim"
                                    class="w-full min-w-[100px] rounded-lg border-gray-300 bg-white px-3 py-1.5 font-mono text-xs shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 focus:outline-none"
                                />
                            </td>
                            <td class="px-4 py-2.5">
                                <input
                                    v-model="getRow(a).jabatan"
                                    class="w-full min-w-[110px] rounded-lg border-gray-300 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 focus:outline-none"
                                />
                            </td>
                            <td class="px-4 py-2.5">
                                <Select v-model="getRow(a).status_keanggotaan">
                                    <option value="Aktif">Aktif</option>
                                    <option value="Keluar">Keluar</option>
                                </Select>
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button
                                        type="button"
                                        class="rounded-lg bg-green-600 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-green-700"
                                        @click="saveAnggota(a)"
                                    >
                                        Simpan
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                                        @click="destroyAnggota(a)"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!ukm.anggota.length">
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                                Belum ada anggota. Tambahkan di atas.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
