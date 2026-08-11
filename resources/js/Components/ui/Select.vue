<script setup>
defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
})
</script>

<template>
    <div class="relative">
        <select
            :value="modelValue"
            @change="$emit('update:modelValue', $event.target.value)"
            class="block w-full cursor-pointer appearance-none rounded-lg border-gray-300 bg-white py-2 pl-3 pr-9 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 focus:outline-none"
        >
            <slot />
        </select>
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>
</template>
