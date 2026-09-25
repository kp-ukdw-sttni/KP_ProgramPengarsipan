import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useAuth() {
    const page = usePage()

    const user = computed(() => page.props.auth?.user ?? null)

    const roles = computed(() => user.value?.roles ?? [])

    const permissions = computed(() => user.value?.permissions ?? [])

    const hasRole = (...roleNames) => {
        return roleNames.some((role) => roles.value.includes(role))
    }

    const hasPermission = (permission) => {
        return permissions.value.includes(permission)
    }

    const isStaffArsip = () =>
        hasRole('Admin', 'Superadmin')

    const canManageArsip = (arsip) => {
        if (!user.value) return false
        if (hasRole('Admin', 'Superadmin')) return true
        if (arsip.uploader_id === user.value.id) return true
        return false
    }

    const canSign = () => hasRole('Admin', 'Superadmin', 'Dosen')

    const canViewFile = (arsip) => {
        if (!user.value) return false
        if (hasRole('Admin', 'Superadmin')) return true
        if (['Internal'].includes(arsip.status_publikasi)) return true
        if (arsip.uploader_id === user.value.id) return true
        return false
    }

    return {
        user,
        roles,
        permissions,
        hasRole,
        hasPermission,
        isStaffArsip,
        canManageArsip,
        canSign,
        canViewFile,
    }
}
