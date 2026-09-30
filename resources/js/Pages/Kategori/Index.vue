<script setup>
import { computed, ref } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import Badge from '../../Components/ui/Badge.vue'
import Modal from '../../Components/ui/Modal.vue'
import Pagination from '../../Components/ui/Pagination.vue'
import Input from '../../Components/ui/Input.vue'
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
})

const routeFn = useRoute()

const items = computed(() => props.categories.data ?? [])
const paginationLinks = computed(() => props.categories.links ?? [])

const createOpen = ref(false)
const step = ref(1)
const parentKategori = ref(null)
const subNames = ref([''])
const createError = ref('')
const deleteTarget = ref(null)
const working = ref(false)

const form = useForm({
    kode: '',
    name: '',
    deskripsi: '',
})

const filledSubNames = computed(() => subNames.value.map((n) => n.trim()).filter((n) => n !== ''))

const xsrfToken = () => {
    const row = document.cookie.split('; ').find((c) => c.startsWith('XSRF-TOKEN='))

    return row ? decodeURIComponent(row.split('=').slice(1).join('=')) : ''
}

const openCreate = () => {
    form.reset()
    form.clearErrors()
    createError.value = ''
    parentKategori.value = null
    subNames.value = ['']
    step.value = 1
    createOpen.value = true
}

const resetCreate = () => {
    createOpen.value = false
    step.value = 1
    parentKategori.value = null
    subNames.value = ['']
    createError.value = ''
    form.reset()
    form.clearErrors()
}

const closeCreate = () => {
    // After step one the category only exists in the database, not in the
    // table yet, so pull fresh props whenever something was already created.
    const hasNewCategory = parentKategori.value !== null
    resetCreate()

    if (hasNewCategory) {
        router.reload({ only: ['categories'] })
    }
}

const addSubRow = () => {
    subNames.value.push('')
}

const removeSubRow = (index) => {
    subNames.value.splice(index, 1)

    if (subNames.value.length === 0) {
        subNames.value.push('')
    }
}

const submitCategory = async () => {
    createError.value = ''
    form.clearErrors()

    try {
        const response = await fetch(routeFn('kategori.store'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': xsrfToken(),
            },
            body: JSON.stringify({
                kode: form.kode,
                name: form.name,
                deskripsi: form.deskripsi || null,
            }),
        })

        if (response.status === 422) {
            const payload = await response.json()
            const fields = Object.keys(payload.errors ?? {})
            const field = fields.find((key) => key in form) ?? 'name'
            form.setError(field, payload.errors?.[field]?.[0] ?? 'Data tidak valid.')
            return
        }

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`)
        }

        parentKategori.value = await response.json()
        subNames.value = ['']
        step.value = 2
    } catch (e) {
        createError.value = 'Gagal menyimpan kategori. Silakan coba lagi.'
    }
}

const submitSubCategories = () => {
    if (filledSubNames.value.length === 0) return

    router.post(
        routeFn('kategori.children.store', parentKategori.value?.id),
        { children: filledSubNames.value },
        {
            // Inertia already swapped in fresh props with the redirect, so just
            // tear the wizard down instead of asking for them again.
            onSuccess: () => resetCreate(),
            onError: (errors) => {
                createError.value = Object.values(errors)[0] ?? 'Sub-kategori gagal disimpan.'
            },
        },
    )
}

const confirmDelete = (kategori) => {
    deleteTarget.value = kategori
}

const deleteTargetChildCount = computed(() => deleteTarget.value?.children?.length ?? 0)

const performDelete = () => {
    if (!deleteTarget.value) return
    working.value = true
    router.delete(routeFn('kategori.destroy', deleteTarget.value.id), {
        // The service refuses to drop sub-categories silently, so the cascade is
        // only requested once the modal has listed what would disappear.
        data: { cascade: deleteTargetChildCount.value > 0 },
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
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-600/40 transition hover:bg-blue-700"
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
                        <template v-for="cat in items" :key="cat.id">
                        <tr class="transition-colors duration-150 hover:bg-indigo-50/40">
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
                        <tr v-for="child in (cat.children ?? [])" :key="child.id">
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
                        </template>
                    </tbody>
                </table>
            </div>

            <div v-if="paginationLinks.length > 3" class="border-t border-gray-100 px-4 py-3">
                <Pagination :links="paginationLinks" />
            </div>
        </div>

        <Modal :show="createOpen" max-width="lg" @close="closeCreate">
            <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-xl">
                <div class="shrink-0 border-b border-gray-100 px-6 py-4">
                    <h3 class="text-sm font-bold text-gray-900">
                        {{ step === 1 ? 'Tambah Kategori' : 'Tambah Sub-kategori' }}
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        <template v-if="step === 1">
                            Isi kategori utama terlebih dahulu, lalu lanjutkan ke sub-kategori.
                        </template>
                        <template v-else>
                            Untuk kategori
                            <span class="font-semibold text-gray-700">{{ parentKategori?.name }}</span>.
                            Kode sub-kategori dibuat otomatis dari nama.
                        </template>
                    </p>
                    <ol class="mt-3 flex items-center gap-2 text-[11px] font-semibold">
                        <li
                            v-for="(label, i) in ['Kategori Utama', 'Sub-kategori']"
                            :key="label"
                            class="flex items-center gap-1.5"
                            :class="(step === i + 1) ? 'text-blue-600' : (step > i + 1 ? 'text-green-600' : 'text-gray-400')"
                        >
                            <span
                                class="flex h-5 w-5 items-center justify-center rounded-full text-[10px]"
                                :class="step > i + 1 ? 'bg-green-100' : (step === i + 1 ? 'bg-blue-100' : 'bg-gray-100')"
                            >
                                {{ step > i + 1 ? '\u2713' : i + 1 }}
                            </span>
                            {{ label }}
                        </li>
                    </ol>
                </div>

                <div v-if="createError" class="shrink-0 border-b border-red-100 bg-red-50 px-6 py-3 text-xs font-medium text-red-700">
                    {{ createError }}
                </div>

                <form v-if="step === 1" class="flex min-h-0 flex-1 flex-col" @submit.prevent="submitCategory">
                    <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                                    Deskripsi <span class="font-normal text-gray-400">(opsional)</span>
                                </label>
                                <textarea
                                    v-model="form.deskripsi"
                                    rows="2"
                                    class="block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <InputError :message="form.errors.deskripsi" />
                            </div>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center justify-end gap-2 border-t border-gray-100 px-6 py-4">
                        <SecondaryButton type="button" @click="closeCreate">Batal</SecondaryButton>
                        <PrimaryButton type="submit">Simpan &amp; Lanjut</PrimaryButton>
                    </div>
                </form>

                <form v-else class="flex min-h-0 flex-1 flex-col" @submit.prevent="submitSubCategories">
                    <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
                        <p v-if="filledSubNames.length === 0" class="mb-4 text-xs text-gray-500">
                            Belum ada sub-kategori. Tambahkan minimal satu, atau selesaikan tanpa sub-kategori.
                        </p>
                        <div class="space-y-3">
                            <div v-for="(subName, i) in subNames" :key="i" class="flex items-start gap-2">
                                <div class="flex-1">
                                    <label class="mb-1 block text-[11px] font-semibold text-gray-500">Nama Sub-kategori {{ i + 1 }}</label>
                                    <Input v-model="subNames[i]" type="text" placeholder="contoh: Surat Dinas" />
                                </div>
                                <button
                                    type="button"
                                    class="mt-6 rounded-md p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-600"
                                    title="Hapus baris"
                                    @click="removeSubRow(i)"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700"
                            @click="addSubRow"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah baris
                        </button>
                    </div>
                    <div class="flex shrink-0 flex-wrap items-center justify-end gap-2 border-t border-gray-100 px-6 py-4">
                        <SecondaryButton type="button" @click="closeCreate">Selesai Tanpa Sub-kategori</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="filledSubNames.length === 0">
                            Simpan Sub-kategori
                        </PrimaryButton>
                    </div>
                </form>
            </div>
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

                <div
                    v-if="deleteTargetChildCount > 0"
                    class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5"
                >
                    <p class="text-xs font-semibold text-amber-900">
                        {{ deleteTargetChildCount }} sub-kategori ikut terhapus:
                    </p>
                    <ul class="mt-1.5 space-y-0.5">
                        <li
                            v-for="child in deleteTarget.children"
                            :key="child.id"
                            class="text-[11px] text-amber-800"
                        >
                            &bull; {{ child.name }}
                        </li>
                    </ul>
                </div>

                <p
                    v-else-if="(deleteTarget.arsip_count ?? 0) > 0"
                    class="mt-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-xs font-medium text-red-700"
                >
                    Kategori ini memiliki {{ deleteTarget.arsip_count }} arsip sehingga tidak dapat dihapus.
                </p>

                <div class="mt-5 flex items-center justify-end gap-2">
                    <SecondaryButton type="button" @click="deleteTarget = null">Batal</SecondaryButton>
                    <DangerButton :disabled="working || (deleteTarget.arsip_count ?? 0) > 0" @click="performDelete">
                        {{ working ? 'Menghapus...' : 'Hapus' }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
