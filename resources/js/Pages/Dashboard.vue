<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../Components/ui/FlashMessages.vue'
import Badge from '../Components/ui/Badge.vue'
import { useAuth } from '../Composables/useAuth'

const props = defineProps({
    totalArsip: { type: Number, default: 0 },
    totalActivePeminjaman: { type: Number, default: 0 },
    totalUsers: { type: Number, default: 0 },
    totalExpiredArsip: { type: Number, default: 0 },
    totalPendingPeminjaman: { type: Number, default: 0 },
    totalCompletedPeminjaman: { type: Number, default: 0 },
    divisiStats: { type: Array, default: () => [] },
    recentPeminjaman: { type: Array, default: () => [] },
    myActivePeminjaman: { type: Array, default: () => [] },
    uploadTrend: { type: Array, default: () => [] },
    recentUploads: { type: Array, default: () => [] },
})

const { user, hasRole } = useAuth()

const isKaryawan = computed(() => hasRole('Karyawan', 'Mahasiswa', 'Dosen'))

const statusBadge = (peminjaman) => {
    const status = peminjaman.status_approval
    if (status === 'Pending') return { label: 'Pending', color: 'amber' }
    if (status === 'Approved') {
        const expired = peminjaman.expired_at && new Date(peminjaman.expired_at) < new Date()
        return expired ? { label: 'Expired', color: 'gray' } : { label: 'Active', color: 'green' }
    }
    if (status === 'Rejected') return { label: 'Rejected', color: 'red' }
    return { label: status, color: 'gray' }
}

const formatDate = (value) => {
    if (!value) return '-'
    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return '-'
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date)
}

const formatDateOnly = (value) => {
    if (!value) return '-'
    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return '-'
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date)
}

const cards = computed(() => {
    if (isKaryawan.value) {
        return [
            { label: 'Total Arsip Aktif', value: props.totalArsip, bg: 'bg-[#4f46e5]' },
            { label: 'Unduhan Saya', value: props.totalActivePeminjaman, bg: 'bg-[#2563eb]' },
            { label: 'Akses Pending', value: props.totalPendingPeminjaman, bg: 'bg-[#ef4444]' },
            { label: 'Akses Selesai', value: props.totalCompletedPeminjaman, bg: 'bg-[#f59e0b]' },
            { label: 'Akses Aktif', value: props.myActivePeminjaman.length, bg: 'bg-[#10b981]' },
        ]
    }
    return [
        { label: 'Total Arsip', value: props.totalArsip, bg: 'bg-[#4f46e5]' },
        { label: 'Unduhan / Pinjam', value: props.totalActivePeminjaman, bg: 'bg-[#2563eb]' },
        { label: 'Arsip Expired', value: props.totalExpiredArsip, bg: 'bg-[#8b5cf6]' },
        { label: 'Akses Pending', value: props.totalPendingPeminjaman, bg: 'bg-[#ef4444]' },
        { label: 'Akses Selesai', value: props.totalCompletedPeminjaman, bg: 'bg-[#f59e0b]' },
        { label: 'Staf Terdaftar', value: props.totalUsers, bg: 'bg-[#10b981]' },
    ]
})
</script>

<template>
    <AuthenticatedLayout title="Dashboard">
        <FlashMessages />

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-6 mb-8">
            <div
                v-for="card in cards"
                :key="card.label"
                :class="card.bg"
                class="flex items-center justify-between rounded-2xl p-5 text-white shadow-sm"
            >
                <div>
                    <span class="mb-1.5 block text-xs font-medium text-white/80">
                        {{ card.label }}
                    </span>
                    <span class="text-3xl font-extrabold">{{ card.value }}</span>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-6 flex items-center justify-between border-b border-gray-100 pb-4">
                <h3 class="text-base font-bold text-gray-800">
                    {{
                        isKaryawan
                            ? 'Riwayat Pengajuan Akses Saya'
                            : 'Riwayat Pengajuan Peminjaman Terbaru'
                    }}
                </h3>
                <Link
                    :href="route('peminjaman.manage')"
                    v-if="hasRole('Superadmin', 'Operator', 'Staf TU', 'Kaprodi', 'Dekan')"
                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:underline"
                >
                    Lihat Selengkapnya &rarr;
                </Link>
                <Link
                    v-else
                    :href="route('peminjaman.index')"
                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:underline"
                >
                    Lihat Selengkapnya &rarr;
                </Link>
            </div>

            <div v-if="recentPeminjaman.length === 0" class="py-12 text-center text-sm font-medium text-gray-400">
                Tidak ada riwayat aktivitas peminjaman terdokumentasi.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="border-b text-gray-500 font-semibold">
                            <th scope="col" class="w-12 px-4 py-3 text-left">No</th>
                            <th v-if="!isKaryawan" scope="col" class="px-4 py-3 text-left">
                                Nama Staf / Karyawan
                            </th>
                            <th scope="col" class="px-4 py-3 text-left">Dokumen Terkait</th>
                            <th scope="col" class="px-4 py-3 text-left">Status</th>
                            <th scope="col" class="px-4 py-3 text-left">Masa Aktif Akses</th>
                            <th scope="col" class="px-4 py-3 text-right">Waktu Pengajuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <tr v-for="(rp, index) in recentPeminjaman" :key="rp.id" class="transition-colors hover:bg-gray-50/55">
                            <td class="px-5 py-4 font-medium text-gray-400">{{ index + 1 }}</td>
                            <td v-if="!isKaryawan" class="px-5 py-4">
                                <div class="leading-none font-bold text-gray-800">{{ rp.user?.name }}</div>
                                <div class="mt-1 text-[10px] text-gray-400">{{ rp.user?.email }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="leading-tight font-semibold text-gray-900">{{ rp.arsip?.judul }}</div>
                                <div class="mt-1 text-[10px] text-gray-400">
                                    <code>{{ rp.arsip?.nomor_arsip }}</code>
                                    &bull; {{ rp.arsip?.kategori?.name }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <Badge :color="statusBadge(rp).color">
                                    {{ statusBadge(rp).label }}
                                </Badge>
                            </td>
                            <td class="px-5 py-4 text-xs">
                                <span v-if="rp.status_approval === 'Approved'" class="font-semibold text-indigo-600">
                                    s.d. {{ formatDateOnly(rp.expired_at) }} WIB
                                </span>
                                <span v-else class="italic text-gray-400">-</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right font-mono text-xs text-gray-500">
                                {{ formatDate(rp.created_at) }} WIB
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
