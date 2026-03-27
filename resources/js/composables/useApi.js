import { router } from '@inertiajs/vue3'

export function useApi() {
  const get = async (url, params = {}) => {
    return new Promise((resolve, reject) => {
      router.get(url, params, {
        onSuccess: (response) => resolve(response.props),
        onError: (errors) => reject({ response: { status: 422, data: { errors } } })
      })
    })
  }

  const post = async (url, data = {}) => {
    return new Promise((resolve, reject) => {
      router.post(url, data, {
        onSuccess: (response) => resolve(response.props),
        onError: (errors) => reject({ response: { status: 422, data: { errors } } })
      })
    })
  }

  const put = async (url, data = {}) => {
    return new Promise((resolve, reject) => {
      router.put(url, data, {
        onSuccess: (response) => resolve(response.props),
        onError: (errors) => reject({ response: { status: 422, data: { errors } } })
      })
    })
  }

  const deleteRequest = async (url) => {
    return new Promise((resolve, reject) => {
      router.delete(url, {
        onSuccess: (response) => resolve(response.props),
        onError: (errors) => reject({ response: { status: 422, data: { errors } } })
      })
    })
  }

  return {
    get,
    post,
    put,
    delete: deleteRequest
  }
}
