<script setup>
import { ref, computed, watch, defineAsyncComponent } from 'vue'
import Modal from './ui/Modal.vue'
import { useRoute } from '../Composables/useRoute'

const VuePdfEmbed = defineAsyncComponent(() => import('vue-pdf-embed'))

const props = defineProps({
    arsip: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits(['close'])

const routeFn = useRoute()

const pageCount = ref(0)
const page = ref(1)
const zoom = ref(1)
const loading = ref(false)
const error = ref('')

const src = computed(() => {
    if (!props.arsip) return null
    return routeFn('arsip.stream', props.arsip.id)
})

watch(
    () => props.arsip,
    () => {
        page.value = 1
        zoom.value = 1
        error.value = ''
        pageCount.value = 0
        if (props.arsip) loading.value = true
    },
)

const onLoaded = ({ numPages }) => {
    pageCount.value = numPages
    loading.value = false
}

const onProgress = () => {
    loading.value = true
}

const onLoadingFailed = (err) => {
    loading.value = false
    error.value = err?.message ?? 'Gagal memuat dokumen.'
}
</script>

<template>
    <Modal :show="!!arsip" max-width="7xl" @close="emit('close')">
        <template v-if="arsip">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-3.5">
                <div class="min-w-0 pr-4">
                    <h3 class="truncate text-sm font-bold text-gray-900">
                        {{ arsip.judul }}
                    </h3>
                    <p class="mt-0.5 truncate text-xs text-gray-500">
                        {{ arsip.nomor_arsip }}
                        <template v-if="arsip.tahun"> &bull; Tahun {{ arsip.tahun }}</template>
                    </p>
                </div>
                <button
                    type="button"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-gray-500 transition hover:bg-gray-100 hover:text-gray-900"
                    @click="emit('close')"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 bg-gray-50 px-5 py-2">
                <div class="flex items-center gap-3 text-xs text-gray-600">
                    <button
                        type="button"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-gray-300 bg-white font-bold hover:bg-gray-100 disabled:opacity-40"
                        :disabled="page <= 1"
                        @click="page--"
                    >
                        &lsaquo;
                    </button>
                    <span>
                        Halaman
                        <span class="font-semibold text-gray-900">{{ page }}</span>
                        /
                        <span class="font-semibold text-gray-900">{{ pageCount }}</span>
                    </span>
                    <button
                        type="button"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-gray-300 bg-white font-bold hover:bg-gray-100 disabled:opacity-40"
                        :disabled="page >= pageCount"
                        @click="page++"
                    >
                        &rsaquo;
                    </button>
                </div>
                <div class="flex items-center gap-1.5">
                    <button
                        type="button"
                        class="rounded-md px-2 py-1 text-xs font-semibold hover:bg-gray-200 disabled:opacity-40"
                        :disabled="zoom <= 0.5"
                        @click="zoom = Math.round((zoom - 0.25) * 100) / 100"
                    >
                        Zoom - ({{ Math.round(zoom * 100) }}%)
                    </button>
                    <button
                        type="button"
                        class="rounded-md px-2 py-1 text-xs font-semibold hover:bg-gray-200 disabled:opacity-40"
                        :disabled="zoom >= 2"
                        @click="zoom = Math.round((zoom + 0.25) * 100) / 100"
                    >
                        Zoom +
                    </button>
                </div>
            </div>

            <div class="max-h-[70vh] overflow-y-auto bg-gray-100 px-4 py-4">
                <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 p-6 text-center text-sm font-medium text-red-700">
                    {{ error }}
                </div>
                <div v-else class="mx-auto w-fit min-w-0">
                    <div v-if="loading" class="flex flex-col items-center gap-3 py-20">
                        <div class="h-8 w-8 animate-spin rounded-full border-2 border-indigo-600 border-t-transparent" />
                        <span class="text-xs font-medium text-gray-500">Memuat dokumen...</span>
                    </div>
                    <VuePdfEmbed
                        :source="src"
                        :page="page"
                        :zoom="zoom"
                        class="rounded-lg shadow-md"
                        @loaded="onLoaded"
                        @progress="onProgress"
                        @loading-failed="onLoadingFailed"
                    />
                </div>
            </div>
        </template>
    </Modal>
</template>
