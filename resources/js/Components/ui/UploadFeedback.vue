<script setup>
import { useUploadFeedback } from '../../Composables/useUploadFeedback'

const { state } = useUploadFeedback()
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="state.visible"
                class="fixed inset-0 z-[80] flex items-center justify-center bg-white/80 backdrop-blur-sm"
            >
                <div class="flex flex-col items-center gap-5">
                    <div class="relative h-20 w-20">
                        <Transition
                            enter-active-class="transition-opacity duration-200 ease-out"
                            enter-from-class="opacity-0"
                            enter-to-class="opacity-100"
                            leave-active-class="transition-opacity duration-150 ease-in"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <div
                                v-if="state.status === 'loading'"
                                class="absolute inset-0 flex items-center justify-center"
                            >
                                <div class="h-20 w-20 animate-spin rounded-full border-4 border-indigo-200 border-t-indigo-600"></div>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <svg class="h-7 w-7 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </Transition>

                        <Transition enter-active-class="animate-pop" leave-active-class="transition-opacity duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
                            <div
                                v-if="state.status === 'success'"
                                class="absolute inset-0 flex items-center justify-center rounded-full bg-green-500 shadow-lg shadow-green-500/40"
                            >
                                <svg class="h-10 w-10 text-white" viewBox="0 0 24 24" fill="none">
                                    <path
                                        class="draw-path"
                                        d="M5 13l4 4L19 7"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        fill="none"
                                    />
                                </svg>
                            </div>
                        </Transition>

                        <Transition enter-active-class="animate-pop" leave-active-class="transition-opacity duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
                            <div
                                v-if="state.status === 'error'"
                                class="absolute inset-0 flex items-center justify-center rounded-full bg-red-500 shadow-lg shadow-red-500/40"
                            >
                                <svg class="h-10 w-10 text-white" viewBox="0 0 24 24" fill="none">
                                    <path
                                        class="draw-path"
                                        d="M7 7l10 10"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                        fill="none"
                                    />
                                    <path
                                        class="draw-path draw-path-delay"
                                        d="M17 7L7 17"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                        fill="none"
                                    />
                                </svg>
                            </div>
                        </Transition>
                    </div>

                    <Transition
                        enter-active-class="transition-opacity duration-300 ease-out"
                        enter-from-class="opacity-0"
                        enter-to-class="opacity-100"
                        leave-active-class="transition-opacity duration-150 ease-in"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <p
                            v-if="state.status !== 'loading'"
                            :class="state.status === 'success' ? 'text-green-600' : 'text-red-600'"
                            class="text-sm font-bold"
                        >
                            {{ state.message }}
                        </p>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.draw-path {
    stroke-dasharray: 40;
    stroke-dashoffset: 40;
    animation: draw 0.45s ease-out 0.1s forwards;
}

.draw-path-delay {
    animation-delay: 0.28s;
}

@keyframes draw {
    to {
        stroke-dashoffset: 0;
    }
}

.animate-pop {
    animation: pop 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes pop {
    0% {
        transform: scale(0.4);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}
</style>
