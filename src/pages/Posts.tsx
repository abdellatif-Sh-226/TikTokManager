import { useEffect, useState } from 'react'
import { usePostsStore } from '../store'
import { useTranslation } from '../hooks/useTranslation'

function Posts() {
  const t = useTranslation()
  const { posts, isLoading, fetchPosts, addPost, deletePost } = usePostsStore()
  const [description, setDescription] = useState('')
  const [showForm, setShowForm] = useState(false)

  useEffect(() => {
    fetchPosts()
  }, [fetchPosts])

  const handleAdd = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!description.trim()) return
    await addPost({ description: description.trim() })
    setDescription('')
    setShowForm(false)
  }

  if (isLoading && posts.length === 0) {
    return (
      <div className="text-center text-[#888888] py-20">Loading...</div>
    )
  }

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-2xl font-bold text-white">{t.posts.title}</h1>
        <button
          onClick={() => setShowForm(!showForm)}
          className="px-4 py-2 bg-[#fe2c55] text-white rounded-lg text-sm font-medium hover:bg-[#e01e45] transition-colors"
        >
          {t.posts.add}
        </button>
      </div>

      {showForm && (
        <form
          onSubmit={handleAdd}
          className="bg-[#1e1e1e] border border-[#2e2e2e] rounded-xl p-4 mb-6 flex gap-3"
        >
          <input
            type="text"
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            className="flex-1 px-3 py-2 bg-[#121212] border border-[#2e2e2e] rounded-lg text-white text-sm focus:outline-none focus:border-[#fe2c55]"
            placeholder={t.posts.description}
          />
          <button
            type="submit"
            className="px-4 py-2 bg-[#25f4ee] text-black rounded-lg text-sm font-medium hover:bg-[#1fd8d2] transition-colors"
          >
            {t.posts.add}
          </button>
        </form>
      )}

      {posts.length === 0 ? (
        <p className="text-[#888888] text-center py-10">{t.posts.noPosts}</p>
      ) : (
        <div className="space-y-3">
          {posts.map((post) => (
            <div
              key={post.id}
              className="bg-[#1e1e1e] border border-[#2e2e2e] rounded-xl p-5 flex items-center justify-between"
            >
              <div className="flex-1 min-w-0">
                <p className="text-white text-sm font-medium truncate">
                  {post.description}
                </p>
                <div className="flex gap-4 mt-2 text-xs text-[#888888]">
                  <span>
                    {t.posts.views}: {post.views.toLocaleString()}
                  </span>
                  <span>
                    {t.posts.likes}: {post.likes.toLocaleString()}
                  </span>
                  <span>
                    {t.posts.comments}: {post.comments.toLocaleString()}
                  </span>
                  <span>{t.posts.status}: {post.status}</span>
                </div>
              </div>
              <button
                onClick={() => deletePost(post.id)}
                className="ml-4 px-3 py-1.5 text-xs text-[#ff1744] border border-[#ff1744] rounded-lg hover:bg-[#ff1744] hover:text-white transition-colors"
              >
                {t.posts.delete}
              </button>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}

export default Posts
