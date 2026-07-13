import { useEffect } from 'react'
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
} from 'recharts'
import StatsCard from '../components/StatsCard'
import { useDashboardStore } from '../store'
import { useTranslation } from '../hooks/useTranslation'

function Dashboard() {
  const t = useTranslation()
  const { stats, dailyStats, isLoading, fetchStats } = useDashboardStore()

  useEffect(() => {
    fetchStats()
  }, [fetchStats])

  if (isLoading || !stats) {
    return (
      <div className="text-center text-[#888888] py-20">Loading...</div>
    )
  }

  return (
    <div>
      <h1 className="text-2xl font-bold text-white mb-6">{t.dashboard.title}</h1>

      <div className="grid grid-cols-4 gap-4 mb-8">
        <StatsCard
          title={t.dashboard.followers}
          value={stats.followers.toLocaleString()}
          change={stats.followersChange}
        />
        <StatsCard
          title={t.dashboard.views}
          value={stats.views.toLocaleString()}
          change={stats.viewsChange}
        />
        <StatsCard
          title={t.dashboard.likes}
          value={stats.likes.toLocaleString()}
          change={stats.likesChange}
        />
        <StatsCard
          title={t.dashboard.comments}
          value={stats.comments.toLocaleString()}
          change={stats.commentsChange}
        />
      </div>

      <div className="bg-[#1e1e1e] border border-[#2e2e2e] rounded-xl p-6">
        <h2 className="text-lg font-semibold text-white mb-4">
          {t.dashboard.dailyViews}
        </h2>
        <ResponsiveContainer width="100%" height={300}>
          <LineChart data={dailyStats}>
            <CartesianGrid strokeDasharray="3 3" stroke="#2e2e2e" />
            <XAxis dataKey="date" stroke="#888888" fontSize={12} />
            <YAxis stroke="#888888" fontSize={12} />
            <Tooltip
              contentStyle={{
                backgroundColor: '#1e1e1e',
                border: '1px solid #2e2e2e',
                borderRadius: '8px',
                color: '#fff',
              }}
            />
            <Line
              type="monotone"
              dataKey="views"
              stroke="#fe2c55"
              strokeWidth={2}
              dot={{ fill: '#fe2c55' }}
            />
          </LineChart>
        </ResponsiveContainer>
      </div>
    </div>
  )
}

export default Dashboard
