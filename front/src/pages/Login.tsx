import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { z } from 'zod'
import { useAuthStore } from '../store'
import { useTranslation } from '../hooks/useTranslation'

const loginSchema = z.object({
  email: z.string().email('Invalid email'),
  password: z.string().min(1, 'Password is required'),
})

function Login() {
  const t = useTranslation()
  const navigate = useNavigate()
  const { login, loginWithTikTok, isLoading } = useAuthStore()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')

    const result = loginSchema.safeParse({ email, password })
    if (!result.success) {
      setError(result.error.issues[0].message)
      return
    }

    try {
      await login(email, password)
      navigate('/')
    } catch {
      setError(t.login.error)
    }
  }

  return (
    <div className="min-h-screen bg-[#121212] flex items-center justify-center">
      <div className="bg-[#1e1e1e] border border-[#2e2e2e] rounded-xl p-8 w-full max-w-sm">
        <h1 className="text-2xl font-bold text-white mb-6 text-center">
          {t.login.title}
        </h1>

        <button
          onClick={loginWithTikTok}
          className="w-full py-2.5 bg-[#25f4ee] text-black rounded-lg text-sm font-bold mb-6 hover:opacity-90 transition-opacity flex items-center justify-center gap-2"
        >
          <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.04-.1z"/>
          </svg>
          Continue with TikTok
        </button>

        <div className="flex items-center gap-3 mb-6">
          <div className="flex-1 h-px bg-[#2e2e2e]" />
          <span className="text-xs text-[#555]">OR</span>
          <div className="flex-1 h-px bg-[#2e2e2e]" />
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm text-[#888888] mb-1">
              {t.login.email}
            </label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="w-full px-3 py-2 bg-[#121212] border border-[#2e2e2e] rounded-lg text-white text-sm focus:outline-none focus:border-[#fe2c55]"
              placeholder={t.login.email}
            />
          </div>

          <div>
            <label className="block text-sm text-[#888888] mb-1">
              {t.login.password}
            </label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="w-full px-3 py-2 bg-[#121212] border border-[#2e2e2e] rounded-lg text-white text-sm focus:outline-none focus:border-[#fe2c55]"
              placeholder={t.login.password}
            />
          </div>

          {error && (
            <p className="text-sm text-[#ff1744]">{error}</p>
          )}

          <button
            type="submit"
            disabled={isLoading}
            className="w-full py-2.5 bg-[#fe2c55] text-white rounded-lg text-sm font-medium hover:bg-[#e01e45] transition-colors disabled:opacity-50"
          >
            {isLoading ? '...' : t.login.submit}
          </button>
        </form>
      </div>
    </div>
  )
}

export default Login
