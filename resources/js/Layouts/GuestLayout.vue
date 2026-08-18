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
    <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-gray-50 via-gray-100 to-blue-50/40 p-4 sm:p-6">
        <!-- Auth Container Card -->
        <div class="w-full max-w-md rounded-2xl border border-gray-200/60 bg-white p-8 shadow-xl shadow-blue-900/5 relative overflow-hidden">
            <!-- Top Gradient Accent -->
            <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-blue-600 via-indigo-600 to-indigo-700"></div>

            <!-- Brand Header -->
            <div class="mb-8 text-center">
                <Link :href="route('dashboard')" class="inline-block hover:opacity-90 transition">
                    <img src="/logo.png" alt="Logo STTNI" class="mx-auto h-16 w-auto object-contain" />
                </Link>
                <h2 class="mt-4 text-xl font-bold text-gray-900 leading-tight">E-Archive STTNI</h2>
                <p class="mt-1.5 text-xs text-gray-500">Sistem Pengarsipan Dokumen</p>
            </div>

            <slot />
        </div>

        <!-- Back to Home Link -->
        <a :href="route('dashboard')" class="mt-6 inline-flex items-center text-xs font-semibold text-gray-500 hover:text-indigo-600 transition">
            &larr; Kembali ke Beranda
        </a>
    </div>
</template>
