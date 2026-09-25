<script setup>
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import PageHero from '../../Components/ui/PageHero.vue'
import Pagination from '../../Components/ui/Pagination.vue'
import { useRoute } from '../../Composables/useRoute'
import { useAuth } from '../../Composables/useAuth'

const props = defineProps({
    presensi: {
        type: Object,
        required: true,
    },
    ukmList: {
        type: Array,
        default: () => [],
    },
    selectedUkmId: {
        type: [String, Number],
        default: '',
    },
})

const routeFn = useRoute()
const { hasRole } = useAuth()

const filterByUkm = (id) => {
    router.get(
        routeFn('presensi.index'),
        { ukm_id: id || undefined },
        { preserveState: true, replace: true }
    )
}

const openPdf = (id) => window.open(routeFn('presensi.pdf', { presensi: id }), '_blank')
const openExcel = (id) => window.open(routeFn('presensi.excel', { presensi: id }), '_blank')
</script>

<template>
    <AuthenticatedLayout title="Presensi UKM">
        <FlashMessages />

        <PageHero
            eyebrow="Presensi UKM"
            title="Daftar Presensi & Rekap"
            subtitle="Kelola sesi presensi UKM dan unduh rekap kehadiran per kegiatan/UKM."
        >
            <template #actions>
                <Link
                    :href="routeFn('presensi.create')"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-gold to-yellow-500 px-4 py-2.5 text-sm font-bold text-navy shadow-lg transition hover:scale-105"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Isi Presensi Baru
                </Link>
            </template>
        </PageHero>

        <!-- Filter Tab Berdasarkan UKM -->
        <div class="mt-6 flex flex-wrap items-center gap-2 rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
            <span class="px-2 text-xs font-bold uppercase tracking-wider text-gray-400">Pilih UKM:</span>
            <button
                type="button"
                class="rounded-xl px-4 py-2 text-xs font-bold transition"
                :class="!selectedUkmId ? 'bg-navy text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                @click="filterByUkm('')"
            >
                Semua UKM
            </button>
            <button
                v-for="u in ukmList"
                :key="u.id"
                type="button"
                class="rounded-xl px-4 py-2 text-xs font-bold transition"
                :class="String(selectedUkmId) === String(u.id) ? 'bg-navy text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                @click="filterByUkm(u.id)"
            >
                {{ u.name }}
            </button>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-navy text-xs uppercase tracking-wide text-gray-300">
                        <tr>
                            <th class="px-4 py-3 font-semibold">UKM / Kegiatan</th>
                            <th class="px-4 py-3 text-center font-semibold">Pertemuan</th>
                            <th class="px-4 py-3 font-semibold">Tanggal</th>
                            <th class="px-4 py-3 text-center font-semibold">Hadir (Anggota)</th>
                            <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="p in presensi.data" :key="p.id" class="transition hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900">{{ p.ukm?.name }}</p>
                                <p class="text-xs text-gray-500">{{ p.judul_kegiatan || '-' }}</p>
                                <p class="mt-0.5 text-[11px] text-gray-400">oleh {{ p.pengisi?.name }}</p>
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-blue-600">Pertemuan {{ p.pertemuan_ke ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ p.tanggal_kegiatan }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-gray-800">{{ p.details_count }} Anggota</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1.5">
                                    <Link
                                        :href="routeFn('presensi.show', { presensi: p.id })"
                                        class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-100"
                                    >
                                        Detail
                                    </Link>
                                    <button
                                        type="button"
                                        title="Unduh PDF"
                                        class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                                        @click="openPdf(p.id)"
                                    >
                                        PDF
                                    </button>
                                    <button
                                        type="button"
                                        title="Unduh Excel"
                                        class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs font-semibold text-green-600 transition hover:bg-green-50"
                                        @click="openExcel(p.id)"
                                    >
                                        Excel
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!presensi.data.length">
                            <td colspan="5" class="px-4 py-12 text-center text-sm text-gray-500">
                                Belum ada presensi. Klik
                                <Link :href="routeFn('presensi.create')" class="font-semibold text-indigo-600 hover:underline">
                                    Isi Presensi Baru
                                </Link>
                                untuk mulai.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Pagination :links="presensi.links" />
    </AuthenticatedLayout>
</template>
