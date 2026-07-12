import api from './api'
import type { Stats, DailyStats } from '../types'

export const dashboardService = {
  getStats: async (): Promise<Stats> => {
    const { data } = await api.get<{ data: Stats }>('/dashboard/stats')
    return data.data
  },

  getDailyStats: async (): Promise<DailyStats[]> => {
    const { data } = await api.get<{ data: DailyStats[] }>('/dashboard/daily-stats')
    return data.data
  },
}
