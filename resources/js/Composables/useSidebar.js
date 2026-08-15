import { ref, watch } from 'vue'

const collapsed = ref(localStorage.getItem('sidebar-collapsed') === '1')

watch(collapsed, (val) => {
    localStorage.setItem('sidebar-collapsed', val ? '1' : '0')
})

export function useSidebar() {
    const toggle = () => {
        collapsed.value = !collapsed.value
    }

    return { collapsed, toggle }
}
