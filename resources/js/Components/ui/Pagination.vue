<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    links: {
        type: Array,
        required: true,
    },
})

const items = computed(() => props.links.slice(1, -1))

const current = computed(() => {
    const active = props.links.find((link) => link.active)
    if (!active) return '-'
    return /(\d+)/.exec(active.label)?.[1] ?? '-'
})

const last = computed(() => {
    const numbers = props.links
        .map((link) => /(\d+)/.exec(link.label)?.[1])
        .filter(Boolean)
        .map(Number)
    return numbers.length ? Math.max(...numbers) : '-'
})
</script>

<template>
    <nav v-if="links.length > 3" class="mt-4 flex items-center justify-between">
        <div class="flex flex-1 justify-between sm:hidden">
            <template v-for="link in links" :key="link.url ?? link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    :preserve-scroll="true"
                    :preserve-state="true"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                >
                    <span v-if="link.label === '&laquo; Previous'">Previous</span>
                    <span v-else-if="link.label === 'Next &raquo;'">Next</span>
                    <span v-else>{{ link.label }}</span>
                </Link>
            </template>
        </div>

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Menampilkan halaman
                    <span class="font-semibold">{{ current }}</span>
                    dari
                    <span class="font-semibold">{{ last }}</span>
                </p>
            </div>
            <div>
                <nav class="isolate inline-flex divide-x divide-gray-200 overflow-hidden rounded-lg border border-gray-200 shadow-sm">
                    <template v-for="(link, i) in items" :key="i">
                        <span
                            v-if="link.url === null"
                            class="relative inline-flex items-center bg-white px-4 py-2 text-sm font-medium text-gray-400"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            :href="link.url"
                            :preserve-scroll="true"
                            :preserve-state="true"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium focus:z-20 focus:outline-offset-0"
                            :class="
                                link.active
                                    ? 'z-10 bg-indigo-600 text-white'
                                    : 'bg-white text-gray-500 hover:bg-gray-50'
                            "
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </div>
    </nav>
</template>
