<script setup>
import { useForm } from '@inertiajs/vue3'
import GuestLayout from '../../Layouts/GuestLayout.vue'
import Input from '../../Components/ui/Input.vue'
import Select from '../../Components/ui/Select.vue'
import InputError from '../../Components/ui/InputError.vue'
import PrimaryButton from '../../Components/ui/PrimaryButton.vue'
import { Link } from '@inertiajs/vue3'
import { useRoute } from '../../Composables/useRoute'

const props = defineProps({
    divisi: {
        type: Array,
        default: () => [],
    },
})

const routeFn = useRoute()

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    divisi_id: '',
})

const submit = () => {
    form.post(routeFn('register'))
}
</script>

<template>
    <GuestLayout title="Daftar">
        <h2 class="text-lg font-bold text-gray-900">Daftar Akun</h2>
        <p class="mt-1 text-xs text-gray-500">Buat akun baru untuk mengakses sistem pengarsipan.</p>

        <form @submit.prevent="submit" class="mt-6 space-y-4">
            <div>
                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Nama Lengkap</label>
                <Input v-model="form.name" type="text" autocomplete="name" />
                <InputError :message="form.errors.name" />
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Email</label>
                <Input v-model="form.email" type="email" autocomplete="username" />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Divisi</label>
                <Select v-model="form.divisi_id">
                    <option value="">-- Pilih Divisi --</option>
                    <option v-for="d in divisi" :key="d.id" :value="d.id">{{ d.name }}</option>
                </Select>
                <InputError :message="form.errors.divisi_id" />
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Kata Sandi</label>
                <Input v-model="form.password" type="password" autocomplete="new-password" />
                <InputError :message="form.errors.password" />
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Konfirmasi Kata Sandi</label>
                <Input v-model="form.password_confirmation" type="password" autocomplete="new-password" />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <PrimaryButton class="w-full" :disabled="form.processing">
                {{ form.processing ? 'Mendaftar...' : 'Daftar' }}
            </PrimaryButton>
        </form>

        <p class="mt-4 text-center text-xs text-gray-500">
            Sudah punya akun?
            <Link :href="routeFn('login')" class="font-semibold text-indigo-600 hover:underline">
                Masuk
            </Link>
        </p>
    </GuestLayout>
</template>
