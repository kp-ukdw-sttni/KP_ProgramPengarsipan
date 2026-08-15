<script setup>
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import PageHero from '../../Components/ui/PageHero.vue'
import { useRoute } from '../../Composables/useRoute'

const props = defineProps({
    presensi: {
        type: Object,
        required: true,
    },
    rekap: {
        type: Object,
        required: true,
    },
})

const routeFn = useRoute()

const statusStyle = (status) => {
    switch (status) {
        case 'Terverifikasi':
            return 'bg-green-100 text-green-700 ring-green-600/20'
        case 'Ditolak':
            return 'bg-red-100 text-red-700 ring-red-600/20'
        default:
            return 'bg-amber-100 text-amber-700 ring-amber-600/20'
    }
}

const badgeClass = (status) => {
    switch (status) {
        case 'Hadir':
            return 'bg-green-100 text-green-700'
        case 'Izin':
            return 'bg-amber-100 text-amber-700'
        case 'Sakit':
            return 'bg-red-100 text-red-700'
        default:
            return 'bg-gray-200 text-gray-600'
    }
}

const openPdf = () => window.open(routeFn('presensi.pdf', { presensi: props.presensi.id }), '_blank')
const openExcel = () => window.open(routeFn('presensi.excel', { presensi: props.presensi.id }), '_blank')
</script>

<template>
    <AuthenticatedLayout title="Detail Presensi">
        <FlashMessages />

        <PageHero
            eyebrow="Detail Presensi"
            :title="`${rekap.ukm.name} - Pertemuan ${rekap.pertemuan_ke ?? '-'}`"
            :subtitle="rekap.judul_kegiatan || rekap.tanggal_kegiatan"
        >
            <template #actions>
                <Link
                    :href="routeFn('presensi.index')"
                    class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20"
                >
                    Kembali
                </Link>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:bg-red-700"
                    @click="openPdf"
                >
                    Unduh PDF
                </button>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:bg-green-700"
                    @click="openExcel"
                >
                    Unduh Excel
                </button>
            </template>
        </PageHero>

        <!-- Info rekap -->
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Nomor Arsip</p>
                <p class="mt-1 font-mono text-sm font-bold text-gray-900">{{ rekap.nomor_arsip || '-' }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Tanggal Kegiatan</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">{{ rekap.tanggal_kegiatan }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Diisi Oleh</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">{{ rekap.pengisi }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Status Verifikasi</p>
                <span
                    class="mt-1 inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1"
                    :class="statusStyle(rekap.status_arsip)"
                >
                    {{ rekap.status_arsip }}
                </span>
            </div>
        </div>

        <!-- Ringkasan -->
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-5">
            <div v-for="(count, label) in rekap.rekap" :key="label" class="rounded-2xl border border-gray-200 bg-white p-4 text-center shadow-sm">
                <p class="text-2xl font-extrabold text-gray-900">{{ count }}</p>
                <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">{{ label }}</p>
            </div>
        </div>

        <!-- Tabel detail -->
        <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-base font-bold text-gray-800">Detail Kehadiran</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-navy text-xs uppercase tracking-wide text-gray-300">
                        <tr>
                            <th class="px-4 py-3 text-center font-semibold">No</th>
                            <th class="px-4 py-3 font-semibold">NIM</th>
                            <th class="px-4 py-3 font-semibold">Nama</th>
                            <th class="px-4 py-3 text-center font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="d in rekap.details" :key="d.no" class="transition hover:bg-gray-50">
                            <td class="px-4 py-3 text-center text-gray-500">{{ d.no }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ d.nim }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ d.nama }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold" :class="badgeClass(d.status_kehadiran)">
                                    {{ d.status_kehadiran }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ d.keterangan || '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
