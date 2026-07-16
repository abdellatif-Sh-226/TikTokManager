import { useEffect } from 'react'
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  Area,
  AreaChart,
} from 'recharts'
import StatsCard from '../components/StatsCard'
import { useDashboardStore, useAuthStore } from '../store'
import { useTranslation } from '../hooks/useTranslation'

function Dashboard() {
  const t = useTranslation()
  const { stats, dailyStats, isLoading, fetchStats } = useDashboardStore()
  const { loginWithTikTok } = useAuthStore()

  useEffect(() => {
    fetchStats()
  }, [fetchStats])

  if (isLoading || !stats) {
    return (
      <div className="flex items-center justify-center h-[60vh]">
        <div className="text-center">
          <div className="animate-spin w-8 h-8 border-2 border-[#fe2c55] border-t-transparent rounded-full mx-auto mb-4" />
          <p className="text-[#888888] text-sm">{t.dashboard.title}...</p>
        </div>
      </div>
    )
  }

  const hasTikTok = stats.avatar || stats.followers > 0 || stats.views > 0 || stats.likes > 0

  return (
    <div>
      {/* Header with Avatar */}
      <div className="flex items-center gap-4 mb-8 bg-[#1e1e1e] border border-[#2e2e2e] rounded-xl p-5">
        {stats.avatar ? (
          <img
            src={stats.avatar}
            alt="avatar"
            className="w-14 h-14 rounded-full border-2 border-[#fe2c55] object-cover"
          />
        ) : (
          <div className="w-14 h-14 rounded-full bg-[#2e2e2e] flex items-center justify-center text-2xl">
            🎵
          </div>
        )}
        <div className="flex-1">
          <h1 className="text-2xl font-bold text-white">
            {stats.displayName || t.dashboard.title}
          </h1>
          {stats.username && (
            <p className="text-sm text-[#888888]">@{stats.username}</p>
          )}
          {!hasTikTok && (
            <p className="text-sm text-[#888888] mt-1">{t.dashboard.noTikTok}</p>
          )}
        </div>
        {!hasTikTok && (
          <button
            onClick={loginWithTikTok}
            className="px-4 py-2 bg-[#25f4ee] text-black rounded-lg text-sm font-bold hover:opacity-90 transition-opacity"
          >
            {t.dashboard.connectTikTok}
          </button>
        )}
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
        <StatsCard
          title={t.dashboard.followers}
          value={stats.followers.toLocaleString()}
          change={stats.followersChange}
          icon="👥"
        />
        <StatsCard
          title={t.dashboard.views}
          value={stats.views.toLocaleString()}
          change={stats.viewsChange}
          icon="👁️"
        />
        <StatsCard
          title={t.dashboard.likes}
          value={stats.likes.toLocaleString()}
          change={stats.likesChange}
          icon="❤️"
        />
        <StatsCard
          title={t.dashboard.comments}
          value={stats.comments.toLocaleString()}
          change={stats.commentsChange}
          icon="💬"
        />
        <StatsCard
          title={t.dashboard.shares}
          value={stats.shares.toLocaleString()}
          change={stats.sharesChange}
          icon="🔄"
        />
      </div>

      {/* Chart */}
      <div className="bg-[#1e1e1e] border border-[#2e2e2e] rounded-xl p-6">
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-lg font-semibold text-white">
            {t.dashboard.dailyViews}
          </h2>
          <div className="flex gap-4 text-xs text-[#888888]">
            <span className="flex items-center gap-1">
              <span className="w-2 h-2 rounded-full bg-[#fe2c55]" />
              Views
            </span>
            <span className="flex items-center gap-1">
              <span className="w-2 h-2 rounded-full bg-[#25f4ee]" />
              Likes
            </span>
          </div>
        </div>
        {dailyStats.length > 0 ? (
          <ResponsiveContainer width="100%" height={320}>
            <AreaChart data={dailyStats}>
              <defs>
                <linearGradient id="colorViews" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#fe2c55" stopOpacity={0.3} />
                  <stop offset="95%" stopColor="#fe2c55" stopOpacity={0} />
                </linearGradient>
                <linearGradient id="colorLikes" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#25f4ee" stopOpacity={0.3} />
                  <stop offset="95%" stopColor="#25f4ee" stopOpacity={0} />
                </linearGradient>
              </defs>
              <CartesianGrid strokeDasharray="3 3" stroke="#2e2e2e" />
              <XAxis dataKey="date" stroke="#555" fontSize={12} tickLine={false} />
              <YAxis stroke="#555" fontSize={12} tickLine={false} />
              <Tooltip
                contentStyle={{
                  backgroundColor: '#1e1e1e',
                  border: '1px solid #2e2e2e',
                  borderRadius: '8px',
                  color: '#fff',
                  fontSize: '13px',
                }}
              />
              <Area
                type="monotone"
                dataKey="views"
                stroke="#fe2c55"
                strokeWidth={2}
                fill="url(#colorViews)"
                dot={false}
              />
              <Area
                type="monotone"
                dataKey="likes"
                stroke="#25f4ee"
                strokeWidth={2}
                fill="url(#colorLikes)"
                dot={false}
              />
            </AreaChart>
          </ResponsiveContainer>
        ) : (
          <div className="flex items-center justify-center h-[320px] text-[#555] text-sm">
            No data yet — connect TikTok and publish videos
          </div>
        )}
      </div>
    </div>
  )
}

export default Dashboard
