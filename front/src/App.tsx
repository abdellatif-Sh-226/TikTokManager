import { useEffect } from 'react'
import { Routes, Route, Navigate } from 'react-router-dom'
import { useAuthStore } from './store'
import Sidebar from './components/Sidebar'
import Dashboard from './pages/Dashboard'
import Posts from './pages/Posts'
import CreatePost from './pages/CreatePost'
import Settings from './pages/Settings'
import Login from './pages/Login'
import './App.css'

function App() {
  const { isAuthenticated, initialized, init } = useAuthStore()

  useEffect(() => {
    console.log('[App] Checking URL for token...')
    const params = new URLSearchParams(window.location.search)
    const token = params.get('token')
    const error = params.get('error')

    if (error) {
      console.error('[App] Login error from redirect:', error)
      // Clean the URL and let the login page show
      window.history.replaceState({}, '', '/login?error=' + encodeURIComponent(error))
      init()
      return
    }

    if (token) {
      console.log('[App] Token found in URL, saving to localStorage:', token.substring(0, 15) + '...')
      localStorage.setItem('tiktok_token', token)
      window.location.href = '/'
      return
    }

    console.log('[App] No token in URL, calling init()')
    init()
  }, [init])

  if (!initialized) {
    console.log('[App] Not initialized yet, showing loading...')
    return (
      <div className="flex h-screen bg-[#121212] items-center justify-center">
        <div className="text-center">
          <div className="animate-spin w-8 h-8 border-2 border-[#fe2c55] border-t-transparent rounded-full mx-auto mb-4" />
          <div className="text-[#888888]">Loading...</div>
        </div>
      </div>
    )
  }

  if (!isAuthenticated) {
    console.log('[App] Not authenticated, showing login routes')
    return (
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route path="*" element={<Navigate to="/login" replace />} />
      </Routes>
    )
  }

  console.log('[App] Authenticated, showing main app')
  return (
    <div className="flex h-screen bg-[#121212]">
      <Sidebar />
      <main className="flex-1 overflow-y-auto p-8">
        <Routes>
          <Route path="/" element={<Dashboard />} />
          <Route path="/posts" element={<Posts />} />
          <Route path="/posts/new" element={<CreatePost />} />
          <Route path="/settings" element={<Settings />} />
          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </main>
    </div>
  )
}

export default App
