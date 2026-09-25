<script setup>
import { computed, ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import Input from '../../Components/ui/Input.vue'
import Select from '../../Components/ui/Select.vue'
import InputError from '../../Components/ui/InputError.vue'
import PageHero from '../../Components/ui/PageHero.vue'
import PrimaryButton from '../../Components/ui/PrimaryButton.vue'
import { useRoute } from '../../Composables/useRoute'

const props = defineProps({
    ukmList: {
        type: Array,
        default: () => [],
    },
})

const routeFn = useRoute()

const statusOptions = ['Hadir', 'Izin', 'Sakit', 'Alpha']

const form = useForm({
    ukm_id: '',
    judul_kegiatan: '',
    tanggal_kegiatan: new Date().toISOString().slice(0, 10),
    pertemuan_ke: '',
    catatan_pengisi: '',
    anggota: [],
})

const anggotaRows = ref([])

const selectedUkm = computed(() =>
    props.ukmList.find((u) => String(u.id) === String(form.ukm_id)),
)

const step = ref(1)

const isStep1Valid = computed(() => {
    return (
        form.ukm_id &&
        form.tanggal_kegiatan &&
        form.pertemuan_ke !== '' &&
        form.pertemuan_ke !== null &&
        form.judul_kegiatan?.trim()
    )
})

const nextStep = () => {
    if (!form.ukm_id) {
        form.errors.ukm_id = 'Pilih UKM terlebih dahulu.'
        return
    }
    if (!form.tanggal_kegiatan) {
        form.errors.tanggal_kegiatan = 'Tanggal kegiatan wajib diisi.'
        return
    }
    if (form.pertemuan_ke === '' || form.pertemuan_ke === null) {
        form.errors.pertemuan_ke = 'Pertemuan ke- wajib diisi.'
        return
    }
    if (!form.judul_kegiatan?.trim()) {
        form.errors.judul_kegiatan = 'Judul kegiatan wajib diisi.'
        return
    }
    form.errors.ukm_id = null
    form.errors.tanggal_kegiatan = null
    form.errors.pertemuan_ke = null
    form.errors.judul_kegiatan = null
    step.value = 2
}

const prevStep = () => {
    step.value = 1
}

const onUkmChange = () => {
    anggotaRows.value = (selectedUkm.value?.anggota ?? []).map((a) => ({
        anggota_id: a.id,
        nama: a.nama,
        nim: a.nim || '-',
        status_kehadiran: 'Hadir',
        keterangan: '',
    }))
    form.anggota = anggotaRows.value
}

const setStatus = (row, status) => {
    row.status_kehadiran = status
    if (status !== 'Izin') row.keterangan = ''
}

const statusBadgeClass = (status) => {
    const base = 'inline-flex items-center rounded-lg px-2 py-1 text-[11px] font-bold transition'
    switch (status) {
        case 'Hadir':
            return `${base} bg-green-600 text-white`
        case 'Izin':
            return `${base} bg-amber-500 text-white`
        case 'Sakit':
            return `${base} bg-red-500 text-white`
        default:
            return `${base} bg-gray-500 text-white`
    }
}

const statusPillClass = (status, selected) => {
    const base =
        'inline-flex items-center rounded-lg px-2 py-1 text-[11px] font-semibold transition cursor-pointer select-none'
    if (selected) return statusBadgeClass(status)
    return `${base} bg-gray-100 text-gray-600 hover:bg-gray-200`
}

const hitung = (status) =>
    anggotaRows.value.filter((a) => a.status_kehadiran === status).length

const submit = () => {
    form.anggota = anggotaRows.value
    form.post(routeFn('presensi.store'))
}
</script>

<template>
    <AuthenticatedLayout title="Presensi Lapangan UKM">
        <FlashMessages />

        <PageHero
            eyebrow="Sie Kesiswaan"
            title="Form Presensi Lapangan UKM"
            subtitle="Pilih UKM & informasi kegiatan terlebih dahulu, lalu isi daftar kehadiran anggota."
        >
            <template #actions>
                <Link
                    :href="routeFn('presensi.index')"
                    class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali
                </Link>
            </template>
        </PageHero>

        <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7">
            <!-- Step Indicator -->
            <div class="mb-6 flex items-center gap-4 border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <span
                        class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold transition"
                        :class="step === 1 ? 'bg-navy text-white' : 'bg-green-100 text-green-700'"
                    >
                        1
                    </span>
                    <span class="text-xs font-bold text-gray-800">1. Informasi Kegiatan</span>
                </div>
                <div class="h-0.5 w-8 bg-gray-200"></div>
                <div class="flex items-center gap-2">
                    <span
                        class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold transition"
                        :class="step === 2 ? 'bg-navy text-white' : 'bg-gray-100 text-gray-400'"
                    >
                        2
                    </span>
                    <span class="text-xs font-bold" :class="step === 2 ? 'text-gray-800' : 'text-gray-400'">2. Daftar Kehadiran Anggota</span>
                </div>
            </div>

            <!-- STEP 1: FORM INFORMASI KEGIATAN -->
            <div v-if="step === 1" class="space-y-6">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama UKM <span class="text-red-500">*</span></label>
                        <Select v-model="form.ukm_id" @change="onUkmChange">
                            <option value="">-- Pilih UKM --</option>
                            <option v-for="u in ukmList" :key="u.id" :value="u.id">
                                {{ u.name }}
                            </option>
                        </Select>
                        <InputError :message="form.errors.ukm_id" class="mt-1" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Tanggal Kegiatan <span class="text-red-500">*</span></label>
                        <Input v-model="form.tanggal_kegiatan" type="date" />
                        <InputError :message="form.errors.tanggal_kegiatan" class="mt-1" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Pertemuan Ke <span class="text-red-500">*</span></label>
                        <Input v-model="form.pertemuan_ke" type="number" min="1" placeholder="Contoh: 3" />
                        <InputError :message="form.errors.pertemuan_ke" class="mt-1" />
                    </div>

                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Judul Kegiatan <span class="text-red-500">*</span></label>
                        <Input v-model="form.judul_kegiatan" placeholder="Contoh: Latihan Rutin Mingguan" />
                        <InputError :message="form.errors.judul_kegiatan" class="mt-1" />
                    </div>
                </div>

                <div class="flex items-center justify-end border-t border-gray-100 pt-6">
                    <PrimaryButton
                        type="button"
                        class="!px-6 !py-3 text-sm font-bold"
                        :disabled="!isStep1Valid"
                        @click="nextStep"
                    >
                        Lanjut ke Daftar Kehadiran &rarr;
                    </PrimaryButton>
                </div>
            </div>

            <!-- STEP 2: DAFTAR KEHADIRAN ANGGOTA -->
            <div v-else class="space-y-6">
                <!-- Info Ringkasan Kegiatan -->
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-blue-100 bg-blue-50/60 p-4">
                    <div>
                        <p class="text-xs font-bold text-blue-900">{{ selectedUkm?.name }}</p>
                        <p class="text-xs text-blue-700">
                            {{ form.judul_kegiatan || 'Sesi Presensi' }} | Tanggal: {{ form.tanggal_kegiatan }} | Pertemuan ke-{{ form.pertemuan_ke || '-' }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg border border-blue-200 bg-white px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50"
                        @click="prevStep"
                    >
                        Ubah Informasi Sesi
                    </button>
                </div>

                <!-- Tabel Kehadiran -->
                <div>
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <h3 class="text-base font-bold text-gray-800">Daftar Kehadiran Anggota</h3>
                        <div v-if="anggotaRows.length" class="flex flex-wrap gap-2 text-[11px] font-semibold">
                            <span class="rounded-lg bg-green-100 px-2 py-1 text-green-700">Hadir: {{ hitung('Hadir') }}</span>
                            <span class="rounded-lg bg-amber-100 px-2 py-1 text-amber-700">Izin: {{ hitung('Izin') }}</span>
                            <span class="rounded-lg bg-red-100 px-2 py-1 text-red-700">Sakit: {{ hitung('Sakit') }}</span>
                            <span class="rounded-lg bg-gray-100 px-2 py-1 text-gray-600">Alpha: {{ hitung('Alpha') }}</span>
                        </div>
                    </div>

                    <div v-if="!anggotaRows.length" class="rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-10 text-center text-sm text-gray-500">
                        UKM ini belum memiliki anggota aktif. Tambahkan anggota melalui menu Kelola UKM.
                    </div>

                    <div v-else class="overflow-hidden rounded-xl border border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-navy text-xs uppercase tracking-wide text-gray-300">
                                    <tr>
                                        <th class="px-3 py-3 text-center font-semibold">#</th>
                                        <th class="px-3 py-3 font-semibold">NIM</th>
                                        <th class="px-3 py-3 font-semibold">Nama</th>
                                        <th class="px-3 py-3 text-center font-semibold">Status Kehadiran</th>
                                        <th class="px-3 py-3 font-semibold">Keterangan (Alasan Izin)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="(row, idx) in anggotaRows" :key="row.anggota_id" class="bg-white transition hover:bg-gray-50">
                                        <td class="px-3 py-2 text-center text-gray-500">{{ idx + 1 }}</td>
                                        <td class="px-3 py-2 font-mono text-xs text-gray-600">{{ row.nim }}</td>
                                        <td class="px-3 py-2 font-medium text-gray-800">{{ row.nama }}</td>
                                        <td class="px-3 py-2">
                                            <div class="flex flex-wrap justify-center gap-1">
                                                <button
                                                    v-for="status in statusOptions"
                                                    :key="status"
                                                    type="button"
                                                    :class="statusPillClass(status, row.status_kehadiran === status)"
                                                    @click="setStatus(row, status)"
                                                >
                                                    {{ status }}
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input
                                                v-model="row.keterangan"
                                                :disabled="row.status_kehadiran !== 'Izin'"
                                                :required="row.status_kehadiran === 'Izin'"
                                                type="text"
                                                placeholder="Alasan izin..."
                                                class="w-full min-w-[160px] rounded-lg border-gray-300 bg-white px-3 py-1.5 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:bg-gray-100 disabled:text-gray-400"
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <InputError :message="form.errors.anggota" class="mt-2" />
                </div>

                <!-- Catatan Pengisi -->
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catatan Pengisi (Opsional)</label>
                    <textarea
                        v-model="form.catatan_pengisi"
                        rows="2"
                        placeholder="Catatan tambahan untuk pertemuan ini..."
                        class="block w-full rounded-lg border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                    />
                    <InputError :message="form.errors.catatan_pengisi" class="mt-1" />
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between border-t border-gray-100 pt-6">
                    <button
                        type="button"
                        class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100"
                        @click="prevStep"
                    >
                        &larr; Kembali
                    </button>
                    <PrimaryButton
                        type="button"
                        class="!px-6 !py-3 text-base font-bold"
                        :disabled="!anggotaRows.length || form.processing"
                        @click="submit"
                    >
                        <svg v-if="form.processing" class="h-5 w-5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                        </svg>
                        {{ form.processing ? 'Menyimpan...' : 'Submit Kehadiran' }}
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
