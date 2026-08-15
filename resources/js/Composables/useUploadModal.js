import { reactive } from 'vue'

const state = reactive({
    open: false,
})

export function useUploadModal() {
    const open = () => {
        state.open = true
    }

    const close = () => {
        state.open = false
    }

    return { state, open, close }
}
