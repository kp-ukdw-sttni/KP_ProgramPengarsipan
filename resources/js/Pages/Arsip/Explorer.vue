<script setup>
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
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
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Jelajah Folder Arsip</h3>
                <p class="text-xs text-gray-500">
                    Navigasi: Fakultas &rarr; Program Studi &rarr; Tahun &rarr; Jenis Dokumen
                </p>
            </div>
            <Link
                :href="routeFn('arsip.index')"
                class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
            >
                Kembali ke Daftar
            </Link>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm lg:col-span-1">
                <h4 class="mb-3 px-2 text-xs font-bold uppercase tracking-wide text-gray-500">
                    Struktur Folder
                </h4>
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
