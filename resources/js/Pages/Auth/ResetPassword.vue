<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import GuestLayout from '../../Layouts/GuestLayout.vue'
import Input from '../../Components/ui/Input.vue'
import InputError from '../../Components/ui/InputError.vue'
import PrimaryButton from '../../Components/ui/PrimaryButton.vue'
import { useRoute } from '../../Composables/useRoute'

const props = defineProps({
    request: {
        type: Object,
        required: true,
    },
})

const routeFn = useRoute()
const page = usePage()
const status = computed(() => page.props.flash?.status ?? page.props.status ?? null)

const form = useForm({
    token: props.request.token ?? '',
    email: props.request.email ?? '',
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.post(routeFn('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <GuestLayout title="Reset Kata Sandi">
        <h2 class="text-lg font-bold text-gray-900">Atur Ulang Kata Sandi</h2>
        <p class="mt-1 text-xs text-gray-500">Buat kata sandi baru untuk akun Anda.</p>

        <form @submit.prevent="submit" class="mt-6 space-y-4">
            <div v-if="status" class="rounded-lg border border-green-400 bg-green-50 p-3 text-sm font-medium text-green-700">
                {{ status }}
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Email</label>
                <Input v-model="form.email" type="email" autocomplete="username" />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Kata Sandi Baru</label>
                <Input v-model="form.password" type="password" autocomplete="new-password" />
                <InputError :message="form.errors.password" />
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold text-gray-500">Konfirmasi Kata Sandi</label>
                <Input v-model="form.password_confirmation" type="password" autocomplete="new-password" />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <PrimaryButton class="w-full" :disabled="form.processing">
                {{ form.processing ? 'Menyimpan...' : 'Simpan Kata Sandi' }}
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>
