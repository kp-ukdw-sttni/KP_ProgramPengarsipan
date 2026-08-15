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
        hasRole('Superadmin', 'Operator', 'Staf TU')

    const canManageArsip = (arsip) => {
        if (!user.value) return false
        if (hasRole('Superadmin')) return true
        if (hasRole('Operator', 'Staf TU')) {
            return arsip.divisi_id === user.value.divisi_id
        }
        return false
    }

    const canSign = () => hasRole('Superadmin', 'Kaprodi', 'Dekan', 'Staf TU')

    const canViewFile = (arsip) => {
        if (!user.value) return false
        if (hasRole('Superadmin')) return true
        if (arsip.status_publikasi === 'Public') return true

        const staffSameDivisi =
            hasRole('Operator', 'Staf TU') && arsip.divisi_id === user.value.divisi_id

        if (arsip.status_publikasi === 'Internal') {
            return staffSameDivisi || hasRole('Dosen', 'Kaprodi', 'Dekan')
        }

        if (arsip.status_publikasi === 'Confidential') {
            return staffSameDivisi || hasRole('Kaprodi', 'Dekan')
        }

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
