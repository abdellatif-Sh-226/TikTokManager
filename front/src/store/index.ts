import { create } from 'zustand'
import type { User, Post, Stats, DailyStats, TikTokPublishStatus } from '../types'
import { authService } from '../services/auth'
import { dashboardService } from '../services/dashboard'
import { postsService } from '../services/posts'

export interface AuthStore {
  user: User | null
  isAuthenticated: boolean
  isLoading: boolean
  initialized: boolean
  login: (email: string, password: string) => Promise<void>
  register: (name: string, email: string, password: string) => Promise<void>
  loginWithTikTok: () => Promise<void>
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
  error: string | null
  fetchPosts: () => Promise<void>
  addPost: (formData: FormData) => Promise<Post & { tiktokStatus?: TikTokPublishStatus }>
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
    console.log('[AuthStore] login called', { email })
    set({ isLoading: true })
    try {
      const { user } = await authService.login(email, password)
      console.log('[AuthStore] login success', { user })
      set({ user, isAuthenticated: true, isLoading: false })
    } catch (error) {
      console.error('[AuthStore] login failed', { error })
      set({ isLoading: false })
      throw new Error('Invalid credentials')
    }
  },
  register: async (name, email, password) => {
    console.log('[AuthStore] register called', { name, email })
    set({ isLoading: true })
    try {
      const { user } = await authService.register(name, email, password)
      console.log('[AuthStore] register success', { user })
      set({ user, isAuthenticated: true, isLoading: false })
    } catch (error) {
      console.error('[AuthStore] register failed', { error })
      set({ isLoading: false })
      throw error
    }
  },
  loginWithTikTok: async () => {
    console.log('[AuthStore] loginWithTikTok called')
    try {
      const url = await authService.getTikTokAuthUrl()
      console.log('[AuthStore] Redirecting to TikTok auth:', url)
      window.location.href = url
    } catch (error) {
      console.error('[AuthStore] loginWithTikTok failed', { error })
    }
  },
  logout: async () => {
    console.log('[AuthStore] logout called')
    try {
      await authService.logout()
    } catch {
      // ignore
    }
    set({ user: null, isAuthenticated: false })
    console.log('[AuthStore] logout complete')
  },
  init: async () => {
    const token = localStorage.getItem('tiktok_token')
    console.log('[AuthStore] init called', { hasToken: !!token, tokenPrefix: token ? token.substring(0, 15) + '...' : null })

    if (!token) {
      console.log('[AuthStore] No token found, marking as initialized (not authenticated)')
      set({ initialized: true })
      return
    }
    try {
      console.log('[AuthStore] Validating token with /user endpoint')
      const user = await authService.getUser()
      console.log('[AuthStore] Token valid, user authenticated', { user })
      set({ user, isAuthenticated: true, initialized: true })
    } catch (error) {
      console.error('[AuthStore] Token invalid, removing', { error })
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
    console.log('[DashboardStore] fetchStats called')
    set({ isLoading: true })
    try {
      const [stats, dailyStats] = await Promise.all([
        dashboardService.getStats(),
        dashboardService.getDailyStats(),
      ])
      console.log('[DashboardStore] fetchStats success', { stats, dailyStatsCount: dailyStats?.length })
      set({ stats, dailyStats, isLoading: false })
    } catch (error) {
      console.error('[DashboardStore] fetchStats failed', { error })
      set({ isLoading: false })
    }
  },
}))

export const usePostsStore = create<PostsStore>((set, get) => ({
  posts: [],
  isLoading: false,
  error: null,
  fetchPosts: async () => {
    console.log('[PostsStore] fetchPosts called')
    set({ isLoading: true, error: null })
    try {
      const posts = await postsService.getAll()
      console.log('[PostsStore] fetchPosts success', { count: posts?.length })
      set({ posts, isLoading: false })
    } catch (error: any) {
      console.error('[PostsStore] fetchPosts failed', { error })
      set({ isLoading: false, error: 'Failed to load posts. Make sure the backend server is running.' })
    }
  },
  addPost: async (formData) => {
    console.log('[PostsStore] addPost called')
    try {
      const post = await postsService.create(formData)
      console.log('[PostsStore] addPost success', { post, tiktokStatus: post?.tiktokStatus })
      if (post && post.id) {
        set({ posts: [post, ...get().posts.filter(p => p && p.id)] })
      }
      return post
    } catch (error) {
      console.error('[PostsStore] addPost failed', { error })
      throw error
    }
  },
  deletePost: async (id) => {
    console.log('[PostsStore] deletePost called', { id })
    try {
      await postsService.remove(id)
      set({ posts: get().posts.filter(p => p.id !== id) })
      console.log('[PostsStore] deletePost success', { id })
    } catch (error) {
      console.error('[PostsStore] deletePost failed', { id, error })
      throw error
    }
  },
}))

export const useSettingsStore = create<SettingsStore>((set) => ({
  language: (localStorage.getItem('lang') as 'en' | 'fr') || 'en',
  setLanguage: (lang) => {
    console.log('[SettingsStore] setLanguage called', { lang })
    localStorage.setItem('lang', lang)
    set({ language: lang })
  },
}))
