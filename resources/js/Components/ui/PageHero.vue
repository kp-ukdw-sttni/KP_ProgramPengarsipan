<script setup>
defineProps({
    eyebrow: {
        type: String,
        default: '',
    },
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
})
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="min-w-0">
                <p v-if="eyebrow" class="text-xs font-semibold uppercase tracking-wider text-blue-600">
                    {{ eyebrow }}
                </p>
                <h2 class="mt-1.5 text-xl font-extrabold text-gray-900 sm:text-2xl">{{ title }}</h2>
                <p v-if="subtitle" class="mt-1 text-sm text-gray-500">{{ subtitle }}</p>
            </div>
            <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2">
                <slot name="actions" />
            </div>
        </div>
    </div>
</template>
