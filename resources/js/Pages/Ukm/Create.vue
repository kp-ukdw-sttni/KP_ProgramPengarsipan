<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import Input from '../../Components/ui/Input.vue'
import Select from '../../Components/ui/Select.vue'
import InputError from '../../Components/ui/InputError.vue'
import PageHero from '../../Components/ui/PageHero.vue'
import PrimaryButton from '../../Components/ui/PrimaryButton.vue'
import { useRoute } from '../../Composables/useRoute'

const props = defineProps({
    divisi: {
        type: Array,
        default: () => [],
    },
})

const routeFn = useRoute()

const form = useForm({
    kode: '',
    name: '',
    deskripsi: '',
    pembina: '',
    ketua: '',
    divisi_id: '',
    status: 'Aktif',
})

const submit = () => form.post(routeFn('ukm.store'))
</script>

<template>
    <AuthenticatedLayout title="Tambah UKM">
        <FlashMessages />

        <PageHero
            eyebrow="Master Data UKM"
            title="Tambah UKM Baru"
            subtitle="Lengkapi informasi UKM sebelum digunakan untuk presensi."
        >
            <template #actions>
                <Link
                    :href="routeFn('ukm.index')"
                    class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20"
                >
                    Kembali
                </Link>
            </template>
        </PageHero>

        <div class="mt-6 max-w-3xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama UKM <span class="text-red-500">*</span></label>
                    <Input v-model="form.name" placeholder="Contoh: UKM Paduan Suara" />
                    <InputError :message="form.errors.name" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Pembina</label>
                    <Input v-model="form.pembina" placeholder="Nama dosen pembina" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Ketua UKM</label>
                    <Input v-model="form.ketua" placeholder="Nama ketua UKM" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Status</label>
                    <Select v-model="form.status">
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </Select>
                    <InputError :message="form.errors.status" class="mt-1" />
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi</label>
                    <textarea
                        v-model="form.deskripsi"
                        rows="3"
                        placeholder="Deskripsi singkat UKM..."
                        class="block w-full rounded-lg border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 focus:outline-none"
                    />
                    <InputError :message="form.errors.deskripsi" class="mt-1" />
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <PrimaryButton type="button" :disabled="form.processing" @click="submit">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan UKM' }}
                </PrimaryButton>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
