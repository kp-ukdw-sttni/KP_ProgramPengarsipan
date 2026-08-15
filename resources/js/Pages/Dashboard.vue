<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../Components/ui/FlashMessages.vue'
import Badge from '../Components/ui/Badge.vue'
import { useAuth } from '../Composables/useAuth'
import { useRoute } from '../Composables/useRoute'
import { useUploadModal } from '../Composables/useUploadModal'

const props = defineProps({
    totalArsip: { type: Number, default: 0 },
    totalDownloads: { type: Number, default: 0 },
    totalUsers: { type: Number, default: 0 },
    totalAktifArsip: { type: Number, default: 0 },
    totalInaktifArsip: { type: Number, default: 0 },
    totalDiarsipkanArsip: { type: Number, default: 0 },
    totalDimusnahkanArsip: { type: Number, default: 0 },
    totalPendingPeminjaman: { type: Number, default: 0 },
    totalCompletedPeminjaman: { type: Number, default: 0 },
    divisiStats: { type: Array, default: () => [] },
    recentPeminjaman: { type: Array, default: () => [] },
    myActivePeminjaman: { type: Array, default: () => [] },
    uploadTrend: { type: Array, default: () => [] },
    recentUploads: { type: Array, default: () => [] },
})

const { user, hasRole } = useAuth()
const routeFn = useRoute()
const { open: openUploadModal } = useUploadModal()

const isKaryawan = computed(() => hasRole('Karyawan', 'Mahasiswa', 'Dosen'))
const canUpload = computed(() => hasRole('Superadmin', 'Operator', 'Staf TU'))

const firstName = computed(() => (user.value?.name ?? '').split(' ')[0])

const today = computed(() =>
    new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date()),
)

const subtitle = computed(() =>
    isKaryawan.value
        ? 'Pantau akses dan pengajuan dokumen arsip Anda di bawah ini.'
        : 'Ringkasan kondisi arsip, aktivitas akses, dan statistik terbaru.',
)

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

const monthLabel = (month) => {
    const [mm, yy] = String(month).split('-')
    const date = new Date(Number(yy), Number(mm) - 1, 1)
    const label = new Intl.DateTimeFormat('id-ID', { month: 'short' }).format(date)
    return `${label} ${String(yy).slice(2)}`
}

const formatFileSize = (bytes) => {
    if (!bytes) return '-'
    const units = ['B', 'KB', 'MB', 'GB']
    let value = Number(bytes)
    let i = 0
    while (value >= 1024 && i < units.length - 1) {
        value /= 1024
        i++
    }
    return `${value.toFixed(value >= 100 ? 0 : 1)} ${units[i]}`
}

const publikasiBadge = (status) => {
    const map = {
        Public: { label: 'Public', color: 'green' },
        Internal: { label: 'Internal', color: 'blue' },
        Confidential: { label: 'Confidential', color: 'red' },
    }
    return map[status] ?? { label: status ?? '-', color: 'gray' }
}

const trendMax = computed(() =>
    Math.max(1, ...props.uploadTrend.map((t) => Number(t.total) || 0)),
)

const divisiMax = computed(() =>
    Math.max(1, ...props.divisiStats.map((d) => Number(d.arsip_count) || 0)),
)

const cards = computed(() => {
    if (isKaryawan.value) {
        return [
            {
                label: 'Total Arsip Aktif',
                value: props.totalAktifArsip,
                icon: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
                chip: 'bg-blue-50 text-blue-600',
            },
            {
                label: 'Unduhan Saya',
                value: props.totalDownloads,
                icon: 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5',
                chip: 'bg-indigo-50 text-indigo-600',
            },
            {
                label: 'Akses Pending',
                value: props.totalPendingPeminjaman,
                icon: 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
                chip: 'bg-amber-50 text-amber-600',
            },
            {
                label: 'Akses Aktif',
                value: props.myActivePeminjaman.length,
                icon: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                chip: 'bg-green-50 text-green-600',
            },
            {
                label: 'Akses Selesai',
                value: props.totalCompletedPeminjaman,
                icon: 'M4.5 12.75l6 6 9-13.5',
                chip: 'bg-blue-50 text-blue-600',
            },
        ]
    }
    return [
        {
            label: 'Total Arsip',
            value: props.totalArsip,
            icon: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
            chip: 'bg-blue-50 text-blue-600',
        },
        {
            label: 'Arsip Aktif',
            value: props.totalAktifArsip,
            icon: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            chip: 'bg-green-50 text-green-600',
        },
        {
            label: 'Arsip Inaktif',
            value: props.totalInaktifArsip,
            icon: 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
            chip: 'bg-amber-50 text-amber-600',
        },
        {
            label: 'Arsip Diarsipkan',
            value: props.totalDiarsipkanArsip,
            icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            chip: 'bg-blue-50 text-blue-600',
        },
        {
            label: 'Arsip Dimusnahkan',
            value: props.totalDimusnahkanArsip,
            icon: 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0',
            chip: 'bg-gray-100 text-gray-600',
        },
        {
            label: 'Total Unduhan',
            value: props.totalDownloads,
            icon: 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5',
            chip: 'bg-indigo-50 text-indigo-600',
        },
        {
            label: 'Akses Pending',
            value: props.totalPendingPeminjaman,
            icon: 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
            chip: 'bg-amber-50 text-amber-600',
        },
        {
            label: 'Akses Selesai',
            value: props.totalCompletedPeminjaman,
            icon: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            chip: 'bg-green-50 text-green-600',
        },
        {
            label: 'Staf Terdaftar',
            value: props.totalUsers,
            icon: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
            chip: 'bg-indigo-50 text-indigo-600',
        },
    ]
})
</script>

<template>
    <AuthenticatedLayout title="Dashboard">
        <FlashMessages />

        <div class="space-y-6">
            <!-- Hero -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-navy via-navy-dark to-[#0A1120] p-6 text-white shadow-lg sm:p-8">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(212,175,55,0.18),transparent_55%)]"></div>
                <div class="relative flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gold-light">{{ today }}</p>
                        <h2 class="mt-1.5 text-2xl font-extrabold sm:text-3xl">
                            Halo, {{ firstName }}
                        </h2>
                        <p class="mt-1 text-sm text-blue-200">{{ subtitle }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            v-if="canUpload"
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-600/40 transition hover:from-blue-700 hover:to-blue-800"
                            @click="openUploadModal"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            Unggah Dokumen
                        </button>
                        <Link
                            :href="routeFn('arsip.index')"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20"
                        >
                            Lihat Dokumen
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Stat cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <div
                    v-for="card in cards"
                    :key="card.label"
                    class="flex items-center gap-3.5 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" :class="card.chip">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <span class="block truncate text-xs font-medium text-gray-500">{{ card.label }}</span>
                        <span class="block text-2xl font-extrabold leading-tight text-gray-900">{{ card.value }}</span>
                    </div>
                </div>
            </div>

            <!-- Charts row -->
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                <!-- Upload trend -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-gray-800">Tren Upload Arsip</h3>
                            <p class="text-xs text-gray-400">Jumlah berkas per bulan</p>
                        </div>
                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-600">
                            {{ props.totalArsip }} total
                        </span>
                    </div>

                    <div v-if="uploadTrend.length === 0" class="py-12 text-center text-sm font-medium text-gray-400">
                        Belum ada data upload.
                    </div>

                    <div v-else class="flex h-44 items-end gap-2 sm:gap-3">
                        <div v-for="t in uploadTrend" :key="t.month" class="group flex h-full flex-1 flex-col items-center justify-end gap-1.5">
                            <span class="text-xs font-bold text-gray-600 opacity-0 transition group-hover:opacity-100">
                                {{ t.total }}
                            </span>
                            <div
                                class="w-full max-w-[34px] rounded-t-lg bg-gradient-to-t from-blue-700 to-blue-500 shadow-sm transition-all duration-300 group-hover:from-blue-800 group-hover:to-blue-600"
                                :style="{ height: `${Math.round((Number(t.total) / trendMax) * 100)}%` }"
                            ></div>
                            <span class="whitespace-nowrap text-[10px] font-medium text-gray-400">
                                {{ monthLabel(t.month) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Divisi stats -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="mb-4">
                        <h3 class="text-sm font-bold text-gray-800">Arsip per Divisi</h3>
                        <p class="text-xs text-gray-400">Distribusi dokumen tersimpan</p>
                    </div>

                    <div v-if="divisiStats.length === 0" class="py-12 text-center text-sm font-medium text-gray-400">
                        Belum ada data divisi.
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="d in divisiStats" :key="d.id" class="flex items-center gap-3">
                            <span class="w-24 shrink-0 truncate text-xs font-semibold text-gray-600 sm:w-28">
                                {{ d.name }}
                            </span>
                            <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-gray-100">
                                <div
                                    class="h-full rounded-full bg-gradient-to-r from-gold to-blue-600 transition-all duration-500"
                                    :style="{ width: `${Math.round((Number(d.arsip_count) / divisiMax) * 100)}%` }"
                                ></div>
                            </div>
                            <span class="w-7 shrink-0 text-right text-sm font-extrabold text-gray-700">
                                {{ d.arsip_count }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent uploads + peminjaman -->
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-5">
                <!-- Recent uploads -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2">
                    <div class="mb-3 flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="text-sm font-bold text-gray-800">Upload Terbaru</h3>
                        <Link :href="routeFn('arsip.index')" class="text-xs font-semibold text-indigo-600 hover:underline">
                            Lihat Semua &rarr;
                        </Link>
                    </div>

                    <div v-if="recentUploads.length === 0" class="py-10 text-center text-sm font-medium text-gray-400">
                        Belum ada dokumen terarsip.
                    </div>

                    <ul v-else class="divide-y divide-gray-100">
                        <li v-for="u in recentUploads" :key="u.id" class="flex items-start gap-3 py-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="truncate text-sm font-semibold text-gray-800">{{ u.judul }}</p>
                                    <Badge :color="publikasiBadge(u.status_publikasi).color">
                                        {{ publikasiBadge(u.status_publikasi).label }}
                                    </Badge>
                                </div>
                                <p class="truncate text-[11px] text-gray-400">
                                    <code>{{ u.nomor_arsip }}</code>
                                    <template v-if="u.pengirim"> &bull; dari {{ u.pengirim }}</template>
                                    <template v-if="u.divisi?.name"> &bull; {{ u.divisi.name }}</template>
                                    <template v-if="u.kategori?.name"> &bull; {{ u.kategori.name }}</template>
                                </p>
                                <p class="mt-0.5 text-[10px] text-gray-400">
                                    <template v-if="u.tanggal_dokumen">{{ formatDate(u.tanggal_dokumen) }}</template>
                                    <template v-if="u.file_size"> &bull; {{ formatFileSize(u.file_size) }}</template>
                                </p>
                            </div>
                            <span class="whitespace-nowrap text-[10px] text-gray-400">{{ formatDate(u.created_at) }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Recent peminjaman -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-3">
                    <div class="mb-3 flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="text-sm font-bold text-gray-800">
                            {{ isKaryawan ? 'Riwayat Pengajuan Akses Saya' : 'Pengajuan Peminjaman Terbaru' }}
                        </h3>
                        <Link
                            :href="hasRole('Superadmin', 'Operator', 'Staf TU', 'Kaprodi', 'Dekan') ? routeFn('peminjaman.manage') : routeFn('peminjaman.index')"
                            class="text-xs font-semibold text-indigo-600 hover:underline"
                        >
                            Lihat Selengkapnya &rarr;
                        </Link>
                    </div>

                    <div v-if="recentPeminjaman.length === 0" class="py-10 text-center text-sm font-medium text-gray-400">
                        Tidak ada riwayat aktivitas peminjaman terdokumentasi.
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead>
                                <tr class="text-gray-500">
                                    <th scope="col" class="w-10 px-2 py-2 text-left text-xs font-semibold uppercase tracking-wide">No</th>
                                    <th v-if="!isKaryawan" scope="col" class="px-2 py-2 text-left text-xs font-semibold uppercase tracking-wide">Staf</th>
                                    <th scope="col" class="px-2 py-2 text-left text-xs font-semibold uppercase tracking-wide">Dokumen</th>
                                    <th scope="col" class="px-2 py-2 text-left text-xs font-semibold uppercase tracking-wide">Status</th>
                                    <th scope="col" class="px-2 py-2 text-right text-xs font-semibold uppercase tracking-wide">Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-700">
                                <tr v-for="(rp, index) in recentPeminjaman" :key="rp.id" class="transition-colors hover:bg-gray-50/55">
                                    <td class="px-2 py-3 font-medium text-gray-400">{{ index + 1 }}</td>
                                    <td v-if="!isKaryawan" class="px-2 py-3">
                                        <div class="leading-none font-bold text-gray-800">{{ rp.user?.name }}</div>
                                        <div class="mt-1 text-[10px] text-gray-400">{{ rp.user?.email }}</div>
                                    </td>
                                    <td class="px-2 py-3">
                                        <div class="max-w-[240px] truncate font-semibold text-gray-900">{{ rp.arsip?.judul }}</div>
                                        <div class="mt-1 text-[10px] text-gray-400">
                                            <code>{{ rp.arsip?.nomor_arsip }}</code>
                                            <template v-if="rp.arsip?.kategori?.name"> &bull; {{ rp.arsip.kategori.name }}</template>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-2 py-3">
                                        <Badge :color="statusBadge(rp).color">{{ statusBadge(rp).label }}</Badge>
                                    </td>
                                    <td class="whitespace-nowrap px-2 py-3 text-right font-mono text-[11px] text-gray-500">
                                        {{ formatDate(rp.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
