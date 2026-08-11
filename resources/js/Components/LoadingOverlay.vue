<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'
import { useUploadFeedback } from '../Composables/useUploadFeedback'

const { state: feedback } = useUploadFeedback()

const loading = ref(false)
const showLoading = computed(() => loading.value && !feedback.visible)

let finishTimer = null

const onStart = () => {
    clearTimeout(finishTimer)
    loading.value = true
}

const onFinish = () => {
    clearTimeout(finishTimer)
    finishTimer = setTimeout(() => {
        loading.value = false
    }, 350)
}

router.on('start', onStart)
router.on('finish', onFinish)
router.on('cancel', onFinish)
router.on('error', onFinish)

onBeforeUnmount(() => clearTimeout(finishTimer))
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-300 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="showLoading"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/40 backdrop-blur-sm"
            aria-busy="true"
            role="status"
        >
            <div class="flex flex-col items-center gap-5">
                <div class="relative h-16 w-16">
                    <div class="absolute inset-0 rounded-full border-4 border-white/30"></div>
                    <div class="absolute inset-0 animate-spin rounded-full border-4 border-transparent border-t-indigo-400"></div>
                    <div class="absolute inset-3 rounded-full border-2 border-white/20"></div>
                    <div class="absolute inset-3 animate-spin rounded-full border-2 border-transparent border-t-white [animation-direction:reverse] [animation-duration:0.8s]"></div>
                </div>
                <p class="text-xs font-bold uppercase tracking-widest text-white/90">
                    Memuat...
                </p>
            </div>
        </div>
    </Transition>
</template>
