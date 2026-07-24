import api from './api'
import type { User } from '../types'

export const authService = {
  getUser: async (): Promise<User> => {
    console.log('[AuthService] getUser called')
    try {
      const { data } = await api.get<{ user: User }>('/user')
      console.log('[AuthService] getUser success', { user: data.user })
      return data.user
    } catch (error: any) {
      console.error('[AuthService] getUser failed', { error: error.response?.data || error.message })
      throw error
    }
  },

  getTikTokAuthUrl: async (): Promise<string> => {
    console.log('[AuthService] getTikTokAuthUrl called')
    try {
      const { data } = await api.get<{ url: string }>('/auth/tiktok/redirect')
      console.log('[AuthService] TikTok auth URL received', { url: data.url })
      return data.url
    } catch (error: any) {
      console.error('[AuthService] getTikTokAuthUrl failed', { error: error.response?.data || error.message })
      throw error
    }
  },
}
