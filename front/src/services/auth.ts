import api from './api'
import type { User, LoginResponse } from '../types'

export const authService = {
  login: async (email: string, password: string): Promise<LoginResponse> => {
    console.log('[AuthService] login called', { email })
    try {
      const { data } = await api.post<LoginResponse>('/login', { email, password })
      console.log('[AuthService] login success', { user: data.user, hasToken: !!data.token })
      localStorage.setItem('tiktok_token', data.token)
      return data
    } catch (error: any) {
      console.error('[AuthService] login failed', { error: error.response?.data || error.message })
      throw error
    }
  },

  logout: async (): Promise<void> => {
    console.log('[AuthService] logout called')
    try {
      await api.post('/logout')
      console.log('[AuthService] logout success')
    } catch (error: any) {
      console.error('[AuthService] logout failed (ignoring)', { error: error.response?.data || error.message })
    }
    localStorage.removeItem('tiktok_token')
    console.log('[AuthService] Token removed from localStorage')
  },

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

  register: async (name: string, email: string, password: string): Promise<LoginResponse> => {
    console.log('[AuthService] register called', { name, email })
    try {
      const { data } = await api.post<LoginResponse>('/register', { name, email, password })
      console.log('[AuthService] register success', { user: data.user, hasToken: !!data.token })
      localStorage.setItem('tiktok_token', data.token)
      return data
    } catch (error: any) {
      console.error('[AuthService] register failed', { error: error.response?.data || error.message })
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
