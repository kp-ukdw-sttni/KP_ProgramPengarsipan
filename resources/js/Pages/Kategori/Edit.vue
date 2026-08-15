<script setup>
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import Input from '../../Components/ui/Input.vue'
import Select from '../../Components/ui/Select.vue'
import InputError from '../../Components/ui/InputError.vue'
import PageHero from '../../Components/ui/PageHero.vue'
import PrimaryButton from '../../Components/ui/PrimaryButton.vue'
import SecondaryButton from '../../Components/ui/SecondaryButton.vue'
import { Link } from '@inertiajs/vue3'
import { useRoute } from '../../Composables/useRoute'

const props = defineProps({
    kategori: {
        type: Object,
        required: true,
    },
    parentCategories: {
        type: Array,
        default: () => [],
    },
})

const routeFn = useRoute()

const form = useForm({
    kode: props.kategori.kode ?? '',
    name: props.kategori.name ?? '',
    deskripsi: props.kategori.deskripsi ?? '',
    parent_id: props.kategori.parent_id ?? '',
})

const submit = () => {
    form.put(routeFn('kategori.update', props.kategori.id))
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
                    class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Daftar
                </Link>
            </template>
        </PageHero>

        <div class="mx-auto mt-6 max-w-2xl">
            <form @submit.prevent="submit" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

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
        </div>
    </AuthenticatedLayout>
</template>
