<script setup>
import { ref } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import PageHero from '../../Components/ui/PageHero.vue'
import Pagination from '../../Components/ui/Pagination.vue'
import Modal from '../../Components/ui/Modal.vue'
import InputError from '../../Components/ui/InputError.vue'
import { useRoute } from '../../Composables/useRoute'

const props = defineProps({
    presensi: {
        type: Object,
        required: true,
    },
})

const routeFn = useRoute()

const activeItem = ref(null)
const action = ref('approve')
const form = useForm({ catatan_reviewer: '' })

const openReview = (item, type) => {
    activeItem.value = item
    action.value = type
    form.catatan_reviewer = ''
    form.clearErrors()
}

const close = () => {
    activeItem.value = null
    form.reset()
}

const submit = () => {
    if (!activeItem.value) return
    form.post(
        routeFn(action.value === 'approve' ? 'presensi.approve' : 'presensi.reject', {
            presensi: activeItem.value.id,
        }),
        { onSuccess: close },
    )
}

const statusStyle = (status) => {
    switch (status) {
        case 'Terverifikasi':
            return 'bg-green-100 text-green-700 ring-green-600/20'
        case 'Ditolak':
            return 'bg-red-100 text-red-700 ring-red-600/20'
        default:
            return 'bg-amber-100 text-amber-700 ring-amber-600/20'
    }
}
</script>

<template>
    <AuthenticatedLayout title="Verifikasi Arsip Presensi">
        <FlashMessages />

        <PageHero
            eyebrow="Admin Pengarsipan"
            title="Review & Verifikasi Arsip Presensi"
            subtitle="Tinjau, verifikasi, atau tolak arsip rekap presensi yang masuk dari Sie Kesiswaan."
        />

        <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-navy text-xs uppercase tracking-wide text-gray-300">
                        <tr>
                            <th class="px-4 py-3 font-semibold">UKM / Kegiatan</th>
                            <th class="px-4 py-3 text-center font-semibold">Pertemuan</th>
                            <th class="px-4 py-3 font-semibold">Tanggal</th>
                            <th class="px-4 py-3 font-semibold">Nomor Arsip</th>
                            <th class="px-4 py-3 text-center font-semibold">Status</th>
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
                            <td class="px-4 py-3 text-center text-gray-600">{{ p.pertemuan_ke ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ p.tanggal_kegiatan }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ p.arsip?.nomor_arsip || '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1"
                                    :class="statusStyle(p.status_arsip)"
                                >
                                    {{ p.status_arsip }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1.5">
                                    <Link
                                        :href="routeFn('presensi.show', { presensi: p.id })"
                                        class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-100"
                                    >
                                        Tinjau
                                    </Link>
                                    <button
                                        v-if="p.status_arsip === 'Menunggu Verifikasi'"
                                        type="button"
                                        class="rounded-lg bg-green-600 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-green-700"
                                        @click="openReview(p, 'approve')"
                                    >
                                        Verifikasi
                                    </button>
                                    <button
                                        v-if="p.status_arsip === 'Menunggu Verifikasi'"
                                        type="button"
                                        class="rounded-lg bg-red-600 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-red-700"
                                        @click="openReview(p, 'reject')"
                                    >
                                        Tolak
                                    </button>
                                    <span v-else class="text-xs text-gray-400">{{ p.reviewed_at ? 'Selesai' : '-' }}</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!presensi.data.length">
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">
                                Belum ada presensi yang masuk.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Pagination :links="presensi.links" />

        <!-- Review modal -->
        <Modal :show="!!activeItem" @close="close">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900">
                    {{ action === 'approve' ? 'Verifikasi Arsip' : 'Tolak Arsip' }}
                </h3>
                <p v-if="activeItem" class="mt-1 text-sm text-gray-500">
                    {{ activeItem.ukm?.name }} &mdash; Pertemuan {{ activeItem.pertemuan_ke ?? '-' }} ({{ activeItem.tanggal_kegiatan }})
                </p>

                <div class="mt-4">
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                        {{ action === 'approve' ? 'Catatan Verifikasi (Opsional)' : 'Alasan Penolakan (Wajib)' }}
                        <span v-if="action === 'reject'" class="text-red-500">*</span>
                    </label>
                    <textarea
                        v-model="form.catatan_reviewer"
                        rows="3"
                        :placeholder="action === 'approve' ? 'Contoh: Dokumen rekap lengkap, layak diarsipkan.' : 'Contoh: Data kehadiran belum lengkap, mohon dilengkapi.'"
                        class="block w-full rounded-lg border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 focus:outline-none"
                    />
                    <InputError :message="form.errors.catatan_reviewer" class="mt-1" />
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100"
                        @click="close"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-white transition disabled:opacity-50"
                        :class="action === 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'"
                        :disabled="form.processing"
                        @click="submit"
                    >
                        {{ form.processing ? 'Memproses...' : action === 'approve' ? 'Setujui & Terverifikasi' : 'Tolak Arsip' }}
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
