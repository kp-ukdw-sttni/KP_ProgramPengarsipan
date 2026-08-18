<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
})
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
    >
        <slot />
    </button>
</template>
