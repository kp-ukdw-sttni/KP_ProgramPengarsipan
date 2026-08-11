<script setup>
import { computed, ref, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import Badge from '../../Components/ui/Badge.vue'
import Pagination from '../../Components/ui/Pagination.vue'
import Input from '../../Components/ui/Input.vue'
import Select from '../../Components/ui/Select.vue'
import SecondaryButton from '../../Components/ui/SecondaryButton.vue'
import { useRoute } from '../../Composables/useRoute'

const props = defineProps({
    logs: {
        type: Object,
        required: true,
    },
    users: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
})

const routeFn = useRoute()

const items = computed(() => props.logs.data ?? [])
const paginationLinks = computed(() => props.logs.links ?? [])

const userId = ref(props.filters.user_id ?? '')
const action = ref(props.filters.action ?? '')
const startDate = ref(props.filters.start_date ?? '')
const endDate = ref(props.filters.end_date ?? '')

let timer = null

const applyFilters = () => {
    const params = {}
    if (userId.value) params.user_id = userId.value
    if (action.value) params.action = action.value
    if (startDate.value) params.start_date = startDate.value
    if (endDate.value) params.end_date = endDate.value

    router.get(routeFn('audit.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['logs', 'filters'],
    })
}

watch([userId, action], () => {
    clearTimeout(timer)
    timer = setTimeout(applyFilters, 300)
})

const resetFilters = () => {
    userId.value = ''
    action.value = ''
    startDate.value = ''
    endDate.value = ''
    applyFilters()
}

onMounted(() => {
    userId.value = props.filters.user_id ?? ''
    action.value = props.filters.action ?? ''
    startDate.value = props.filters.start_date ?? ''
    endDate.value = props.filters.end_date ?? ''
})

const actionColors = {
    Login: 'green',
    Logout: 'gray',
    Create: 'blue',
    Update: 'indigo',
    Delete: 'red',
    Download: 'amber',
    'Create Arsip': 'blue',
    'Update Arsip': 'indigo',
    'Delete Arsip': 'red',
}

const formatDate = (value) => {
    if (!value) return '-'
    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return '-'
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    }).format(date)
}
</script>

<template>
    <AuthenticatedLayout title="Audit Log">
        <FlashMessages />

        <div class="mb-4">
            <h3 class="text-lg font-bold text-gray-900">Audit Log Aktivitas</h3>
            <p class="text-xs text-gray-500">{{ props.logs.total ?? 0 }} aktivitas tercatat.</p>
        </div>

        <div class="mb-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                <Select v-model="userId">
                    <option value="">Semua Pengguna</option>
                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                </Select>
                <Select v-model="action">
                    <option value="">Semua Aksi</option>
                    <option value="Login">Login</option>
                    <option value="Logout">Logout</option>
                    <option value="Download">Download</option>
                    <option value="Create">Create</option>
                    <option value="Update">Update</option>
                    <option value="Delete">Delete</option>
                </Select>
                <Input v-model="startDate" type="date" />
                <Input v-model="endDate" type="date" />
            </div>
            <div class="mt-3 flex justify-end">
                <SecondaryButton type="button" @click="resetFilters">Reset</SecondaryButton>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div v-if="items.length === 0" class="py-16 text-center">
                <p class="text-sm font-medium text-gray-500">Tidak ada log yang cocok.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Waktu</th>
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Pengguna</th>
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Aksi</th>
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Detail</th>
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <tr v-for="log in items" :key="log.id" class="transition-colors duration-150 hover:bg-indigo-50/40">
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-gray-500">
                                {{ formatDate(log.created_at) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ log.user?.name ?? '-' }}</div>
                                <div class="text-[11px] text-gray-400">{{ log.user?.email }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <Badge :color="actionColors[log.action] ?? 'gray'">
                                    {{ log.action }}
                                </Badge>
                            </td>
                            <td class="max-w-md px-4 py-3 text-xs leading-relaxed text-gray-600">
                                {{ log.details }}
                                <span v-if="log.arsip" class="text-gray-400">
                                    &mdash; <code>{{ log.arsip.nomor_arsip }}</code>
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-gray-400">
                                {{ log.ip_address ?? '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="paginationLinks.length > 3" class="border-t border-gray-100 px-4 py-3">
                <Pagination :links="paginationLinks" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
