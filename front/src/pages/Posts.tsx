import { useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { usePostsStore } from '../store'
import { useTranslation } from '../hooks/useTranslation'

function Posts() {
  const t = useTranslation()
  const navigate = useNavigate()
  const { posts, isLoading, error, fetchPosts, deletePost } = usePostsStore()

  useEffect(() => {
    fetchPosts()
  }, [fetchPosts])

  if (isLoading && posts.length === 0) {
    return (
      <div className="text-center text-[#888888] py-20">Loading...</div>
    )
  }

  if (error) {
    return (
      <div>
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-2xl font-bold text-white">{t.posts.title}</h1>
          <button
            onClick={() => navigate('/posts/new')}
            className="px-4 py-2 bg-[#fe2c55] text-white rounded-lg text-sm font-medium hover:bg-[#e01e45] transition-colors"
          >
            {t.posts.add}
          </button>
        </div>
        <div className="bg-[#1e1e1e] border border-[#2e2e2e] rounded-xl p-6 text-center">
          <p className="text-[#ff1744] text-sm mb-3">{error}</p>
          <button
            onClick={fetchPosts}
            className="px-4 py-2 bg-[#fe2c55] text-white rounded-lg text-sm font-medium hover:bg-[#e01e45] transition-colors"
          >
            Retry
          </button>
        </div>
      </div>
    )
  }

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-2xl font-bold text-white">{t.posts.title}</h1>
        <button
          onClick={() => navigate('/posts/new')}
          className="px-4 py-2 bg-[#fe2c55] text-white rounded-lg text-sm font-medium hover:bg-[#e01e45] transition-colors"
        >
          {t.posts.add}
        </button>
      </div>

      {posts.length === 0 ? (
        <div className="text-center py-20">
          <p className="text-[#888888] mb-4">{t.posts.noPosts}</p>
          <button
            onClick={() => navigate('/posts/new')}
            className="px-4 py-2 bg-[#fe2c55] text-white rounded-lg text-sm font-medium hover:bg-[#e01e45] transition-colors"
          >
            {t.posts.add}
          </button>
        </div>
      ) : (
        <div className="space-y-3">
          {posts.map((post) => (
            <div
              key={post.id}
              className="bg-[#1e1e1e] border border-[#2e2e2e] rounded-xl p-5 flex items-center justify-between"
            >
              <div className="flex-1 min-w-0">
                <div className="flex items-start gap-4">
                  {post.thumbnailUrl && (
                    <img
                      src={post.thumbnailUrl}
                      alt=""
                      className="w-16 h-16 rounded-lg object-cover flex-shrink-0"
                    />
                  )}
                  <div className="min-w-0">
                    <p className="text-white text-sm font-medium truncate">
                      {post.description}
                    </p>
                    {post.hashtags && (
                      <p className="text-[#25f4ee] text-xs mt-1 truncate">
                        {post.hashtags}
                      </p>
                    )}
                    <div className="flex gap-4 mt-2 text-xs text-[#888888]">
                      <span>{t.posts.views}: {post.views.toLocaleString()}</span>
                      <span>{t.posts.likes}: {post.likes.toLocaleString()}</span>
                      <span>{t.posts.comments}: {post.comments.toLocaleString()}</span>
                      <span>{t.posts.status}: {post.status}</span>
                    </div>
                  </div>
                </div>
              </div>
              <button
                onClick={() => deletePost(post.id)}
                className="ml-4 px-3 py-1.5 text-xs text-[#ff1744] border border-[#ff1744] rounded-lg hover:bg-[#ff1744] hover:text-white transition-colors flex-shrink-0"
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
