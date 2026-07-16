interface StatsCardProps {
  title: string
  value: string
  change?: number
  icon?: string
  color?: string
}

const icons: Record<string, string> = {
  followers: '👥',
  views: '👁️',
  likes: '❤️',
  comments: '💬',
  shares: '🔄',
}

function StatsCard({ title, value, change, icon, color = '#fe2c55' }: StatsCardProps) {
  return (
    <div className="bg-[#1e1e1e] border border-[#2e2e2e] rounded-xl p-5 hover:border-[#fe2c55] transition-colors">
      <div className="flex items-center justify-between mb-3">
        <span className="text-lg">{icon || icons[title.toLowerCase()] || '📊'}</span>
        {change != null && (
          <span
            className={`text-xs font-medium px-2 py-0.5 rounded-full ${
              change >= 0
                ? 'bg-[#00c853]/10 text-[#00c853]'
                : 'bg-[#ff1744]/10 text-[#ff1744]'
            }`}
          >
            {change >= 0 ? '+' : ''}{change}%
          </span>
        )}
      </div>
      <p className="text-xs text-[#888888] mb-1">{title}</p>
      <p className="text-2xl font-bold text-white">{value}</p>
    </div>
  )
}

export default StatsCard
