import api from './api'
import type { User, LoginResponse } from '../types'

export const authService = {
  login: async (email: string, password: string): Promise<LoginResponse> => {
    const { data } = await api.post<LoginResponse>('/login', { email, password })
    localStorage.setItem('tiktok_token', data.token)
    return data
  },

  logout: async (): Promise<void> => {
    await api.post('/logout')
    localStorage.removeItem('tiktok_token')
  },

  getUser: async (): Promise<User> => {
    const { data } = await api.get<{ user: User }>('/user')
    return data.user
  },

  getTikTokAuthUrl: async (): Promise<string> => {
    const { data } = await api.get<{ url: string }>('/auth/tiktok/redirect')
    return data.url
  },
}
