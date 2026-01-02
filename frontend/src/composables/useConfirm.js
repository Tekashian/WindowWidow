import { getCurrentInstance } from 'vue'

export function useConfirm() {
  const instance = getCurrentInstance()
  const confirmDialog = instance?.appContext.config.globalProperties.$confirmDialog
  
  if (!confirmDialog) {
    console.error('ConfirmDialog not available. Make sure it is registered globally.')
    return {
      confirm: () => Promise.reject('ConfirmDialog not available')
    }
  }
  
  const confirm = (options) => {
    return confirmDialog.open(options)
  }
  
  return {
    confirm
  }
}
