import api from './api'
import type { Post } from '../types'

export const postsService = {
  getAll: async (): Promise<Post[]> => {
    const { data } = await api.get<{ data: Post[] }>('/posts')
    return data.data
  },

  create: async (postData: Partial<Post>): Promise<Post> => {
    const { data } = await api.post<{ data: Post }>('/posts', postData)
    return data.data
  },

  remove: async (id: string): Promise<void> => {
    await api.delete(`/posts/${id}`)
  },
}
