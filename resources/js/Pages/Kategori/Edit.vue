<script setup>
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import Input from '../../Components/ui/Input.vue'
import Select from '../../Components/ui/Select.vue'
import InputError from '../../Components/ui/InputError.vue'
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

        <div class="mx-auto max-w-2xl">
            <form @submit.prevent="submit" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-gray-700">Edit Kategori</h3>

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
