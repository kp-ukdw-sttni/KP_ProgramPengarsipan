<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import GuestLayout from '../../Layouts/GuestLayout.vue'
import Input from '../../Components/ui/Input.vue'
import InputError from '../../Components/ui/InputError.vue'
import PrimaryButton from '../../Components/ui/PrimaryButton.vue'
import { Link } from '@inertiajs/vue3'
import { useRoute } from '../../Composables/useRoute'

const routeFn = useRoute()
const page = usePage()
const status = computed(() => page.props.flash?.status ?? page.props.status ?? null)

const form = useForm({
    email: '',
})

const submit = () => {
    form.post(routeFn('password.email'))
}
</script>

<template>
    <GuestLayout title="Lupa Kata Sandi">
        <h2 class="text-lg font-bold text-gray-900">Lupa Kata Sandi</h2>
        <p class="mt-1 text-xs text-gray-500">
            Masukkan email Anda. Tautan pengaturan ulang kata sandi akan dikirim ke email Anda.
        </p>

        <form @submit.prevent="submit" class="mt-6 space-y-4">
            <div v-if="status" class="rounded-lg border border-green-400 bg-green-50 p-3 text-sm font-medium text-green-700">
                {{ status }}
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Email</label>
                <Input v-model="form.email" type="email" autocomplete="username" />
                <InputError :message="form.errors.email" />
            </div>

            <PrimaryButton class="w-full" :disabled="form.processing">
                {{ form.processing ? 'Mengirim...' : 'Kirim Tautan Reset' }}
            </PrimaryButton>
        </form>

        <p class="mt-4 text-center text-xs text-gray-500">
            Ingat kata sandi?
            <Link :href="routeFn('login')" class="font-semibold text-indigo-600 hover:underline">
                Masuk
            </Link>
        </p>
    </GuestLayout>
</template>
