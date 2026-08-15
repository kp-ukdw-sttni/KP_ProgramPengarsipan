<script setup>
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import PageHero from '../../Components/ui/PageHero.vue'
import FolderTree from './Partials/FolderTree.vue'
import { useRoute } from '../../Composables/useRoute'

defineProps({
    tree: {
        type: Array,
        default: () => [],
    },
})

const routeFn = useRoute()
</script>

<template>
    <AuthenticatedLayout title="Jelajah Folder">
        <PageHero
            eyebrow="Arsip Dokumen"
            title="Jelajah Folder Arsip"
            :subtitle="`Navigasi hierarki: Fakultas → Program Studi → Tahun → Jenis Dokumen. ${tree.length} folder utama tersedia.`"
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

        <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm lg:col-span-1">
                <div class="mb-3 flex items-center justify-between px-2">
                    <h4 class="text-xs font-bold uppercase tracking-wide text-gray-500">
                        Struktur Folder
                    </h4>
                    <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-600">
                        {{ tree.length }}
                    </span>
                </div>
                <ul v-if="tree.length" class="space-y-0.5">
                    <FolderTree v-for="node in tree" :key="node.id" :node="node" :depth="0" />
                </ul>
                <div v-else class="py-10 text-center text-sm font-medium text-gray-400">
                    Belum ada arsip aktif.
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
                <h4 class="mb-3 text-xs font-bold uppercase tracking-wide text-gray-500">Petunjuk</h4>
                <ol class="space-y-3 text-sm text-gray-600">
                    <li class="flex items-start gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600">1</span>
                        <span>Klik folder <span class="font-semibold">Fakultas</span> untuk membuka daftar program studi.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600">2</span>
                        <span>Buka <span class="font-semibold">Program Studi</span> lalu pilih <span class="font-semibold">Tahun</span> arsip.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600">3</span>
                        <span>Klik <span class="font-semibold">jenis dokumen</span> untuk melihat daftar arsip pada folder tersebut dengan filter otomatis terpasang.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600">4</span>
                        <span>Untuk memuat ulang folder saat ada arsip baru, cukup muat ulang halaman ini.</span>
                    </li>
                </ol>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
