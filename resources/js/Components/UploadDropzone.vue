<script setup>
import { ref } from 'vue'

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(['update:modelValue'])

const inputRef = ref(null)
const dragging = ref(false)

const maxFiles = 10
const maxSizeMB = 10
const accepted = 'application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png'

const formatSize = (bytes) => {
    if (!bytes) return '-'
    if (bytes < 1024) return `${bytes} B`
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
    return `${(bytes / 1024 / 1024).toFixed(1)} MB`
}

const isDuplicate = (file) => props.modelValue.some((f) => f.name === file.name && f.size === file.size)

const addFiles = (fileList) => {
    const incoming = Array.from(fileList)
    const next = [...props.modelValue]
    for (const file of incoming) {
        if (next.length >= maxFiles) break
        if (isDuplicate(file)) continue
        next.push(file)
    }
    emit('update:modelValue', next)
}

const onDrop = (event) => {
    dragging.value = false
    addFiles(event.dataTransfer.files)
}

const onSelect = (event) => {
    addFiles(event.target.files)
    inputRef.value.value = ''
}

const removeFile = (index) => {
    const next = [...props.modelValue]
    next.splice(index, 1)
    emit('update:modelValue', next)
}

const pickFile = () => inputRef.value?.click()
</script>

<template>
    <div>
        <div
            class="relative flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-6 py-10 text-center transition"
            :class="dragging
                ? 'border-indigo-400 bg-indigo-50'
                : 'border-gray-300 bg-gray-50 hover:border-indigo-300 hover:bg-indigo-50/50'"
            @click="pickFile"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="onDrop"
        >
            <svg class="h-10 w-10 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
            </svg>
            <p class="mt-3 text-sm font-semibold text-gray-700">
                Tarik &amp; letakkan berkas di sini, atau klik untuk memilih
            </p>
            <p class="mt-1 text-xs text-gray-500">
                PDF, DOCX, JPG, PNG &mdash; maks. {{ maxFiles }} berkas, {{ maxSizeMB }}MB per berkas
            </p>
            <input
                ref="inputRef"
                type="file"
                multiple
                :accept="accepted"
                class="hidden"
                @change="onSelect"
            >
        </div>

        <ul v-if="modelValue.length" class="mt-3 space-y-2">
            <li
                v-for="(file, index) in modelValue"
                :key="`${file.name}-${file.size}-${index}`"
                class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white px-3 py-2 shadow-sm"
            >
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-gray-800">{{ file.name }}</p>
                    <p class="text-[11px] text-gray-400">{{ formatSize(file.size) }}</p>
                </div>
                <button
                    type="button"
                    class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600"
                    @click.stop="removeFile(index)"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </li>
        </ul>
    </div>
</template>
