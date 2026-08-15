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
    user: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
        default: () => [],
    },
    divisi: {
        type: Array,
        default: () => [],
    },
})

const routeFn = useRoute()

const currentRole = props.user.roles?.[0]?.name ?? ''

const form = useForm({
    name: props.user.name ?? '',
    nik_nim: props.user.nik_nim ?? '',
    email: props.user.email ?? '',
    password: '',
    password_confirmation: '',
    divisi_id: props.user.divisi_id ?? '',
    role: currentRole,
    status_akun: props.user.status_akun ?? 'Aktif',
})

const submit = () => {
    form.put(routeFn('users.update', props.user.id))
}
</script>

<template>
    <AuthenticatedLayout title="Edit Pengguna">
        <FlashMessages />

        <PageHero
            eyebrow="Manajemen"
            title="Edit Pengguna"
            subtitle="Perbarui data akun dan hak akses pengguna."
        >
            <template #actions>
                <Link
                    :href="routeFn('users.index')"
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
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">Nama Lengkap</label>
                        <Input v-model="form.name" type="text" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">Email</label>
                        <Input v-model="form.email" type="email" />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">NIK / NIM</label>
                        <Input v-model="form.nik_nim" type="text" />
                        <InputError :message="form.errors.nik_nim" />
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">
                            Password Baru <span class="font-normal text-gray-400">(kosongkan bila tidak diganti)</span>
                        </label>
                        <Input v-model="form.password" type="password" autocomplete="new-password" />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">Konfirmasi Password</label>
                        <Input v-model="form.password_confirmation" type="password" autocomplete="new-password" />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">Role</label>
                        <Select v-model="form.role">
                            <option value="">-- Pilih Role --</option>
                            <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name }}</option>
                        </Select>
                        <InputError :message="form.errors.role" />
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">Status Akun</label>
                        <Select v-model="form.status_akun">
                            <option value="Aktif">Aktif</option>
                            <option value="Suspended">Suspended</option>
                            <option value="Deactivated">Nonaktif</option>
                        </Select>
                        <InputError :message="form.errors.status_akun" />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">Divisi</label>
                        <Select v-model="form.divisi_id">
                            <option value="">-- Tanpa Divisi --</option>
                            <option v-for="d in divisi" :key="d.id" :value="d.id">{{ d.name }}</option>
                        </Select>
                        <InputError :message="form.errors.divisi_id" />
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-2">
                    <Link :href="routeFn('users.index')">
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
