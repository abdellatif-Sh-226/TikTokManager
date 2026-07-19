import api from './api'
import type { Stats, DailyStats } from '../types'

export const dashboardService = {
  getStats: async (): Promise<Stats> => {
    console.log('[DashboardService] getStats called')
    try {
      const { data } = await api.get<{ data: Stats }>('/dashboard/stats')
      console.log('[DashboardService] getStats success', { stats: data.data })
      return data.data
    } catch (error: any) {
      console.error('[DashboardService] getStats failed', { error: error.response?.data || error.message })
      throw error
    }
  },

  getDailyStats: async (): Promise<DailyStats[]> => {
    console.log('[DashboardService] getDailyStats called')
    try {
      const { data } = await api.get<{ data: DailyStats[] }>('/dashboard/daily-stats')
      console.log('[DashboardService] getDailyStats success', { count: data.data?.length, stats: data.data })
      return data.data
    } catch (error: any) {
      console.error('[DashboardService] getDailyStats failed', { error: error.response?.data || error.message })
      throw error
    }
  },
}
