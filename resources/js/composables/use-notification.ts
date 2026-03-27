import { ref } from 'vue'

export interface Notification {
  id: string
  type: 'success' | 'error' | 'warning' | 'info'
  title: string
  message?: string
  duration?: number
}

const notifications = ref<Notification[]>([])

export function useNotification() {
  const show = (notification: Omit<Notification, 'id'>) => {
    const id = Math.random().toString(36).substr(2, 9)
    const newNotification: Notification = {
      id,
      duration: 5000,
      ...notification
    }
    
    notifications.value.push(newNotification)
    
    if (newNotification.duration && newNotification.duration > 0) {
      setTimeout(() => {
        remove(id)
      }, newNotification.duration)
    }
    
    return id
  }
  
  const remove = (id: string) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }
  
  const clear = () => {
    notifications.value.splice(0)
  }
  
  const success = (title: string, message?: string) => {
    return show({ type: 'success', title, message })
  }
  
  const error = (title: string, message?: string) => {
    return show({ type: 'error', title, message })
  }
  
  const warning = (title: string, message?: string) => {
    return show({ type: 'warning', title, message })
  }
  
  const info = (title: string, message?: string) => {
    return show({ type: 'info', title, message })
  }
  
  return {
    notifications: notifications.value,
    show,
    remove,
    clear,
    success,
    error,
    warning,
    info
  }
}
