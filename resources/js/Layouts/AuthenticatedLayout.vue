<script setup>
import { computed, ref } from 'vue'
import { Link, Head, router } from '@inertiajs/vue3'
import { useAuth } from '../Composables/useAuth'
import { useRoute } from '../Composables/useRoute'
import NavContent from './Partials/NavContent.vue'
import UploadFeedback from '../Components/ui/UploadFeedback.vue'

const props = defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
})

const { user, hasRole } = useAuth()
const routeFn = useRoute()

const mobileMenuOpen = ref(false)
const userMenuOpen = ref(false)

const isActive = (...names) => names.some((name) => routeFn().current(name))

const initials = computed(() => {
    const words = (user.value?.name ?? '')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
    return words.map((w) => w[0].toUpperCase()).join('')
})

const primaryRole = computed(() => user.value?.roles?.[0] ?? '')

const today = computed(() =>
    new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date()),
)

const canUpload = computed(() => hasRole('Superadmin', 'Operator', 'Staf TU'))
const canApprove = computed(() =>
    hasRole('Superadmin', 'Operator', 'Staf TU', 'Kaprodi', 'Dekan'),
)
const canAdmin = computed(() => hasRole('Superadmin', 'Operator', 'Staf TU'))
const canManageUsers = computed(() => hasRole('Superadmin'))

const logout = () => {
    router.post(routeFn('logout'))
}

const vClickOutside = {
    mounted(el, binding) {
        el._clickOutside = (event) => {
            if (!el.contains(event.target)) binding.value()
        }
        document.addEventListener('click', el._clickOutside)
    },
    unmounted(el) {
        document.removeEventListener('click', el._clickOutside)
    },
}
</script>

<template>
    <Head :title="title" />

    <div class="flex min-h-screen bg-gray-50 text-gray-900">
        <!-- Desktop Sidebar -->
        <aside class="hidden md:flex md:w-64 md:shrink-0 md:flex-col bg-[#181c32] text-gray-300">
            <NavContent
                :can-upload="canUpload"
                :can-approve="canApprove"
                :can-admin="canAdmin"
                :can-manage-users="canManageUsers"
                :is-active="isActive"
            />
        </aside>

        <!-- Mobile Drawer -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity ease-linear duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity ease-linear duration-300"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="mobileMenuOpen"
                    class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm md:hidden"
                    @click="mobileMenuOpen = false"
                />
            </Transition>

            <Transition
                enter-active-class="transition ease-in-out duration-300 transform"
                enter-from-class="-translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transition ease-in-out duration-300 transform"
                leave-from-class="translate-x-0"
                leave-to-class="-translate-x-full"
            >
                <div
                    v-if="mobileMenuOpen"
                    class="fixed inset-y-0 left-0 z-50 w-full max-w-xs bg-[#181c32] md:hidden"
                >
                    <div class="absolute right-3 top-3 z-10">
                        <button
                            type="button"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20"
                            @click="mobileMenuOpen = false"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <NavContent
                        :can-upload="canUpload"
                        :can-approve="canApprove"
                        :can-admin="canAdmin"
                        :can-manage-users="canManageUsers"
                        :is-active="isActive"
                        @navigate="mobileMenuOpen = false"
                    />
                </div>
            </Transition>
        </Teleport>

        <!-- Main Column -->
        <div class="flex min-w-0 flex-1 flex-col">
            <!-- Topbar -->
            <header class="shrink-0 border-b border-gray-200 bg-white px-4 py-3.5 sm:px-6 md:px-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <button
                            type="button"
                            class="-ml-1 rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 md:hidden"
                            @click="mobileMenuOpen = true"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <h2 class="text-base font-bold leading-tight text-gray-800 sm:text-lg">
                            {{ title }}
                        </h2>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="hidden items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-semibold text-gray-600 shadow-sm sm:flex">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>{{ today }}</span>
                        </div>

                        <!-- User dropdown -->
                        <div class="relative" v-click-outside="() => userMenuOpen = false">
                            <button
                                type="button"
                                class="flex items-center gap-2 rounded-full p-1 transition hover:bg-gray-100"
                                @click="userMenuOpen = !userMenuOpen"
                            >
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">
                                    {{ initials }}
                                </div>
                            </button>

                            <Transition
                                enter-active-class="transition ease-out duration-100"
                                enter-from-class="opacity-0 scale-95"
                                enter-to-class="opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="opacity-100 scale-100"
                                leave-to-class="opacity-0 scale-95"
                            >
                                <div
                                    v-if="userMenuOpen"
                                    class="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5"
                                >
                                    <div class="border-b border-gray-100 px-4 py-2.5">
                                        <p class="truncate text-sm font-semibold text-gray-900">
                                            {{ user?.name }}
                                        </p>
                                        <p class="truncate text-xs text-gray-500">
                                            {{ user?.email }}
                                        </p>
                                        <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-indigo-600">
                                            {{ primaryRole }}
                                        </p>
                                    </div>

                                    <Link
                                        :href="routeFn('profile.edit')"
                                        class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                        @click="userMenuOpen = false"
                                    >
                                        Profil Saya
                                    </Link>

                                    <button
                                        type="button"
                                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50"
                                        @click="logout"
                                    >
                                        Keluar
                                    </button>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8">
                <slot />
            </main>
        </div>
    </div>

    <UploadFeedback />
</template>
