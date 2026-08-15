<script setup>
import { computed, ref } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import Badge from '../../Components/ui/Badge.vue'
import Modal from '../../Components/ui/Modal.vue'
import Pagination from '../../Components/ui/Pagination.vue'
import Input from '../../Components/ui/Input.vue'
import Select from '../../Components/ui/Select.vue'
import InputError from '../../Components/ui/InputError.vue'
import PageHero from '../../Components/ui/PageHero.vue'
import PrimaryButton from '../../Components/ui/PrimaryButton.vue'
import SecondaryButton from '../../Components/ui/SecondaryButton.vue'
import DangerButton from '../../Components/ui/DangerButton.vue'
import { useRoute } from '../../Composables/useRoute'

const props = defineProps({
    categories: {
        type: Object,
        required: true,
    },
    parentCategories: {
        type: Array,
        default: () => [],
    },
})

const routeFn = useRoute()

const items = computed(() => props.categories.data ?? [])
const paginationLinks = computed(() => props.categories.links ?? [])

const createOpen = ref(false)
const deleteTarget = ref(null)
const working = ref(false)

const form = useForm({
    kode: '',
    name: '',
    deskripsi: '',
    parent_id: '',
})

const openCreate = () => {
    form.reset()
    form.clearErrors()
    createOpen.value = true
}

const submitCreate = () => {
    form.post(routeFn('kategori.store'), {
        onSuccess: () => {
            createOpen.value = false
            form.reset()
        },
    })
}

const confirmDelete = (kategori) => {
    deleteTarget.value = kategori
}

const performDelete = () => {
    if (!deleteTarget.value) return
    working.value = true
    router.delete(routeFn('kategori.destroy', deleteTarget.value.id), {
        onFinish: () => {
            working.value = false
            deleteTarget.value = null
        },
    })
}
</script>

<template>
    <AuthenticatedLayout title="Kategori Arsip">
        <FlashMessages />

        <PageHero
            eyebrow="Klasifikasi Dokumen"
            title="Kategori & Klasifikasi Dokumen"
            subtitle="Kelola kategori utama dan sub-kategori dokumen."
        >
            <template #actions>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-600/40 transition hover:from-blue-700 hover:to-blue-800"
                    @click="openCreate"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Kategori
                </button>
            </template>
        </PageHero>

        <div class="mt-5 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div v-if="items.length === 0" class="py-16 text-center">
                <p class="text-sm font-medium text-gray-500">Belum ada kategori.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Kode</th>
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Nama Kategori</th>
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Deskripsi</th>
                            <th scope="col" class="px-5 py-3.5 text-center font-bold">Sub-Kategori</th>
                            <th scope="col" class="px-5 py-3.5 text-center font-bold">Arsip</th>
                            <th scope="col" class="px-5 py-3.5 text-right font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <tr v-for="cat in items" :key="cat.id" class="transition-colors duration-150 hover:bg-indigo-50/40">
                            <td class="px-5 py-4">
                                <code class="rounded bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">{{ cat.kode }}</code>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ cat.name }}</div>
                            </td>
                            <td class="max-w-sm px-5 py-4 text-xs text-gray-500">
                                {{ cat.deskripsi ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                <Badge color="indigo">{{ cat.children?.length ?? 0 }}</Badge>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <Badge :color="(cat.arsip_count ?? 0) > 0 ? 'green' : 'gray'">
                                    {{ cat.arsip_count ?? 0 }}
                                </Badge>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link
                                        :href="routeFn('kategori.edit', cat.id)"
                                        class="rounded-md px-2 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-100"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        class="rounded-md px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50"
                                        @click="confirmDelete(cat)"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="child in (cat?.children ?? [])" :key="child.id">
                            <td class="px-5 py-4 pl-12">
                                <code class="rounded bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-500">{{ child.kode }}</code>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2 font-semibold text-gray-800">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ child.name }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-400">Sub-kategori {{ cat.name }}</td>
                            <td class="px-5 py-4 text-center text-xs text-gray-400">-</td>
                            <td class="px-5 py-4 text-center">
                                <Badge :color="(child.arsip_count ?? 0) > 0 ? 'green' : 'gray'">
                                    {{ child.arsip_count ?? 0 }}
                                </Badge>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link
                                        :href="routeFn('kategori.edit', child.id)"
                                        class="rounded-md px-2 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-100"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        class="rounded-md px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50"
                                        @click="confirmDelete(child)"
                                    >
                                        Hapus
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

        <Modal :show="createOpen" max-width="lg" @close="createOpen = false">
            <form @submit.prevent="submitCreate">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-sm font-bold text-gray-900">Tambah Kategori</h3>
                </div>
                <div class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">Kode</label>
                        <Input v-model="form.kode" type="text" placeholder="contoh: SUR, KEP" />
                        <InputError :message="form.errors.kode" />
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">Nama Kategori</label>
                        <Input v-model="form.name" type="text" placeholder="contoh: Surat Masuk" />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                            Kategori Induk <span class="font-normal text-gray-400">(opsional)</span>
                        </label>
                        <Select v-model="form.parent_id">
                            <option value="">-- Kategori Utama --</option>
                            <option v-for="p in parentCategories" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </Select>
                        <InputError :message="form.errors.parent_id" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">Deskripsi</label>
                        <textarea
                            v-model="form.deskripsi"
                            rows="2"
                            class="block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <InputError :message="form.errors.deskripsi" />
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-gray-100 px-6 py-4">
                    <SecondaryButton type="button" @click="createOpen = false">Batal</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </PrimaryButton>
                </div>
            </form>
        </Modal>

        <Modal :show="!!deleteTarget" max-width="sm" @close="deleteTarget = null">
            <div v-if="deleteTarget" class="p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-gray-900">Hapus kategori ini?</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            <span class="font-semibold">{{ deleteTarget.name }}</span> akan dihapus permanen.
                        </p>
                    </div>
                </div>
                <div class="mt-5 flex items-center justify-end gap-2">
                    <SecondaryButton type="button" @click="deleteTarget = null">Batal</SecondaryButton>
                    <DangerButton :disabled="working" @click="performDelete">
                        {{ working ? 'Menghapus...' : 'Hapus' }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
