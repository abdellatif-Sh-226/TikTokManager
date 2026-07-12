import { create } from 'zustand'
import type { User, Post, Stats, DailyStats } from '../types'
import { authService } from '../services/auth'
import { dashboardService } from '../services/dashboard'
import { postsService } from '../services/posts'

export interface AuthStore {
  user: User | null
  isAuthenticated: boolean
  isLoading: boolean
  initialized: boolean
  login: (email: string, password: string) => Promise<void>
  logout: () => Promise<void>
  init: () => Promise<void>
}

export interface DashboardStore {
  stats: Stats | null
  dailyStats: DailyStats[]
  isLoading: boolean
  fetchStats: () => Promise<void>
}

export interface PostsStore {
  posts: Post[]
  isLoading: boolean
  fetchPosts: () => Promise<void>
  addPost: (data: Partial<Post>) => Promise<void>
  deletePost: (id: string) => Promise<void>
}

export interface SettingsStore {
  language: 'en' | 'fr'
  setLanguage: (lang: 'en' | 'fr') => void
}

export const useAuthStore = create<AuthStore>((set) => ({
  user: null,
  isAuthenticated: false,
  isLoading: false,
  initialized: false,
  login: async (email, password) => {
    set({ isLoading: true })
    try {
      const { user } = await authService.login(email, password)
      set({ user, isAuthenticated: true, isLoading: false })
    } catch {
      set({ isLoading: false })
      throw new Error('Invalid credentials')
    }
  },
  logout: async () => {
    try {
      await authService.logout()
    } catch {
      // ignore logout errors
    }
    set({ user: null, isAuthenticated: false })
  },
  init: async () => {
    const token = localStorage.getItem('tiktok_token')
    if (!token) {
      set({ initialized: true })
      return
    }
    try {
      const user = await authService.getUser()
      set({ user, isAuthenticated: true, initialized: true })
    } catch {
      localStorage.removeItem('tiktok_token')
      set({ initialized: true })
    }
  },
}))

export const useDashboardStore = create<DashboardStore>((set) => ({
  stats: null,
  dailyStats: [],
  isLoading: false,
  fetchStats: async () => {
    set({ isLoading: true })
    try {
      const [stats, dailyStats] = await Promise.all([
        dashboardService.getStats(),
        dashboardService.getDailyStats(),
      ])
      set({ stats, dailyStats, isLoading: false })
    } catch {
      set({ isLoading: false })
    }
  },
}))

export const usePostsStore = create<PostsStore>((set, get) => ({
  posts: [],
  isLoading: false,
  fetchPosts: async () => {
    set({ isLoading: true })
    try {
      const posts = await postsService.getAll()
      set({ posts, isLoading: false })
    } catch {
      set({ isLoading: false })
    }
  },
  addPost: async (data) => {
    const post = await postsService.create(data)
    set({ posts: [post, ...get().posts] })
  },
  deletePost: async (id) => {
    await postsService.remove(id)
    set({ posts: get().posts.filter(p => p.id !== id) })
  },
}))

export const useSettingsStore = create<SettingsStore>((set) => ({
  language: (localStorage.getItem('lang') as 'en' | 'fr') || 'en',
  setLanguage: (lang) => {
    localStorage.setItem('lang', lang)
    set({ language: lang })
  },
}))
