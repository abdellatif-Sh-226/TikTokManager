export interface User {
  id: string
  email: string
  name: string
  avatar?: string
  tiktokUsername?: string
  hasTikTok?: boolean
}

export interface Post {
  id: string
  description: string
  hashtags?: string
  videoUrl?: string
  thumbnailUrl?: string
  views: number
  likes: number
  comments: number
  shares: number
  createdAt: string
  status: 'published' | 'draft' | 'scheduled'
}

export interface Stats {
  followers: number
  followersChange: number
  views: number
  viewsChange: number
  likes: number
  likesChange: number
  comments: number
  commentsChange: number
}

export interface DailyStats {
  date: string
  views: number
  likes: number
  comments: number
  shares: number
}

export interface LoginResponse {
  user: User
  token: string
}
