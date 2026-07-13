interface StatsCardProps {
  title: string
  value: string
  change?: number
}

function StatsCard({ title, value, change }: StatsCardProps) {
  return (
    <div className="bg-[#1e1e1e] border border-[#2e2e2e] rounded-xl p-5">
      <p className="text-sm text-[#888888] mb-1">{title}</p>
      <p className="text-2xl font-bold text-white">{value}</p>
      {change != null && (
        <span
          className={`inline-block mt-1 text-xs font-medium ${
            change >= 0 ? 'text-[#00c853]' : 'text-[#ff1744]'
          }`}
        >
          {change >= 0 ? '+' : ''}{change}%
        </span>
      )}
    </div>
  )
}

export default StatsCard
