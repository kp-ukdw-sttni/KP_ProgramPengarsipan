<script setup>
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '../../Components/ui/PrimaryButton.vue'
import SecondaryButton from '../../Components/ui/SecondaryButton.vue'
import { useRoute } from '../../Composables/useRoute'

const routeFn = useRoute()

const form = useForm({})

const submit = () => {
    form.post(routeFn('verification.send'))
}

const logout = () => {
    form.post(routeFn('logout'))
}
</script>

<template>
    <AuthenticatedLayout title="Verifikasi Email">
        <div class="mx-auto max-w-md">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                <h2 class="text-lg font-bold text-gray-900">Verifikasi Email Anda</h2>
                <p class="mt-1 text-xs text-gray-500">
                    Tautan verifikasi telah dikirim ke email Anda sebelum mendaftar.
                </p>

                <div class="mt-4 rounded-lg border border-amber-300 bg-amber-50 p-3 text-xs text-amber-700">
                    Anda harus memverifikasi alamat email sebelum mengakses fitur sistem.
                </div>

                <div class="mt-6 flex items-center justify-end gap-2">
                    <SecondaryButton type="button" @click="logout">
                        Keluar
                    </SecondaryButton>
                    <PrimaryButton :disabled="form.processing" @click="submit">
                        {{ form.processing ? 'Mengirim...' : 'Kirim Ulang Email Verifikasi' }}
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
