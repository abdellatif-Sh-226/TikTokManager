import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
})

console.log('[API] Initialized with baseURL:', import.meta.env.VITE_API_URL || 'http://localhost:8000/api')

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('tiktok_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
    console.log('[API] Request:', config.method?.toUpperCase(), config.url, '| Token present:', token.substring(0, 15) + '...')
  } else {
    console.log('[API] Request:', config.method?.toUpperCase(), config.url, '| No token')
  }
  return config
})

api.interceptors.response.use(
  (response) => {
    console.log('[API] Response:', response.status, response.config.url, '| Data keys:', Object.keys(response.data || {}))
    return response
  },
  (error) => {
    console.error('[API] Error:', error.response?.status, error.config?.url, '| Message:', error.response?.data?.message || error.message)
    console.error('[API] Error response data:', error.response?.data)
    return Promise.reject(error)
  }
)

export default api
