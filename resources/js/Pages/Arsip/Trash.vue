<script setup>
import { computed, ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import Badge from '../../Components/ui/Badge.vue'
import PageHero from '../../Components/ui/PageHero.vue'
import Modal from '../../Components/ui/Modal.vue'
import Pagination from '../../Components/ui/Pagination.vue'
import SecondaryButton from '../../Components/ui/SecondaryButton.vue'
import DangerButton from '../../Components/ui/DangerButton.vue'
import { useAuth } from '../../Composables/useAuth'
import { useRoute } from '../../Composables/useRoute'
import { useUploadFeedback } from '../../Composables/useUploadFeedback'

const props = defineProps({
    arsip: {
        type: Object,
        required: true,
    },
})

const routeFn = useRoute()
const { hasRole } = useAuth()
const { start: feedbackStart, success: feedbackSuccess, error: feedbackError } = useUploadFeedback()

const items = computed(() => props.arsip.data ?? [])
const paginationLinks = computed(() => props.arsip.links ?? [])

const isSuperadmin = computed(() => hasRole('Superadmin'))

const forceDeleteTarget = ref(null)
const working = ref(false)

const restoreArsip = (arsip) => {
    feedbackStart('Memulihkan arsip...')
    router.post(
        routeFn('arsip.restore', arsip.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                feedbackSuccess('Arsip berhasil dipulihkan dari Recycle Bin.')
            },
            onError: () => {
                feedbackError('Gagal memulihkan arsip')
            },
        },
    )
}

const confirmForceDelete = (arsip) => {
    forceDeleteTarget.value = arsip
}

const performForceDelete = () => {
    if (!forceDeleteTarget.value) return
    working.value = true
    feedbackStart('Menghapus arsip permanen...')
    router.delete(routeFn('arsip.force_delete', forceDeleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            feedbackSuccess('Arsip berhasil dihapus permanen dari server.')
        },
        onError: () => {
            feedbackError('Gagal menghapus arsip')
        },
        onFinish: () => {
            working.value = false
            forceDeleteTarget.value = null
        },
    })
}

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
</script>

<template>
    <AuthenticatedLayout title="Recycle Bin">
        <FlashMessages />

        <PageHero
            eyebrow="Arsip Dokumen"
            title="Recycle Bin"
            subtitle="Arsip yang dihapus sementara dapat dipulihkan."
        >
            <template #actions>
                <Link
                    :href="routeFn('arsip.index')"
                    class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Daftar Arsip
                </Link>
            </template>
        </PageHero>

        <div class="mt-5 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div v-if="items.length === 0" class="py-16 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <p class="mt-3 text-sm font-medium text-gray-500">Recycle Bin kosong.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Dokumen</th>
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Divisi</th>
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Tahun</th>
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Dihapus</th>
                            <th scope="col" class="px-5 py-3.5 text-right font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <tr v-for="item in items" :key="item.id" class="transition-colors duration-150 hover:bg-indigo-50/40">
                            <td class="max-w-xs px-5 py-4">
                                <div class="leading-tight font-semibold text-gray-900">{{ item.judul }}</div>
                                <div class="mt-1 text-[11px] text-gray-400">
                                    <code>{{ item.nomor_arsip }}</code>
                                    &bull; {{ item.kategori?.name ?? '-' }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600">{{ item.divisi?.name ?? '-' }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600">{{ item.tahun ?? '-' }}</td>
                            <td class="px-5 py-4 text-xs text-gray-500">{{ formatDate(item.deleted_at) }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button
                                        type="button"
                                        class="rounded-md px-2 py-1 text-xs font-semibold text-green-600 hover:bg-green-50"
                                        @click="restoreArsip(item)"
                                    >
                                        Pulihkan
                                    </button>
                                    <button
                                        v-if="isSuperadmin"
                                        type="button"
                                        class="rounded-md px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50"
                                        @click="confirmForceDelete(item)"
                                    >
                                        Hapus Permanen
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="paginationLinks.length > 3" class="border-t border-gray-100 px-4 py-3">
                <Pagination :links="paginationLinks" />
            </div>
        </div>

        <Modal :show="!!forceDeleteTarget" max-width="sm" @close="forceDeleteTarget = null">
            <div v-if="forceDeleteTarget" class="p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-gray-900">Hapus permanen?</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            <span class="font-semibold">{{ forceDeleteTarget.judul }}</span> dan seluruh versinya
                            akan dihapus permanen dari server. Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                </div>
                <div class="mt-5 flex items-center justify-end gap-2">
                    <SecondaryButton type="button" @click="forceDeleteTarget = null">Batal</SecondaryButton>
                    <DangerButton :disabled="working" @click="performForceDelete">
                        {{ working ? 'Menghapus...' : 'Ya, Hapus Permanen' }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
