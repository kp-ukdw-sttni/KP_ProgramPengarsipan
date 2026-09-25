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
    totalPendingPeminjaman: { type: Number, default: 0 },
    totalKategori: { type: Number, default: 0 },
    totalDivisi: { type: Number, default: 0 },
    recentUploads: { type: Array, default: () => [] },
})

const { user, hasRole } = useAuth()
const routeFn = useRoute()
const { open: openUploadModal } = useUploadModal()

const firstName = computed(() => (user.value?.name ?? '').split(' ')[0])

const today = computed(() =>
    new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date()),
)

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
        Internal: { label: 'Internal', color: 'blue' },
        Terbatas: { label: 'Terbatas', color: 'amber' },
        Confidential: { label: 'Rahasia', color: 'red' },
        Rahasia: { label: 'Rahasia', color: 'red' },
    }
    return map[status] ?? { label: status ?? '-', color: 'gray' }
}

const cards = computed(() => [
    {
        label: 'Total Berkas',
        value: props.totalArsip,
        icon: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
        chip: 'bg-blue-50 text-blue-600',
        link: routeFn('arsip.index'),
    },
    {
        label: 'Persetujuan Akses Aktif',
        value: props.totalPendingPeminjaman,
        icon: 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
        chip: 'bg-amber-50 text-amber-600',
        link: hasRole('Admin', 'Superadmin') ? routeFn('peminjaman.manage') : null,
    },
    {
        label: 'Jenis Dokumen',
        value: props.totalKategori,
        icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
        chip: 'bg-indigo-50 text-indigo-600',
        link: hasRole('Admin', 'Superadmin') ? routeFn('kategori.index') : null,
    },
    {
        label: 'Unit Kerja',
        value: props.totalDivisi,
        icon: 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3a1.5 1.5 0 011.5-1.5h3a1.5 1.5 0 011.5 1.5v3',
        chip: 'bg-emerald-50 text-emerald-600',
        link: null,
    },
])
</script>

<template>
    <AuthenticatedLayout title="Dashboard">
        <FlashMessages />

        <div class="space-y-6">
            <!-- Hero Banner -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-navy via-navy-dark to-[#0A1120] p-6 text-white shadow-lg sm:p-8">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(212,175,55,0.18),transparent_55%)]"></div>
                <div class="relative flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gold-light">{{ today }}</p>
                        <h2 class="mt-1.5 text-2xl font-extrabold sm:text-3xl">
                            Selamat Datang, {{ firstName }}
                        </h2>
                        <p class="mt-1 text-sm text-blue-200">
                            Sistem Pengarsipan Dokumen Internal Kampus STTNI.
                        </p>
                    </div>

                    <!-- Quick Actions -->
                    <div class="flex flex-wrap items-center gap-2.5">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-600/40 transition hover:from-blue-700 hover:to-blue-800 hover:scale-105"
                            @click="openUploadModal"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            + Unggah Berkas Baru
                        </button>

                        <Link
                            :href="routeFn('arsip.index')"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                            Cari Dokumen
                        </Link>
                    </div>
                </div>
            </div>

            <!-- 4 Kartu Ringkasan Utama -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <component
                    :is="card.link ? Link : 'div'"
                    v-for="card in cards"
                    :key="card.label"
                    :href="card.link ?? undefined"
                    class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                    :class="card.link ? 'cursor-pointer' : ''"
                >
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl" :class="card.chip">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon" />
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <span class="block truncate text-xs font-semibold text-gray-500">{{ card.label }}</span>
                        <span class="block text-2xl font-extrabold leading-tight text-gray-900">{{ card.value }}</span>
                    </div>
                </component>
            </div>

            <!-- Tabel Ringkas: 5 Berkas Terbaru yang Diunggah -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="text-base font-extrabold text-gray-900">5 Berkas Terbaru yang Diunggah</h3>
                        <p class="text-xs text-gray-400">Daftar dokumen arsip terkini di sistem</p>
                    </div>
                    <Link :href="routeFn('arsip.index')" class="text-xs font-bold text-indigo-600 hover:underline">
                        Lihat Semua Dokumen &rarr;
                    </Link>
                </div>

                <div v-if="recentUploads.length === 0" class="py-12 text-center text-sm font-medium text-gray-400">
                    Belum ada berkas terarsip di sistem.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                                <th scope="col" class="w-12 px-3 py-3 text-left">No</th>
                                <th scope="col" class="px-4 py-3 text-left">Judul Berkas</th>
                                <th scope="col" class="px-4 py-3 text-left">Pengunggah</th>
                                <th scope="col" class="px-4 py-3 text-left">Visibilitas</th>
                                <th scope="col" class="px-4 py-3 text-right">Tanggal Upload</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            <tr v-for="(u, index) in recentUploads" :key="u.id" class="transition hover:bg-indigo-50/30">
                                <td class="px-3 py-3.5 font-semibold text-gray-400">{{ index + 1 }}</td>
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-gray-900">{{ u.judul }}</div>
                                    <div class="mt-0.5 text-[11px] text-gray-400">
                                        <code>{{ u.nomor_arsip }}</code>
                                        <template v-if="u.kategori?.name"> &bull; {{ u.kategori.name }}</template>
                                        <template v-if="u.divisi?.name"> &bull; {{ u.divisi.name }}</template>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-semibold text-gray-800">{{ u.uploader?.name ?? 'Sistem' }}</div>
                                    <div class="text-[10px] text-gray-400">{{ u.uploader?.email ?? '-' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5">
                                    <Badge :color="publikasiBadge(u.status_publikasi).color">
                                        {{ publikasiBadge(u.status_publikasi).label }}
                                    </Badge>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-right font-mono text-xs text-gray-500">
                                    {{ formatDate(u.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
