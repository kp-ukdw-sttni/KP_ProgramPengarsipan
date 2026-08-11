<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    title: {
        type: String,
        default: 'E-Archive',
    },
})
</script>

<template>
    <div class="flex min-h-screen flex-col bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800">
        <div class="flex flex-1 flex-col items-center justify-center px-4 py-12">
            <Link :href="route('dashboard')" class="mb-8 flex items-center gap-3">
                <img
                    src="/logo.png"
                    alt="Logo STTNI"
                    class="h-12 w-12 rounded-xl bg-white object-contain p-1 shadow-md"
                />
                <div class="leading-tight">
                    <h1 class="text-lg font-extrabold text-white">E-Archive STTNI</h1>
                    <p class="text-[11px] font-medium text-blue-200">Sistem Pengarsipan Dokumen</p>
                </div>
            </Link>

            <div class="w-full max-w-md">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
