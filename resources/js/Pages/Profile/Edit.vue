<script setup>
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import Input from '../../Components/ui/Input.vue'
import InputError from '../../Components/ui/InputError.vue'
import PrimaryButton from '../../Components/ui/PrimaryButton.vue'
import SecondaryButton from '../../Components/ui/SecondaryButton.vue'
import { useRoute } from '../../Composables/useRoute'

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
})

const routeFn = useRoute()

const profileForm = useForm({
    name: props.user.name ?? '',
    email: props.user.email ?? '',
})

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const submitProfile = () => {
    profileForm.patch(routeFn('profile.update'), {
        preserveScroll: true,
    })
}

const submitPassword = () => {
    passwordForm.put(routeFn('password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    })
}
</script>

<template>
    <AuthenticatedLayout title="Profil Saya">
        <FlashMessages />

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-gray-700">
                    Informasi Profil
                </h3>
                <form @submit.prevent="submitProfile" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">Nama Lengkap</label>
                        <Input v-model="profileForm.name" type="text" />
                        <InputError :message="profileForm.errors.name" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">Email</label>
                        <Input v-model="profileForm.email" type="email" />
                        <InputError :message="profileForm.errors.email" />
                    </div>
                    <div class="flex items-center justify-end gap-2 sm:col-span-2">
                        <PrimaryButton :disabled="profileForm.processing">
                            {{ profileForm.processing ? 'Menyimpan...' : 'Simpan Profil' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-gray-700">
                    Ganti Kata Sandi
                </h3>
                <form @submit.prevent="submitPassword" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">Kata Sandi Saat Ini</label>
                        <Input v-model="passwordForm.current_password" type="password" autocomplete="current-password" />
                        <InputError :message="passwordForm.errors.current_password" />
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">Kata Sandi Baru</label>
                        <Input v-model="passwordForm.password" type="password" autocomplete="new-password" />
                        <InputError :message="passwordForm.errors.password" />
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-gray-500">Konfirmasi Kata Sandi</label>
                        <Input v-model="passwordForm.password_confirmation" type="password" autocomplete="new-password" />
                        <InputError :message="passwordForm.errors.password_confirmation" />
                    </div>
                    <div class="flex items-center justify-end gap-2 sm:col-span-2">
                        <SecondaryButton type="button" @click="passwordForm.reset()">Reset</SecondaryButton>
                        <PrimaryButton :disabled="passwordForm.processing">
                            {{ passwordForm.processing ? 'Menyimpan...' : 'Ganti Kata Sandi' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-gray-700">Akun</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-[11px] font-semibold text-gray-500">NIK / NIM</dt>
                        <dd class="font-semibold text-gray-900">{{ user.nik_nim ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold text-gray-500">Divisi</dt>
                        <dd class="font-semibold text-gray-900">{{ user.divisi?.name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold text-gray-500">Role</dt>
                        <dd class="flex flex-wrap gap-1">
                            <span
                                v-for="r in user.roles"
                                :key="r.id"
                                class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-700"
                            >
                                {{ r.name }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold text-gray-500">Status Akun</dt>
                        <dd class="font-semibold text-gray-900">{{ user.status_akun }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
