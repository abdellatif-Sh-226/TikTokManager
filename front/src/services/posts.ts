import api from './api'
import type { Post } from '../types'

export const postsService = {
  getAll: async (): Promise<Post[]> => {
    console.log('[PostsService] getAll called')
    try {
      const response = await api.get('/posts')
      console.log('[PostsService] getAll raw response:', {
        status: response.status,
        dataType: typeof response.data,
        dataKeys: typeof response.data === 'object' ? Object.keys(response.data) : null,
      })

      let posts: Post[] = []
      const raw = response.data

      if (Array.isArray(raw)) {
        posts = raw
      } else if (raw && typeof raw === 'object' && Array.isArray(raw.data)) {
        posts = raw.data
      } else if (raw && typeof raw === 'object' && raw.data) {
        posts = Array.isArray(raw.data) ? raw.data : [raw.data]
      } else if (typeof raw === 'string') {
        console.warn('[PostsService] getAll received string response, attempting JSON parse')
        try {
          const parsed = JSON.parse(raw)
          posts = Array.isArray(parsed) ? parsed : (parsed.data ? (Array.isArray(parsed.data) ? parsed.data : [parsed.data]) : [])
        } catch {
          console.error('[PostsService] getAll failed to parse string response:', raw.substring(0, 200))
        }
      }

      console.log('[PostsService] getAll success', { count: posts.length })
      return posts
    } catch (error: any) {
      console.error('[PostsService] getAll failed', { error: error.response?.data || error.message })
      throw error
    }
  },

  create: async (formData: FormData): Promise<Post> => {
    console.log('[PostsService] create called')
    const entries: Record<string, any> = {}
    formData.forEach((value, key) => {
      if (value instanceof File) {
        entries[key] = { name: value.name, size: value.size, type: value.type }
      } else {
        entries[key] = value
      }
    })
    console.log('[PostsService] create FormData:', entries)

    try {
      const response = await api.post('/posts', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })

      console.log('[PostsService] create raw response:', {
        status: response.status,
        dataType: typeof response.data,
        dataKeys: typeof response.data === 'object' ? Object.keys(response.data).slice(0, 10) : null,
        rawPreview: typeof response.data === 'string' ? response.data.substring(0, 200) : null,
      })

      const raw = response.data
      let post: Post

      if (raw && typeof raw === 'object' && raw.data) {
        post = raw.data
      } else if (raw && typeof raw === 'object' && raw.id) {
        post = raw
      } else if (typeof raw === 'string') {
        console.warn('[PostsService] create received string response, attempting JSON parse')
        try {
          const parsed = JSON.parse(raw)
          post = parsed.data || parsed
        } catch {
          throw new Error('Server returned invalid response: ' + raw.substring(0, 100))
        }
      } else {
        throw new Error('Unexpected response format from server')
      }

      const tiktokStatus = (post as any)?.tiktokStatus || null
      console.log('[PostsService] create success', { postId: post?.id, tiktokStatus })
      return post
    } catch (error: any) {
      console.error('[PostsService] create failed', {
        status: error.response?.status,
        data: error.response?.data,
        message: error.message,
      })
      throw error
    }
  },

  remove: async (id: string): Promise<void> => {
    console.log('[PostsService] remove called', { id })
    try {
      await api.delete(`/posts/${id}`)
      console.log('[PostsService] remove success', { id })
    } catch (error: any) {
      console.error('[PostsService] remove failed', { id, error: error.response?.data || error.message })
      throw error
    }
  },
}
