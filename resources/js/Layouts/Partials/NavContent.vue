<script setup>
import { Link } from '@inertiajs/vue3'
import { useRoute } from '../../Composables/useRoute'

defineProps({
    canUpload: {
        type: Boolean,
        default: false,
    },
    canApprove: {
        type: Boolean,
        default: false,
    },
    canAdmin: {
        type: Boolean,
        default: false,
    },
    canManageUsers: {
        type: Boolean,
        default: false,
    },
    isActive: {
        type: Function,
        required: true,
    },
})

const emit = defineEmits(['navigate'])

const routeFn = useRoute()

const itemClass = (active) =>
    active
        ? 'bg-indigo-600 text-white font-semibold shadow-lg shadow-indigo-900/40 hover:bg-indigo-500'
        : 'text-gray-400 hover:bg-indigo-500/25 hover:text-white'

const navigate = () => emit('navigate')
</script>

<template>
    <div class="flex flex-1 flex-col overflow-y-auto bg-[#181c32] text-gray-300">
        <!-- Brand -->
        <div class="flex h-16 shrink-0 items-center border-b border-white/10 bg-gradient-to-r from-indigo-600/15 to-transparent px-4">
            <Link :href="routeFn('dashboard')" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl bg-white shadow-md">
                    <img src="/logo.png" alt="Logo STTNI" class="h-9 w-9 object-contain" />
                </span>
                <span class="leading-tight">
                    <span class="block text-sm font-extrabold tracking-tight text-white">E-Archive STTNI</span>
                    <span class="block text-[10px] font-medium text-gray-400">Sistem Pengarsipan Dokumen</span>
                </span>
            </Link>
        </div>

        <nav class="flex-1 px-3 py-4">
            <!-- Upload shortcut -->
            <div v-if="canUpload" class="mb-5 px-1">
                <Link
                    :href="routeFn('arsip.create')"
                    class="flex w-full items-center justify-center gap-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-900/40 transition hover:from-indigo-600 hover:to-violet-600"
                    @click="navigate"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Unggah Dokumen</span>
                </Link>
            </div>

            <!-- Dashboard -->
            <Link
                :href="routeFn('dashboard')"
                :class="itemClass(isActive('dashboard'))"
                class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition-all"
                @click="navigate"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard</span>
            </Link>

            <!-- Pengarsipan -->
            <div class="pt-5">
                <div class="mb-2 flex items-center gap-3 px-3">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Pengarsipan</span>
                    <span class="h-px flex-1 bg-white/10"></span>
                </div>

                <Link
                    :href="routeFn('arsip.index')"
                    :class="itemClass(isActive('arsip.index', 'arsip.create', 'arsip.edit'))"
                    class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition-all"
                    @click="navigate"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8" />
                    </svg>
                    <span>Daftar Dokumen</span>
                </Link>

                <Link
                    :href="routeFn('arsip.explorer')"
                    :class="itemClass(isActive('arsip.explorer'))"
                    class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition-all"
                    @click="navigate"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    <span>Penjelajah Folder</span>
                </Link>
            </div>

            <!-- Pelaksanaan & Akses -->
            <div class="pt-5">
                <div class="mb-2 flex items-center gap-3 px-3">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Pelaksanaan &amp; Akses</span>
                    <span class="h-px flex-1 bg-white/10"></span>
                </div>

                <Link
                    :href="routeFn('peminjaman.index')"
                    :class="itemClass(isActive('peminjaman.index'))"
                    class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition-all"
                    @click="navigate"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Peminjaman Saya</span>
                </Link>

                <Link
                    v-if="canApprove"
                    :href="routeFn('peminjaman.manage')"
                    :class="itemClass(isActive('peminjaman.manage'))"
                    class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition-all"
                    @click="navigate"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622" />
                    </svg>
                    <span>Persetujuan Akses</span>
                </Link>
            </div>

            <!-- Administrasi -->
            <div v-if="canAdmin" class="pt-5">
                <div class="mb-2 flex items-center gap-3 px-3">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Administrasi</span>
                    <span class="h-px flex-1 bg-white/10"></span>
                </div>

                <Link
                    :href="routeFn('kategori.index')"
                    :class="itemClass(isActive('kategori.*'))"
                    class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition-all"
                    @click="navigate"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span>Kategori Dokumen</span>
                </Link>

                <Link
                    :href="routeFn('arsip.trash')"
                    :class="itemClass(isActive('arsip.trash'))"
                    class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition-all"
                    @click="navigate"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Recycle Bin</span>
                </Link>

                <Link
                    :href="routeFn('audit.index')"
                    :class="itemClass(isActive('audit.index'))"
                    class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition-all"
                    @click="navigate"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" />
                    </svg>
                    <span>Audit Trail Log</span>
                </Link>
            </div>

            <!-- Pengguna -->
            <div v-if="canManageUsers" class="pt-5">
                <div class="mb-2 flex items-center gap-3 px-3">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Pengguna</span>
                    <span class="h-px flex-1 bg-white/10"></span>
                </div>

                <Link
                    :href="routeFn('users.index')"
                    :class="itemClass(isActive('users.*'))"
                    class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition-all"
                    @click="navigate"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Manajemen User</span>
                </Link>
            </div>
        </nav>
    </div>
</template>
