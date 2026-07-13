import { NavLink } from 'react-router-dom'
import { useAuthStore, useSettingsStore } from '../store'
import { useTranslation } from '../hooks/useTranslation'

const navItems = [
  { to: '/', labelKey: 'dashboard' },
  { to: '/posts', labelKey: 'posts' },
  { to: '/settings', labelKey: 'settings' },
] as const

function Sidebar() {
  const { isAuthenticated, logout } = useAuthStore()
  const t = useTranslation()
  const lang = useSettingsStore((s) => s.language)

  return (
    <aside className="w-64 h-screen bg-[#121212] border-r border-[#2e2e2e] flex flex-col">
      <div className="p-6">
        <h1 className="text-xl font-bold text-[#fe2c55]">{t.app.title}</h1>
      </div>

      <nav className="flex-1 px-3 space-y-1">
        {navItems.map((item) => (
          <NavLink
            key={item.to}
            to={item.to}
            end={item.to === '/'}
            className={({ isActive }) =>
              `flex items-center px-4 py-2.5 rounded-lg text-sm font-medium transition-colors ${
                isActive
                  ? 'bg-[#fe2c55] text-white'
                  : 'text-[#888888] hover:text-white hover:bg-[#1e1e1e]'
              }`
            }
          >
            {t.sidebar[item.labelKey as keyof typeof t.sidebar]}
          </NavLink>
        ))}
      </nav>

      <div className="p-3 border-t border-[#2e2e2e]">
        {isAuthenticated ? (
          <button
            onClick={logout}
            className="w-full px-4 py-2.5 text-sm text-[#888888] hover:text-white text-left rounded-lg hover:bg-[#1e1e1e] transition-colors"
          >
            {t.sidebar.logout}
          </button>
        ) : (
          <NavLink
            to="/login"
            className={({ isActive }) =>
              `flex items-center px-4 py-2.5 rounded-lg text-sm font-medium transition-colors ${
                isActive
                  ? 'bg-[#fe2c55] text-white'
                  : 'text-[#888888] hover:text-white hover:bg-[#1e1e1e]'
              }`
            }
          >
            {t.sidebar.login}
          </NavLink>
        )}
      </div>

      <div className="px-3 pb-3">
        <span className="text-xs text-[#555]">
          {lang === 'fr' ? 'Langue' : 'Language'}: {lang.toUpperCase()}
        </span>
      </div>
    </aside>
  )
}

export default Sidebar
