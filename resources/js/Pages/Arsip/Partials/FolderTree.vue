<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useRoute } from '../../../Composables/useRoute'

const props = defineProps({
    node: {
        type: Object,
        required: true,
    },
    depth: {
        type: Number,
        default: 0,
    },
    baseDivisiId: {
        type: [Number, null],
        default: null,
    },
})

const routeFn = useRoute()
const open = ref(false)

const isFakultas = props.depth === 0
const isProdi = props.depth === 1
const isTahun = props.depth === 2

const hasChildren = computed(() =>
    isFakultas ? props.node.children?.length > 0
        : isProdi ? props.node.years?.length > 0
            : isTahun ? props.node.kategori?.length > 0
                : false,
)

const expandable = isFakultas || isProdi || isTahun

const indent = { paddingLeft: `${8 + props.depth * 20}px` }

const folderQuery = (node) => {
    if (isProdi) {
        return { divisi_id: props.node.id }
    }
    if (isTahun) {
        return { divisi_id: props.baseDivisiId, tahun: props.node.tahun }
    }
    return { divisi_id: props.baseDivisiId, tahun: props.node.tahun, kategori_id: props.node.id }
}
</script>

<template>
    <li>
        <div class="flex items-center gap-2 rounded-lg py-1.5 pr-2 transition hover:bg-gray-100" :style="indent">
            <button
                v-if="expandable && hasChildren"
                type="button"
                class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-gray-400 hover:bg-gray-200"
                @click="open = !open"
            >
                <svg
                    class="h-3.5 w-3.5 transition-transform"
                    :class="open ? 'rotate-90' : ''"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>
            <span v-else class="h-5 w-5 shrink-0" />

            <template v-if="expandable && hasChildren">
                <span
                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md"
                    :class="isFakultas ? 'bg-indigo-100 text-indigo-600' : isProdi ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-500'"
                >
                    <svg v-if="isFakultas || isProdi" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    <svg v-else class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </span>
                <span class="min-w-0 flex-1 truncate text-sm font-semibold text-gray-800">{{ node.name }}</span>
                <span class="shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold text-gray-500">
                    {{ node.count ?? node.total }}
                </span>
            </template>

            <Link
                v-else-if="isTahun"
                :href="routeFn('arsip.index', { divisi_id: baseDivisiId, tahun: node.tahun })"
                class="flex min-w-0 flex-1 items-center gap-2 text-sm font-medium text-gray-700 hover:text-indigo-600"
            >
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-gray-100 text-gray-500">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </span>
                <span class="truncate">Tahun {{ node.tahun }}</span>
                <span class="ml-auto shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold text-gray-500">
                    {{ node.total }}
                </span>
            </Link>

            <Link
                v-else
                :href="routeFn('arsip.index', folderQuery(node))"
                class="flex min-w-0 flex-1 items-center gap-2 text-sm text-gray-600 hover:text-indigo-600"
            >
                <span class="h-4 w-4 shrink-0 text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
                <span class="truncate">{{ node.name }}</span>
                <span class="ml-auto shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold text-gray-500">
                    {{ node.count ?? node.total }}
                </span>
            </Link>
        </div>

        <ul v-if="expandable && open && hasChildren" class="space-y-0.5">
            <template v-for="child in (isFakultas ? node.children : isProdi ? node.years : node.kategori)" :key="child.id ?? child.tahun ?? child.name">
                <FolderTree
                    :node="child"
                    :depth="depth + 1"
                    :base-divisi-id="isFakultas ? child.id : baseDivisiId"
                />
            </template>
        </ul>
    </li>
</template>
