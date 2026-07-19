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
  tiktokPublishId?: string
  tiktokStatus?: TikTokPublishStatus | null
}

export interface TikTokPublishStatus {
  publish_id?: string
  status?: string
  error?: string
  message?: string
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
  shares: number
  sharesChange: number
  avatar: string | null
  displayName: string | null
  username: string | null
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
