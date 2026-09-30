<script setup>
import { computed, ref } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import Input from '../../Components/ui/Input.vue'
import InputError from '../../Components/ui/InputError.vue'
import PageHero from '../../Components/ui/PageHero.vue'
import PrimaryButton from '../../Components/ui/PrimaryButton.vue'
import SecondaryButton from '../../Components/ui/SecondaryButton.vue'
import DangerButton from '../../Components/ui/DangerButton.vue'
import Modal from '../../Components/ui/Modal.vue'
import Badge from '../../Components/ui/Badge.vue'
import { useRoute } from '../../Composables/useRoute'

const props = defineProps({
    kategori: {
        type: Object,
        required: true,
    },
    parent: {
        type: Object,
        default: null,
    },
    children: {
        type: Array,
        default: () => [],
    },
})

const routeFn = useRoute()

const form = useForm({
    kode: props.kategori.kode ?? '',
    name: props.kategori.name ?? '',
    deskripsi: props.kategori.deskripsi ?? '',
})

const isSubCategory = computed(() => props.kategori.parent_id !== null)

const subNames = ref([''])
const subError = ref('')

const filledSubNames = computed(() =>
    subNames.value.map((n) => n.trim()).filter((n) => n !== ''),
)

const addSubRow = () => {
    subNames.value.push('')
}

const removeSubRow = (index) => {
    subNames.value.splice(index, 1)

    if (subNames.value.length === 0) {
        subNames.value.push('')
    }
}

const submit = () => {
    form.put(routeFn('kategori.update', props.kategori.id))
}

const submitSubCategories = () => {
    if (filledSubNames.value.length === 0) return

    subError.value = ''

    router.post(
        routeFn('kategori.children.store', props.kategori.id),
        { children: filledSubNames.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                subNames.value = ['']
            },
            onError: (errors) => {
                subError.value =
                    Object.values(errors)[0] ?? 'Sub-kategori gagal disimpan.'
            },
        },
    )
}

const deleteTarget = ref(null)
const deleteWorking = ref(false)

const deleteTargetChildCount = computed(() =>
    deleteTarget.value?.id === props.kategori.id ? props.children.length : 0,
)

const performDelete = () => {
    if (!deleteTarget.value) return
    deleteWorking.value = true

    router.delete(routeFn('kategori.destroy', deleteTarget.value.id), {
        data: { cascade: deleteTargetChildCount.value > 0 },
        onSuccess: () => {
            // Deleting the category we are editing leaves this page pointing at a
            // row that no longer exists, so leave for the listing.
            if (deleteTarget.value?.id === props.kategori.id) {
                router.visit(routeFn('kategori.index'))
            }
        },
        onFinish: () => {
            deleteWorking.value = false
            deleteTarget.value = null
        },
    })
}
</script>

<template>
    <AuthenticatedLayout title="Edit Kategori">
        <FlashMessages />

        <PageHero
            eyebrow="Klasifikasi Dokumen"
            title="Edit Kategori"
            subtitle="Perbarui detail kategori dokumen."
        >
            <template #actions>
                <Link
                    :href="routeFn('kategori.index')"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Daftar
                </Link>
            </template>
        </PageHero>

        <div class="mx-auto mt-6 max-w-2xl space-y-6">
            <form @submit.prevent="submit" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div
                    class="mb-5 flex flex-wrap items-center gap-2 rounded-xl bg-gray-50 px-4 py-3 text-xs"
                >
                    <span class="font-semibold text-gray-500">Jenis</span>
                    <template v-if="isSubCategory">
                        <span class="rounded-md bg-indigo-100 px-2 py-0.5 font-bold text-indigo-700">
                            Sub-kategori
                        </span>
                        <span class="text-gray-500">dari</span>
                        <span class="font-bold text-gray-900">{{ parent?.name ?? '-' }}</span>
                    </template>
                    <span v-else class="rounded-md bg-blue-100 px-2 py-0.5 font-bold text-blue-700">
                        Kategori Utama
                    </span>
                    <span class="ml-auto text-gray-400">
                        {{ kategori.arsip_count ?? 0 }} arsip
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">Kode</label>
                        <Input v-model="form.kode" type="text" />
                        <InputError :message="form.errors.kode" />
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">Nama Kategori</label>
                        <Input v-model="form.name" type="text" />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">Deskripsi</label>
                        <textarea
                            v-model="form.deskripsi"
                            rows="3"
                            class="block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <InputError :message="form.errors.deskripsi" />
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-2">
                    <Link :href="routeFn('kategori.index')">
                        <SecondaryButton type="button">Batal</SecondaryButton>
                    </Link>
                    <PrimaryButton :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </PrimaryButton>
                </div>
            </form>

            <div v-if="!isSubCategory" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-baseline justify-between gap-3">
                    <h3 class="text-sm font-bold text-gray-900">Sub-kategori</h3>
                    <span class="text-xs text-gray-500">{{ children.length }} sub-kategori</span>
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    Kode sub-kategori dibuat otomatis dari nama.
                </p>

                <ul v-if="children.length > 0" class="mt-4 space-y-1.5">
                    <li
                        v-for="child in children"
                        :key="child.id"
                        class="flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-sm"
                    >
                        <code class="rounded bg-white px-1.5 py-0.5 text-[11px] font-semibold text-gray-600">
                            {{ child.kode }}
                        </code>
                        <span class="text-gray-800">{{ child.name }}</span>
                        <Badge
                            v-if="(child.arsip_count ?? 0) > 0"
                            color="green"
                            class="ml-1"
                        >
                            {{ child.arsip_count }} arsip
                        </Badge>
                        <div class="ml-auto flex items-center gap-3">
                            <Link
                                :href="routeFn('kategori.edit', child.id)"
                                class="text-xs font-semibold text-blue-600 hover:text-blue-700"
                            >
                                Edit
                            </Link>
                            <button
                                type="button"
                                class="text-xs font-semibold text-red-600 hover:text-red-700 disabled:cursor-not-allowed disabled:text-gray-300"
                                :disabled="(child.arsip_count ?? 0) > 0"
                                :title="(child.arsip_count ?? 0) > 0 ? 'Sub-kategori ini masih memiliki arsip' : 'Hapus sub-kategori'"
                                @click="deleteTarget = child"
                            >
                                Hapus
                            </button>
                        </div>
                    </li>
                </ul>
                <p v-else class="mt-4 rounded-lg bg-gray-50 px-3 py-3 text-xs text-gray-500">
                    Belum ada sub-kategori.
                </p>

                <div v-if="subError" class="mt-4 rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-xs font-medium text-red-700">
                    {{ subError }}
                </div>

                <form class="mt-5 border-t border-gray-100 pt-5" @submit.prevent="submitSubCategories">
                    <p class="mb-3 text-[11px] font-semibold text-gray-500">Tambah Sub-kategori Baru</p>
                    <div class="space-y-3">
                        <div v-for="(subName, i) in subNames" :key="i" class="flex items-start gap-2">
                            <div class="flex-1">
                                <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                                    Nama Sub-kategori {{ i + 1 }}
                                </label>
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
                    <div class="mt-4 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            class="mr-auto inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700"
                            @click="addSubRow"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah baris
                        </button>
                        <PrimaryButton type="submit" :disabled="filledSubNames.length === 0">
                            Simpan Sub-kategori
                        </PrimaryButton>
                    </div>
                </form>
            </div>

            <div class="rounded-2xl border border-red-100 bg-white p-6 shadow-sm">
                <h3 class="text-sm font-bold text-gray-900">Hapus Kategori</h3>
                <p class="mt-1 text-xs text-gray-500">
                    <template v-if="isSubCategory">
                        Sub-kategori ini akan dihapus permanen.
                    </template>
                    <template v-else>
                        Menghapus kategori utama juga menghapus
                        {{ children.length }} sub-kategori di bawahnya.
                    </template>
                </p>
                <div class="mt-4">
                    <DangerButton
                        type="button"
                        :disabled="(kategori.arsip_count ?? 0) > 0"
                        @click="deleteTarget = kategori"
                    >
                        {{ isSubCategory ? 'Hapus Sub-kategori' : 'Hapus Kategori' }}
                    </DangerButton>
                    <p
                        v-if="(kategori.arsip_count ?? 0) > 0"
                        class="mt-2 text-xs font-medium text-red-600"
                    >
                        Tidak bisa dihapus: kategori ini masih memiliki
                        {{ kategori.arsip_count }} arsip.
                    </p>
                </div>
            </div>
        </div>

        <Modal :show="!!deleteTarget" max-width="sm" @close="deleteTarget = null">
            <div v-if="deleteTarget" class="p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-gray-900">Hapus '{{ deleteTarget.name }}'?</h3>
                        <p class="mt-1 text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan.</p>
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
                            v-for="child in children"
                            :key="child.id"
                            class="text-[11px] text-amber-800"
                        >
                            &bull; {{ child.name }}
                        </li>
                    </ul>
                </div>

                <div class="mt-5 flex items-center justify-end gap-2">
                    <SecondaryButton type="button" @click="deleteTarget = null">Batal</SecondaryButton>
                    <DangerButton :disabled="deleteWorking" @click="performDelete">
                        {{ deleteWorking ? 'Menghapus...' : 'Ya, Hapus' }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
