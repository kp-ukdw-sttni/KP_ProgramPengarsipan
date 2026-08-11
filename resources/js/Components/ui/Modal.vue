<script setup>
import { watch, onBeforeUnmount } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
})

const emit = defineEmits(['close'])

const sizes = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
    '3xl': 'sm:max-w-3xl',
    '4xl': 'sm:max-w-4xl',
    '5xl': 'sm:max-w-5xl',
    '6xl': 'sm:max-w-6xl',
    '7xl': 'sm:max-w-7xl',
}

const onEscape = (e) => {
    if (e.key === 'Escape' && props.show && props.closeable) {
        emit('close')
    }
}

watch(
    () => props.show,
    (open) => {
        if (open) {
            document.body.style.overflow = 'hidden'
            document.addEventListener('keydown', onEscape)
        } else {
            document.body.style.overflow = ''
            document.removeEventListener('keydown', onEscape)
        }
    },
)

onBeforeUnmount(() => {
    document.removeEventListener('keydown', onEscape)
    document.body.style.overflow = ''
})
</script>

<template>
    <Teleport to="body">
        <Transition leave-active-class="duration-200 ease-in">
            <div
                v-show="show"
                class="fixed inset-0 z-[60] overflow-y-auto px-4 py-6 sm:px-0 sm:py-10"
            >
                <Transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-show="show"
                        class="fixed inset-0 bg-black/50 backdrop-blur-sm"
                        @click="closeable && emit('close')"
                    />
                </Transition>

                <Transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div
                        v-show="show"
                        class="relative z-10 mx-auto w-full max-w-full overflow-hidden rounded-xl bg-white shadow-2xl"
                        :class="sizes[maxWidth] ?? sizes['2xl']"
                    >
                        <slot v-if="show" />
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
