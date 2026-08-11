<script setup>
import { computed, ref, watch, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import FlashMessages from '../../Components/ui/FlashMessages.vue'
import Badge from '../../Components/ui/Badge.vue'
import Pagination from '../../Components/ui/Pagination.vue'
import Input from '../../Components/ui/Input.vue'
import Select from '../../Components/ui/Select.vue'
import PrimaryButton from '../../Components/ui/PrimaryButton.vue'
import { useRoute } from '../../Composables/useRoute'

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
        default: () => [],
    },
    divisi: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
})

const routeFn = useRoute()

const items = computed(() => props.users.data ?? [])
const paginationLinks = computed(() => props.users.links ?? [])

const search = ref(props.filters.search ?? '')
const role = ref(props.filters.role ?? '')
const statusAkun = ref(props.filters.status_akun ?? '')

let searchTimer = null

const applyFilters = () => {
    const params = {}
    if (search.value.trim()) params.search = search.value.trim()
    if (role.value) params.role = role.value
    if (statusAkun.value) params.status_akun = statusAkun.value

    router.get(routeFn('users.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['users', 'filters'],
    })
}

watch(search, () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(applyFilters, 350)
})

watch([role, statusAkun], applyFilters)

onMounted(() => {
    search.value = props.filters.search ?? ''
    role.value = props.filters.role ?? ''
    statusAkun.value = props.filters.status_akun ?? ''
})

const statusBadge = (status) => {
    const map = {
        Aktif: { label: 'Aktif', color: 'green' },
        Suspended: { label: 'Suspended', color: 'amber' },
        Deactivated: { label: 'Nonaktif', color: 'gray' },
    }
    return map[status] ?? { label: status, color: 'gray' }
}
</script>

<template>
    <AuthenticatedLayout title="Manajemen Pengguna">
        <FlashMessages />

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Manajemen Pengguna</h3>
                <p class="text-xs text-gray-500">{{ props.users.total ?? 0 }} pengguna terdaftar.</p>
            </div>
            <PrimaryButton type="button" @click="routeFn('users.create')">
                <Link :href="routeFn('users.create')" class="text-white">Tambah Pengguna</Link>
            </PrimaryButton>
        </div>

        <div class="mb-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                <Input v-model="search" type="text" placeholder="Cari nama, email, atau NIK/NIM..." />
                <Select v-model="role">
                    <option value="">Semua Role</option>
                    <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name }}</option>
                </Select>
                <Select v-model="statusAkun">
                    <option value="">Semua Status</option>
                    <option value="Aktif">Aktif</option>
                    <option value="Suspended">Suspended</option>
                    <option value="Deactivated">Nonaktif</option>
                </Select>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div v-if="items.length === 0" class="py-16 text-center">
                <p class="text-sm font-medium text-gray-500">Tidak ada pengguna ditemukan.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Pengguna</th>
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Role</th>
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Divisi</th>
                            <th scope="col" class="px-5 py-3.5 text-left font-bold">Status</th>
                            <th scope="col" class="px-5 py-3.5 text-right font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <tr v-for="user in items" :key="user.id" class="transition-colors duration-150 hover:bg-indigo-50/40">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ user.name }}</div>
                                <div class="mt-0.5 text-[11px] text-gray-400">
                                    {{ user.email }}
                                    <span v-if="user.nik_nim">&bull; {{ user.nik_nim }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <Badge
                                        v-for="r in user.roles"
                                        :key="r.id"
                                        :color="r.name === 'Superadmin' ? 'red' : 'indigo'"
                                    >
                                        {{ r.name }}
                                    </Badge>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600">{{ user.divisi?.name ?? '-' }}</td>
                            <td class="px-5 py-4">
                                <Badge :color="statusBadge(user.status_akun).color">
                                    {{ statusBadge(user.status_akun).label }}
                                </Badge>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <Link
                                    :href="routeFn('users.edit', user.id)"
                                    class="rounded-md px-2 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-100"
                                >
                                    Edit
                                </Link>
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
