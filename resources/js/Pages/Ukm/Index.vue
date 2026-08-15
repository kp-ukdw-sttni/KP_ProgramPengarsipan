<script setup>
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import PageHero from '../../Components/ui/PageHero.vue'
import Pagination from '../../Components/ui/Pagination.vue'
import { useRoute } from '../../Composables/useRoute'

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

const destroy = (item) => {
    if (!confirm(`Hapus UKM "${item.name}" beserta seluruh anggotanya?`)) return
    router.delete(routeFn('ukm.destroy', { ukm: item.id }))
}
</script>

<template>
    <AuthenticatedLayout title="Kelola UKM">
        <FlashMessages />

        <PageHero
            eyebrow="Master Data"
            title="Kelola Unit Kegiatan Mahasiswa (UKM)"
            subtitle="Kelola daftar UKM beserta anggota yang akan diambil presensinya."
        >
            <template #actions>
                <Link
                    :href="routeFn('ukm.create')"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-gold to-yellow-500 px-4 py-2.5 text-sm font-bold text-navy shadow-lg transition hover:scale-105"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah UKM
                </Link>
            </template>
        </PageHero>

        <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-navy text-xs uppercase tracking-wide text-gray-300">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Kode</th>
                            <th class="px-4 py-3 font-semibold">Nama UKM</th>
                            <th class="px-4 py-3 font-semibold">Pembina</th>
                            <th class="px-4 py-3 font-semibold">Ketua</th>
                            <th class="px-4 py-3 text-center font-semibold">Anggota</th>
                            <th class="px-4 py-3 text-center font-semibold">Sesi Presensi</th>
                            <th class="px-4 py-3 text-center font-semibold">Status</th>
                            <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="u in ukm.data" :key="u.id" class="transition hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-600">{{ u.kode }}</td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900">{{ u.name }}</p>
                                <p v-if="u.divisi" class="text-[11px] text-gray-400">{{ u.divisi.name }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ u.pembina || '-' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ u.ketua || '-' }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-gray-800">{{ u.anggota_count }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-gray-800">{{ u.presensi_count }}</td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1"
                                    :class="u.status === 'Aktif' ? 'bg-green-100 text-green-700 ring-green-600/20' : 'bg-gray-100 text-gray-500 ring-gray-400/20'"
                                >
                                    {{ u.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1.5">
                                    <Link
                                        :href="routeFn('ukm.edit', { ukm: u.id })"
                                        class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-100"
                                    >
                                        Kelola
                                    </Link>
                                    <button
                                        type="button"
                                        class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                                        @click="destroy(u)"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!ukm.data.length">
                            <td colspan="8" class="px-4 py-12 text-center text-sm text-gray-500">
                                Belum ada UKM terdaftar.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Pagination :links="ukm.links" />
    </AuthenticatedLayout>
</template>
