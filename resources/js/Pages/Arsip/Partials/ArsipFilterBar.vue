<script setup>
import { ref, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import Input from '../../../Components/ui/Input.vue'
import Select from '../../../Components/ui/Select.vue'
import SecondaryButton from '../../../Components/ui/SecondaryButton.vue'
import { useRoute } from '../../../Composables/useRoute'

const props = defineProps({
    filters: {
        type: Object,
        default: () => ({}),
    },
    kategori: {
        type: Array,
        default: () => [],
    },
    divisiTree: {
        type: Array,
        default: () => [],
    },
    tahunList: {
        type: Array,
        default: () => [],
    },
})

const routeFn = useRoute()

const search = ref(props.filters.search ?? '')
const kategoriId = ref(props.filters.kategori_id ?? '')
const divisiId = ref(props.filters.divisi_id ?? '')
const tahun = ref(props.filters.tahun ?? '')
const statusPublikasi = ref(props.filters.status_publikasi ?? '')
const status = ref(props.filters.status ?? '')

let searchTimer = null

const applyFilters = (preserve = true) => {
    const params = {}
    if (search.value.trim()) params.search = search.value.trim()
    if (kategoriId.value) params.kategori_id = kategoriId.value
    if (divisiId.value) params.divisi_id = divisiId.value
    if (tahun.value) params.tahun = tahun.value
    if (statusPublikasi.value) params.status_publikasi = statusPublikasi.value
    if (status.value) params.status = status.value

    router.get(routeFn('arsip.index'), params, {
        preserveState: preserve,
        preserveScroll: true,
        replace: true,
        only: ['arsip', 'filters'],
    })
}

watch(search, (value) => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => applyFilters(false), 350)
})

watch([kategoriId, divisiId, tahun, statusPublikasi, status], () => {
    applyFilters(false)
})

const resetFilters = () => {
    search.value = ''
    kategoriId.value = ''
    divisiId.value = ''
    tahun.value = ''
    statusPublikasi.value = ''
    status.value = ''
    applyFilters(false)
}

const hasActiveFilters = () =>
    search.value.trim() !== '' ||
    kategoriId.value !== '' ||
    divisiId.value !== '' ||
    tahun.value !== '' ||
    statusPublikasi.value !== '' ||
    status.value !== ''

onMounted(() => {
    const syncFromProps = () => {
        search.value = props.filters.search ?? ''
        kategoriId.value = props.filters.kategori_id ?? ''
        divisiId.value = props.filters.divisi_id ?? ''
        tahun.value = props.filters.tahun ?? ''
        statusPublikasi.value = props.filters.status_publikasi ?? ''
        status.value = props.filters.status ?? ''
    }
    syncFromProps()
})
</script>

<template>
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
            <h4 class="text-sm font-bold text-gray-800">Filter Arsip</h4>
            <span v-if="hasActiveFilters()" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter aktif
            </span>
        </div>
        <div class="grid grid-cols-1 gap-3.5 md:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-1">
                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-gray-500">Pencarian</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                        </svg>
                    </div>
                    <Input v-model="search" type="text" class="pl-9" placeholder="Cari judul, nomor arsip/surat, tags..." />
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-gray-500">Kategori</label>
                <Select v-model="kategoriId">
                    <option value="">Semua Kategori</option>
                    <option v-for="k in kategori" :key="k.id" :value="k.id">{{ k.name }}</option>
                </Select>
            </div>

            <div>
                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-gray-500">Divisi</label>
                <Select v-model="divisiId">
                    <option value="">Semua Divisi</option>
                    <optgroup v-for="f in divisiTree" :key="f.id" :label="f.name">
                        <option v-for="c in f.children" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </optgroup>
                </Select>
            </div>

            <div>
                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-gray-500">Tahun</label>
                <Select v-model="tahun">
                    <option value="">Semua Tahun</option>
                    <option v-for="t in tahunList" :key="t" :value="t">{{ t }}</option>
                </Select>
            </div>

            <div>
                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-gray-500">Publikasi</label>
                <Select v-model="statusPublikasi">
                    <option value="">Semua Publikasi</option>
                    <option value="Public">Public</option>
                    <option value="Restricted">Restricted</option>
                </Select>
            </div>

            <div>
                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-gray-500">Status</label>
                <Select v-model="status">
                    <option value="">Semua Status</option>
                    <option value="Aktif">Aktif</option>
                    <option value="Expired">Expired</option>
                    <option value="Dimusnahkan">Dimusnahkan</option>
                </Select>
            </div>

            <div class="flex items-end justify-start md:justify-end lg:col-span-2">
                <SecondaryButton v-if="hasActiveFilters()" type="button" @click="resetFilters">
                    Reset Filter
                </SecondaryButton>
            </div>
        </div>
    </div>
</template>
