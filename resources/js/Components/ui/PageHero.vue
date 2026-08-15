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
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-navy via-navy-dark to-[#0A1120] p-6 text-white shadow-lg sm:p-8">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(212,175,55,0.18),transparent_55%)]"></div>
        <div class="relative flex flex-wrap items-center justify-between gap-4">
            <div class="min-w-0">
                <p v-if="eyebrow" class="text-xs font-semibold uppercase tracking-wider text-gold-light">
                    {{ eyebrow }}
                </p>
                <h2 class="mt-1.5 text-xl font-extrabold sm:text-2xl">{{ title }}</h2>
                <p v-if="subtitle" class="mt-1 text-sm text-blue-200">{{ subtitle }}</p>
            </div>
            <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2">
                <slot name="actions" />
            </div>
        </div>
    </div>
</template>
