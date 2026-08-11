import { getCurrentInstance } from 'vue'

export function useRoute() {
    const instance = getCurrentInstance()
    const proxy = instance?.proxy ?? {}
    return typeof proxy.route === 'function' ? proxy.route : (name) => name
}
