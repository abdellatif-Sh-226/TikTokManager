import api from './api'
import type { Post } from '../types'

export const postsService = {
  getAll: async (): Promise<Post[]> => {
    const { data } = await api.get<{ data: Post[] }>('/posts')
    return data.data
  },

  create: async (formData: FormData): Promise<Post> => {
    const token = localStorage.getItem('tiktok_token')
    const { data } = await api.post<{ data: Post }>('/posts', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        Authorization: `Bearer ${token}`,
      },
    })
    return data.data
  },

  remove: async (id: string): Promise<void> => {
    await api.delete(`/posts/${id}`)
  },
}
