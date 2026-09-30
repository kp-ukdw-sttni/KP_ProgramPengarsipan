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

    // Access is decided server-side (App\Support\AksesDokumen) and shipped per row
    // as `bisa_diakses`. It deliberately does NOT re-derive the rule from
    // status_publikasi here: the previous version ignored approved access
    // requests entirely, so an approved user saw a locked document and no
    // buttons at all.
    const canViewFile = (arsip) => {
        if (!user.value) return false
        return arsip?.bisa_diaccessed === true || arsip?.bisa_diakses === true
    }

    // True when the row is restricted and this user may ask for it.
    const canRequestAccess = (arsip) => {
        if (!user.value) return false
        if (arsip?.bisa_diakses) return false
        if (!arsip?.butuh_persetujuan) return false
        return arsip?.status_permintaan !== 'Pending'
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
        canRequestAccess,
    }
}
