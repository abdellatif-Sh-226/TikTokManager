import { useSearchParams } from 'react-router-dom'
import { useAuthStore } from '../store'
import { useTranslation } from '../hooks/useTranslation'

function Login() {
  const t = useTranslation()
  const [searchParams] = useSearchParams()
  const { loginWithTikTok } = useAuthStore()

  const oauthError = searchParams.get('error')

  return (
    <div className="min-h-screen bg-[#121212] flex items-center justify-center">
      <div className="bg-[#1e1e1e] border border-[#2e2e2e] rounded-xl p-8 w-full max-w-sm">
        <h1 className="text-2xl font-bold text-white mb-2 text-center">
          {t.app.title}
        </h1>
        <p className="text-sm text-[#888888] mb-8 text-center">
          Connect your TikTok account to manage your content
        </p>

        <button
          onClick={() => loginWithTikTok()}
          className="w-full py-3 bg-[#25f4ee] text-black rounded-lg text-sm font-bold hover:opacity-90 transition-opacity flex items-center justify-center gap-2"
        >
          <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.04-.1z"/>
          </svg>
          Continue with TikTok
        </button>

        {oauthError && (
          <p className="text-sm text-[#ff1744] mt-4 text-center">
            {decodeURIComponent(oauthError)}
          </p>
        )}
      </div>
    </div>
  )
}

export default Login
