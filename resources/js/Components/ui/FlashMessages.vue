<script setup>
import { usePage } from '@inertiajs/vue3'
import { computed, ref, watch, onBeforeUnmount } from 'vue'
import { useUploadFeedback } from '../../Composables/useUploadFeedback'

const page = usePage()
const flash = computed(() => page.props.flash ?? {})
const { state: feedback } = useUploadFeedback()

const successMessage = ref('')
const errorMessage = ref('')
const SUCCESS_TIMEOUT_MS = 6000
let successTimer = null

watch(
    () => flash.value.success,
    (message) => {
        clearTimeout(successTimer)
        if (message && message !== feedback.consumed.success) {
            successMessage.value = message
            successTimer = setTimeout(() => {
                successMessage.value = ''
            }, SUCCESS_TIMEOUT_MS)
        } else {
            successMessage.value = ''
        }
    },
    { immediate: true },
)

watch(
    () => flash.value.error,
    (message) => {
        if (message && message !== feedback.consumed.error) {
            errorMessage.value = message
        } else {
            errorMessage.value = ''
        }
    },
    { immediate: true },
)

onBeforeUnmount(() => clearTimeout(successTimer))
</script>

<template>
    <div class="pointer-events-none fixed inset-x-0 top-4 z-50 flex flex-col items-center gap-3 px-4 sm:items-end sm:pr-6">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="-translate-y-4 opacity-0 scale-95"
            enter-to-class="translate-y-0 opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100 scale-100"
            leave-to-class="-translate-y-3 opacity-0 scale-95"
        >
            <div
                v-if="successMessage"
                class="pointer-events-auto flex w-full max-w-sm items-center gap-3 rounded-xl border border-green-200 bg-white p-4 shadow-lg shadow-green-900/10"
            >
                <span
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-green-500 text-white"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
                <p class="text-sm font-medium text-green-800">{{ successMessage }}</p>
            </div>
        </Transition>

        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-x-4 opacity-0 scale-95"
            enter-to-class="translate-x-0 opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-x-0 opacity-100 scale-100"
            leave-to-class="translate-x-4 opacity-0 scale-95"
        >
            <div
                v-if="errorMessage"
                class="pointer-events-auto flex w-full max-w-sm items-center gap-3 rounded-xl border border-red-200 bg-white p-4 shadow-lg shadow-red-900/10"
            >
                <span
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-500 text-white"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>
                <p class="text-sm font-medium text-red-800">{{ errorMessage }}</p>
            </div>
        </Transition>
    </div>
</template>
