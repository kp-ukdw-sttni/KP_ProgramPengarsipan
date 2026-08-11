<script setup>
import { computed } from 'vue'

const props = defineProps({
    color: {
        type: String,
        default: 'gray',
    },
})

const colors = {
    gray: 'bg-gray-100 text-gray-800',
    blue: 'bg-blue-100 text-blue-800',
    green: 'bg-green-100 text-green-800',
    red: 'bg-red-100 text-red-800',
    amber: 'bg-amber-100 text-amber-800',
    indigo: 'bg-indigo-100 text-indigo-800',
}

const classes = computed(() => colors[props.color] ?? colors.gray)
</script>

<template>
    <span
        :class="classes"
        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 ring-inset ring-black/5"
    >
        <slot />
    </span>
</template>
