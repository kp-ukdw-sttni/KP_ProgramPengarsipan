import { reactive } from 'vue'

const state = reactive({
    visible: false,
    status: 'loading',
    message: '',
    consumed: { success: '', error: '' },
})

let timer = null

export function useUploadFeedback() {
    const start = (message = 'Mengunggah dokumen...') => {
        clearTimeout(timer)
        state.visible = true
        state.status = 'loading'
        state.message = message
    }

    const success = (message = 'Upload berhasil') => {
        clearTimeout(timer)
        state.status = 'success'
        state.message = message
        state.consumed.success = message
        timer = setTimeout(() => {
            state.visible = false
        }, 1700)
    }

    const error = (message = 'Upload gagal') => {
        clearTimeout(timer)
        state.status = 'error'
        state.message = message
        state.consumed.error = message
        timer = setTimeout(() => {
            state.visible = false
        }, 2200)
    }

    return { state, start, success, error }
}
