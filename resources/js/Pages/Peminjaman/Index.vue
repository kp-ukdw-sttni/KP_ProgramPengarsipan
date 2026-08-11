<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import Badge from '../../Components/ui/Badge.vue'
import Modal from '../../Components/ui/Modal.vue'
import Pagination from '../../Components/ui/Pagination.vue'
import SecondaryButton from '../../Components/ui/SecondaryButton.vue'
import DangerButton from '../../Components/ui/DangerButton.vue'
import { useRoute } from '../../Composables/useRoute'

const props = defineProps({
    peminjaman: {
        type: Object,
        required: true,
    },
})

const routeFn = useRoute()

const items = computed(() => props.peminjaman.data ?? [])
const paginationLinks = computed(() => props.peminjaman.links ?? [])

const cancelTarget = ref(null)
const cancelling = ref(false)

const statusBadge = (p) => {
    const map = {
        Pending: { label: 'Pending', color: 'amber' },
        Approved: { label: 'Approved', color: 'green' },
        Rejected: { label: 'Ditolak', color: 'red' },
        Expired: { label: 'Expired', color: 'gray' },
    }
    return map[p.status_approval] ?? { label: p.status_approval, color: 'gray' }
}

const isActive = (p) => {
    if (p.status_approval !== 'Approved') return false
    return new Date(p.expired_at) >= new Date()
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

const confirmCancel = (p) => {
    cancelTarget.value = p
}

const performCancel = () => {
    if (!cancelTarget.value) return
    cancelling.value = true
    router.delete(routeFn('peminjaman.cancel', cancelTarget.value.id), {
        preserveScroll: true,
        onFinish: () => {
            cancelling.value = false
            cancelTarget.value = null
        },
    })
}
</script>

<template>
    <AuthenticatedLayout title="Pengajuan Akses Saya">
        <FlashMessages />

        <div class="mb-4">
            <h3 class="text-lg font-bold text-gray-900">Riwayat Pengajuan Akses</h3>
            <p class="text-xs text-gray-500">Daftar permintaan akses dokumen yang Anda ajukan.</p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div v-if="items.length === 0" class="py-16 text-center">
                <p class="text-sm font-medium text-gray-500">Belum ada pengajuan akses.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Dokumen</th>
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Status</th>
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Masa Akses</th>
                            <th scope="col" class="px-5 py-3.5 text-right font-bold">Waktu Pengajuan</th>
                            <th scope="col" class="px-5 py-3.5 text-right font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <tr v-for="p in items" :key="p.id" class="transition-colors duration-150 hover:bg-indigo-50/40">
                            <td class="max-w-xs px-5 py-4">
                                <div class="leading-tight font-semibold text-gray-900">{{ p.arsip?.judul }}</div>
                                <div class="mt-1 text-[11px] text-gray-400">
                                    <code>{{ p.arsip?.nomor_arsip }}</code>
                                    &bull; {{ p.arsip?.kategori?.name }}
                                    &bull; {{ p.arsip?.divisi?.name }}
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <Badge :color="statusBadge(p).color">
                                    {{ statusBadge(p).label }}
                                </Badge>
                            </td>
                            <td class="px-5 py-4 text-xs">
                                <span v-if="isActive(p)" class="font-semibold text-green-600">
                                    s.d. {{ formatDate(p.expired_at) }}
                                </span>
                                <span v-else-if="p.status_approval === 'Approved'" class="italic text-gray-400">Telah berakhir</span>
                                <span v-else class="italic text-gray-400">-</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right font-mono text-xs text-gray-500">
                                {{ formatDate(p.created_at) }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <button
                                    v-if="p.status_approval === 'Pending'"
                                    type="button"
                                    class="rounded-md px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50"
                                    @click="confirmCancel(p)"
                                >
                                    Batalkan
                                </button>
                                <span v-else class="text-xs text-gray-300">-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="paginationLinks.length > 3" class="border-t border-gray-100 px-4 py-3">
                <Pagination :links="paginationLinks" />
            </div>
        </div>

        <Modal :show="!!cancelTarget" max-width="sm" @close="cancelTarget = null">
            <div v-if="cancelTarget" class="p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-gray-900">Batalkan pengajuan?</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            Pengajuan akses untuk <span class="font-semibold">{{ cancelTarget.arsip?.judul }}</span>
                            akan dibatalkan.
                        </p>
                    </div>
                </div>
                <div class="mt-5 flex items-center justify-end gap-2">
                    <SecondaryButton type="button" @click="cancelTarget = null">Tutup</SecondaryButton>
                    <DangerButton :disabled="cancelling" @click="performCancel">
                        {{ cancelling ? 'Membatalkan...' : 'Ya, Batalkan' }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
