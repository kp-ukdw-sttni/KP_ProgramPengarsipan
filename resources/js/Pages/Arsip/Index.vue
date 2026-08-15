<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import Badge from '../../Components/ui/Badge.vue'
import Modal from '../../Components/ui/Modal.vue'
import Pagination from '../../Components/ui/Pagination.vue'
import PrimaryButton from '../../Components/ui/PrimaryButton.vue'
import SecondaryButton from '../../Components/ui/SecondaryButton.vue'
import DangerButton from '../../Components/ui/DangerButton.vue'
import PageHero from '../../Components/ui/PageHero.vue'
import PdfPreviewModal from '../../Components/PdfPreviewModal.vue'
import ArsipFilterBar from './Partials/ArsipFilterBar.vue'
import { useAuth } from '../../Composables/useAuth'
import { useRoute } from '../../Composables/useRoute'
import { useUploadFeedback } from '../../Composables/useUploadFeedback'
import { useUploadModal } from '../../Composables/useUploadModal'

const props = defineProps({
    arsip: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    kategori: {
        type: Array,
        default: () => [],
    },
    kategoriTree: {
        type: Array,
        default: () => [],
    },
    divisiTree: {
        type: Array,
        default: () => [],
    },
    studyPrograms: {
        type: Array,
        default: () => [],
    },
    tahunList: {
        type: Array,
        default: () => [],
    },
    activePeminjaman: {
        type: Object,
        default: () => ({}),
    },
})

const { user, hasRole, canManageArsip, canViewFile } = useAuth()
const routeFn = useRoute()
const { start: feedbackStart, success: feedbackSuccess, error: feedbackError } = useUploadFeedback()
const { open: openUploadModal } = useUploadModal()

const items = computed(() => props.arsip.data ?? [])
const paginationLinks = computed(() => props.arsip.links ?? [])

const previewArsip = ref(null)
const deleteArsip = ref(null)
const deleting = ref(false)

const canRequest = computed(() => hasRole('Karyawan', 'Mahasiswa', 'Dosen'))

const isPdf = (arsip) => (arsip.file_path ?? '').toLowerCase().endsWith('.pdf')

const formatDate = (value) => {
    if (!value) return '-'
    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return '-'
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date)
}

const publikasiBadge = (arsip) => {
    const map = {
        Public: { label: 'Public', color: 'green' },
        Internal: { label: 'Internal', color: 'blue' },
        Confidential: { label: 'Confidential', color: 'red' },
    }
    return map[arsip.status_publikasi] ?? { label: arsip.status_publikasi ?? '-', color: 'gray' }
}

const statusBadge = (arsip) => {
    const map = {
        Aktif: { label: 'Aktif', color: 'green' },
        Inaktif: { label: 'Inaktif', color: 'amber' },
        Diarsipkan: { label: 'Diarsipkan', color: 'blue' },
        Dimusnahkan: { label: 'Dimusnahkan', color: 'gray' },
    }
    return map[arsip.status] ?? { label: arsip.status, color: 'gray' }
}

const myRequest = (arsip) => props.activePeminjaman[arsip.id] ?? null

const viewArsip = (arsip) => {
    if (!canViewFile(arsip)) return
    if (isPdf(arsip)) {
        previewArsip.value = arsip
    } else {
        window.open(routeFn('arsip.stream', arsip.id), '_blank')
    }
}

const downloadArsip = (arsip) => {
    if (!canViewFile(arsip)) return
    window.location.href = routeFn('arsip.download', arsip.id)
}

const requestAccess = (arsip) => {
    router.post(routeFn('peminjaman.request', arsip.id), {}, {
        preserveScroll: true,
    })
}

const confirmDelete = (arsip) => {
    deleteArsip.value = arsip
}

const performDelete = () => {
    if (!deleteArsip.value) return
    deleting.value = true
    feedbackStart('Menghapus arsip...')
    router.delete(routeFn('arsip.destroy', deleteArsip.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            feedbackSuccess('Arsip berhasil dipindahkan ke Recycle Bin.')
        },
        onError: () => {
            feedbackError('Gagal menghapus arsip')
        },
        onFinish: () => {
            deleting.value = false
            deleteArsip.value = null
        },
    })
}
</script>

<template>
    <AuthenticatedLayout title="Daftar Arsip">
        <FlashMessages />

        <PageHero
            eyebrow="Arsip Dokumen"
            title="Dokumen Tersimpan"
            :subtitle="`${props.arsip.total ?? 0} arsip ditemukan`"
        >
            <template #actions>
                <div v-if="hasRole('Superadmin', 'Operator', 'Staf TU')" class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-600/40 transition hover:from-blue-700 hover:to-blue-800"
                        @click="openUploadModal"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Arsipkan Dokumen
                    </button>
                    <Link
                        :href="routeFn('arsip.explorer')"
                        class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20"
                    >
                        Jelajah Folder
                    </Link>
                </div>
            </template>
        </PageHero>

        <ArsipFilterBar
            class="mt-5"
            :filters="filters"
            :kategori="kategori"
            :divisi-tree="divisiTree"
            :study-programs="studyPrograms"
            :tahun-list="tahunList"
        />

        <div class="mt-4 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div v-if="items.length === 0" class="py-16 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50">
                    <svg class="h-7 w-7 text-indigo-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <p class="mt-4 text-sm font-semibold text-gray-600">Tidak ada arsip yang cocok dengan filter.</p>
                <p class="mt-1 text-xs text-gray-400">Coba ubah kata kunci pencarian atau hapus filter yang aktif.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Dokumen</th>
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Klasifikasi</th>
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Tahun</th>
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Status</th>
                            <th scope="col" class="px-5 py-3.5 text-right font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <tr
                            v-for="item in items"
                            :key="item.id"
                            class="transition-colors duration-150 hover:bg-indigo-50/40"
                        >
                            <td class="max-w-xs px-5 py-4 align-middle">
                                <button
                                    v-if="canViewFile(item)"
                                    type="button"
                                    class="block w-full text-left leading-tight font-semibold text-gray-900 hover:text-indigo-600 hover:underline"
                                    @click="viewArsip(item)"
                                >
                                    {{ item.judul }}
                                </button>
                                <span v-else class="block leading-tight font-semibold text-gray-900">{{ item.judul }}</span>
                                <div class="mt-1 flex flex-wrap items-center gap-x-2 text-[11px] text-gray-400">
                                    <code>{{ item.nomor_arsip }}</code>
                                    <template v-if="item.nomor_surat">
                                        <span>&bull;</span>
                                        <span>{{ item.nomor_surat }}</span>
                                    </template>
                                    <span v-if="item.tags">&bull; {{ item.tags }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 align-middle">
                                <div class="leading-tight text-xs font-semibold text-gray-700">
                                    {{ item.kategori?.name ?? '-' }}
                                </div>
                                <div class="mt-1 text-[11px] text-gray-400">{{ item.divisi?.name ?? '-' }}</div>
                                <div v-if="item.study_program_id" class="mt-1 text-[11px] font-medium text-indigo-500">
                                    {{ item.study_program?.name ?? '-' }}
                                </div>
                            </td>
                            <td class="px-5 py-4 align-middle text-xs font-semibold text-gray-600">
                                {{ item.tahun ?? '-' }}
                            </td>
                            <td class="px-5 py-4 align-middle">
                                <div class="flex flex-col items-start gap-1.5">
                                    <Badge :color="statusBadge(item).color">
                                        {{ statusBadge(item).label }}
                                    </Badge>
                                    <Badge :color="publikasiBadge(item).color">
                                        {{ publikasiBadge(item).label }}
                                    </Badge>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 align-middle text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        v-if="canViewFile(item)"
                                        type="button"
                                        class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-50"
                                        @click="viewArsip(item)"
                                    >
                                        Lihat
                                    </button>
                                    <button
                                        v-if="canViewFile(item)"
                                        type="button"
                                        class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-gray-600 transition hover:bg-gray-100"
                                        @click="downloadArsip(item)"
                                    >
                                        Unduh
                                    </button>

                                    <button
                                        v-if="canRequest && !canViewFile(item) && !myRequest(item)"
                                        type="button"
                                        class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-50"
                                        @click="requestAccess(item)"
                                    >
                                        Minta Akses
                                    </button>
                                    <span
                                        v-else-if="canRequest && myRequest(item)"
                                        class="rounded-lg px-2.5 py-1.5 text-xs font-bold"
                                        :class="myRequest(item).status_approval === 'Approved'
                                            ? 'text-green-600'
                                            : 'text-amber-600'"
                                    >
                                        {{ myRequest(item).status_approval }}
                                    </span>

                                    <Link
                                        v-if="canManageArsip(item)"
                                        :href="routeFn('arsip.edit', item.id)"
                                        class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-gray-600 transition hover:bg-gray-100"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        v-if="canManageArsip(item)"
                                        type="button"
                                        class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                                        @click="confirmDelete(item)"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="paginationLinks.length > 3" class="border-t border-gray-100 px-5 py-3.5">
                <Pagination :links="paginationLinks" />
            </div>
        </div>

        <PdfPreviewModal :arsip="previewArsip" @close="previewArsip = null" />

        <Modal :show="!!deleteArsip" max-width="sm" @close="deleteArsip = null">
            <div v-if="deleteArsip" class="p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-gray-900">Hapus arsip ini?</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            <span class="font-semibold">{{ deleteArsip.judul }}</span> akan dipindahkan ke Recycle Bin.
                            Anda dapat memulihkannya kembali dari sana.
                        </p>
                    </div>
                </div>
                <div class="mt-5 flex items-center justify-end gap-2">
                    <SecondaryButton type="button" @click="deleteArsip = null">Batal</SecondaryButton>
                    <DangerButton :disabled="deleting" @click="performDelete">
                        {{ deleting ? 'Menghapus...' : 'Hapus Arsip' }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
