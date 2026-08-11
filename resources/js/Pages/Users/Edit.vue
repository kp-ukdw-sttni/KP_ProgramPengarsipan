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

        <div class="mx-auto max-w-2xl">
            <form @submit.prevent="submit" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-gray-700">Edit Pengguna</h3>

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
