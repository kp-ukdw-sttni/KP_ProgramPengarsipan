import { route } from 'ziggy-js'
import { usePage } from '@inertiajs/vue3'

/**
 * Composable that returns Ziggy's route() helper pre-configured
 * with the Ziggy config from Inertia's shared props.
 *
 * Usage:
 *   const routeFn = useRoute()
 *   routeFn('dashboard')           // → generate URL
 *   routeFn().current('dashboard') // → check active route
 */
export function useRoute() {
    const page = usePage()

    return (name, params, absolute) => {
        const ziggy = page.props?.ziggy ?? undefined
        return route(name, params, absolute, ziggy)
    }
}
