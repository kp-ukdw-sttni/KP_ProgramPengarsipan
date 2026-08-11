<script setup>
import { useForm } from '@inertiajs/vue3'
import GuestLayout from '../../Layouts/GuestLayout.vue'
import Input from '../../Components/ui/Input.vue'
import InputError from '../../Components/ui/InputError.vue'
import PrimaryButton from '../../Components/ui/PrimaryButton.vue'
import { useRoute } from '../../Composables/useRoute'

const routeFn = useRoute()

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post(routeFn('login'))
}
</script>

<template>
    <GuestLayout title="Masuk">
        <h2 class="text-lg font-bold text-gray-900">Masuk ke Akun</h2>
        <p class="mt-1 text-xs text-gray-500">Silakan masuk untuk mengakses sistem pengarsipan.</p>

        <form @submit.prevent="submit" class="mt-6 space-y-4">
            <div>
                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Email</label>
                <Input v-model="form.email" type="email" autocomplete="username" placeholder="nama@sttni.ac.id" />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Kata Sandi</label>
                <Input v-model="form.password" type="password" autocomplete="current-password" placeholder="********" />
                <InputError :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-xs text-gray-600">
                    <input v-model="form.remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    Ingat saya
                </label>
                <a :href="routeFn('password.request')" class="text-xs font-semibold text-indigo-600 hover:underline">
                    Lupa kata sandi?
                </a>
            </div>

            <PrimaryButton class="w-full" :disabled="form.processing">
                {{ form.processing ? 'Masuk...' : 'Masuk' }}
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>
