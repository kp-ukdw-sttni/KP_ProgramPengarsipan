<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import GuestLayout from '../../Layouts/GuestLayout.vue'
import Input from '../../Components/ui/Input.vue'
import InputError from '../../Components/ui/InputError.vue'
import PrimaryButton from '../../Components/ui/PrimaryButton.vue'
import { useRoute } from '../../Composables/useRoute'

const routeFn = useRoute()
const showPassword = ref(false)

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
        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700">Alamat Email</label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    autocomplete="username"
                    placeholder="nama@sttni.ac.id"
                    class="mt-1"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                    <a :href="routeFn('password.request')" class="text-xs font-semibold text-blue-600 hover:text-blue-500 transition">
                        Lupa Password?
                    </a>
                </div>
                <div class="relative mt-1">
                    <Input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="pr-10"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                    >
                        <svg v-if="!showPassword" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                <InputError :message="form.errors.password" />
            </div>

            <div class="flex items-center">
                <label for="remember_me" class="inline-flex items-center cursor-pointer">
                    <input
                        id="remember_me"
                        v-model="form.remember"
                        type="checkbox"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 h-4 w-4"
                    />
                    <span class="ms-2 text-xs font-medium text-gray-600 select-none">Ingat saya</span>
                </label>
            </div>

            <div>
                <PrimaryButton class="w-full" :disabled="form.processing">
                    {{ form.processing ? 'Masuk...' : 'Masuk Sistem' }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
