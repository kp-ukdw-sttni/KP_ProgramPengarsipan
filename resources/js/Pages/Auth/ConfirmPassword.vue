<script setup>
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import Input from '../../Components/ui/Input.vue'
import InputError from '../../Components/ui/InputError.vue'
import PrimaryButton from '../../Components/ui/PrimaryButton.vue'
import { useRoute } from '../../Composables/useRoute'

const routeFn = useRoute()

const form = useForm({
    password: '',
})

const submit = () => {
    form.post(routeFn('password.confirm'))
}
</script>

<template>
    <AuthenticatedLayout title="Konfirmasi Kata Sandi">
        <div class="mx-auto max-w-md">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                <h2 class="text-lg font-bold text-gray-900">Konfirmasi Kata Sandi</h2>
                <p class="mt-1 text-xs text-gray-500">
                    Ini adalah area aman. Silakan konfirmasi kata sandi Anda sebelum melanjutkan.
                </p>

                <form @submit.prevent="submit" class="mt-6 space-y-4">
                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">Kata Sandi</label>
                        <Input v-model="form.password" type="password" autocomplete="current-password" />
                        <InputError :message="form.errors.password" />
                    </div>

                    <PrimaryButton class="w-full" :disabled="form.processing">
                        {{ form.processing ? 'Mengonfirmasi...' : 'Konfirmasi' }}
                    </PrimaryButton>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
