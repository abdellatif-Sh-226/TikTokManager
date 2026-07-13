import { useState, useRef } from 'react'
import { useNavigate } from 'react-router-dom'
import { usePostsStore } from '../store'
import { useTranslation } from '../hooks/useTranslation'

function CreatePost() {
  const t = useTranslation()
  const navigate = useNavigate()
  const { addPost, isLoading } = usePostsStore()

  const [description, setDescription] = useState('')
  const [hashtags, setHashtags] = useState('')
  const [status, setStatus] = useState<'draft' | 'published' | 'scheduled'>('draft')
  const [video, setVideo] = useState<File | null>(null)
  const [thumbnail, setThumbnail] = useState<File | null>(null)
  const [videoPreview, setVideoPreview] = useState<string | null>(null)
  const [thumbnailPreview, setThumbnailPreview] = useState<string | null>(null)
  const [error, setError] = useState('')

  const videoRef = useRef<HTMLInputElement>(null)
  const thumbnailRef = useRef<HTMLInputElement>(null)

  const handleVideo = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (!file) return
    setVideo(file)
    setVideoPreview(URL.createObjectURL(file))
  }

  const handleThumbnail = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (!file) return
    setThumbnail(file)
    setThumbnailPreview(URL.createObjectURL(file))
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')

    if (!description.trim()) {
      setError('Description is required')
      return
    }

    const formData = new FormData()
    formData.append('description', description.trim())
    formData.append('status', status)

    if (hashtags.trim()) {
      formData.append('hashtags', hashtags.trim())
    }

    if (video) {
      formData.append('video', video)
    }

    if (thumbnail) {
      formData.append('thumbnail', thumbnail)
    }

    try {
      await addPost(formData)
      navigate('/posts')
    } catch {
      setError('Failed to create post')
    }
  }

  return (
    <div className="max-w-3xl mx-auto">
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-2xl font-bold text-white">Create Post</h1>
        <button
          onClick={() => navigate('/posts')}
          className="px-4 py-2 text-sm text-[#888888] hover:text-white transition-colors"
        >
          Cancel
        </button>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        <div className="grid grid-cols-2 gap-6">
          <div
            onClick={() => videoRef.current?.click()}
            className="bg-[#1e1e1e] border-2 border-dashed border-[#2e2e2e] rounded-xl p-8 flex flex-col items-center justify-center cursor-pointer hover:border-[#fe2c55] transition-colors min-h-[240px]"
          >
            {videoPreview ? (
              <video src={videoPreview} className="max-h-[200px] rounded-lg" controls />
            ) : (
              <>
                <svg className="w-12 h-12 text-[#555] mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <p className="text-sm text-[#888888]">Upload Video</p>
                <p className="text-xs text-[#555] mt-1">MP4, MOV, AVI (max 50MB)</p>
              </>
            )}
            <input
              ref={videoRef}
              type="file"
              accept="video/mp4,video/quicktime,video/x-msvideo"
              onChange={handleVideo}
              className="hidden"
            />
          </div>

          <div
            onClick={() => thumbnailRef.current?.click()}
            className="bg-[#1e1e1e] border-2 border-dashed border-[#2e2e2e] rounded-xl p-8 flex flex-col items-center justify-center cursor-pointer hover:border-[#fe2c55] transition-colors min-h-[240px]"
          >
            {thumbnailPreview ? (
              <img src={thumbnailPreview} alt="" className="max-h-[200px] rounded-lg object-cover" />
            ) : (
              <>
                <svg className="w-12 h-12 text-[#555] mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p className="text-sm text-[#888888]">Upload Thumbnail</p>
                <p className="text-xs text-[#555] mt-1">JPG, PNG (max 5MB)</p>
              </>
            )}
            <input
              ref={thumbnailRef}
              type="file"
              accept="image/jpeg,image/png"
              onChange={handleThumbnail}
              className="hidden"
            />
          </div>
        </div>

        <div>
          <label className="block text-sm text-[#888888] mb-1">Description</label>
          <textarea
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            rows={4}
            className="w-full px-3 py-2 bg-[#121212] border border-[#2e2e2e] rounded-lg text-white text-sm focus:outline-none focus:border-[#fe2c55] resize-none"
            placeholder="Write your video description..."
          />
        </div>

        <div>
          <label className="block text-sm text-[#888888] mb-1">Hashtags</label>
          <input
            type="text"
            value={hashtags}
            onChange={(e) => setHashtags(e.target.value)}
            className="w-full px-3 py-2 bg-[#121212] border border-[#2e2e2e] rounded-lg text-white text-sm focus:outline-none focus:border-[#fe2c55]"
            placeholder="#dance #viral #fyp"
          />
          <p className="text-xs text-[#555] mt-1">Separate hashtags with spaces</p>
        </div>

        <div>
          <label className="block text-sm text-[#888888] mb-1">Status</label>
          <select
            value={status}
            onChange={(e) => setStatus(e.target.value as typeof status)}
            className="w-full px-3 py-2 bg-[#121212] border border-[#2e2e2e] rounded-lg text-white text-sm focus:outline-none focus:border-[#fe2c55]"
          >
            <option value="draft">Draft</option>
            <option value="published">Published</option>
            <option value="scheduled">Scheduled</option>
          </select>
        </div>

        {error && (
          <p className="text-sm text-[#ff1744]">{error}</p>
        )}

        <div className="flex gap-3 justify-end">
          <button
            type="button"
            onClick={() => navigate('/posts')}
            className="px-6 py-2.5 text-sm text-[#888888] border border-[#2e2e2e] rounded-lg hover:text-white transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            disabled={isLoading}
            className="px-6 py-2.5 bg-[#fe2c55] text-white rounded-lg text-sm font-medium hover:bg-[#e01e45] transition-colors disabled:opacity-50"
          >
            {isLoading ? 'Creating...' : 'Create Post'}
          </button>
        </div>
      </form>
    </div>
  )
}

export default CreatePost
