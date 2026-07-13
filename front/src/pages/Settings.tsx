import { useState } from 'react'
import { useAuthStore, useSettingsStore } from '../store'
import { useTranslation } from '../hooks/useTranslation'

function Settings() {
  const t = useTranslation()
  const { user } = useAuthStore()
  const { language, setLanguage } = useSettingsStore()
  const [name, setName] = useState(user?.name || '')
  const [email, setEmail] = useState(user?.email || '')

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault()
  }

  return (
    <div className="max-w-2xl">
      <h1 className="text-2xl font-bold text-white mb-6">{t.settings.title}</h1>

      <div className="bg-[#1e1e1e] border border-[#2e2e2e] rounded-xl p-6 mb-6">
        <h2 className="text-lg font-semibold text-white mb-4">
          {t.settings.profile}
        </h2>
        <form onSubmit={handleSave} className="space-y-4">
          <div>
            <label className="block text-sm text-[#888888] mb-1">
              {t.settings.name}
            </label>
            <input
              type="text"
              value={name}
              onChange={(e) => setName(e.target.value)}
              className="w-full px-3 py-2 bg-[#121212] border border-[#2e2e2e] rounded-lg text-white text-sm focus:outline-none focus:border-[#fe2c55]"
            />
          </div>
          <div>
            <label className="block text-sm text-[#888888] mb-1">
              {t.settings.email}
            </label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="w-full px-3 py-2 bg-[#121212] border border-[#2e2e2e] rounded-lg text-white text-sm focus:outline-none focus:border-[#fe2c55]"
            />
          </div>
          <button
            type="submit"
            className="px-4 py-2 bg-[#fe2c55] text-white rounded-lg text-sm font-medium hover:bg-[#e01e45] transition-colors"
          >
            {t.settings.save}
          </button>
        </form>
      </div>

      <div className="bg-[#1e1e1e] border border-[#2e2e2e] rounded-xl p-6">
        <h2 className="text-lg font-semibold text-white mb-4">
          {t.settings.language}
        </h2>
        <div className="flex gap-3">
          <button
            onClick={() => setLanguage('en')}
            className={`px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
              language === 'en'
                ? 'bg-[#fe2c55] text-white'
                : 'bg-[#121212] text-[#888888] border border-[#2e2e2e] hover:text-white'
            }`}
          >
            English
          </button>
          <button
            onClick={() => setLanguage('fr')}
            className={`px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
              language === 'fr'
                ? 'bg-[#fe2c55] text-white'
                : 'bg-[#121212] text-[#888888] border border-[#2e2e2e] hover:text-white'
            }`}
          >
            Français
          </button>
        </div>
      </div>
    </div>
  )
}

export default Settings
