import { useState, useRef } from 'react'
import { useNavigate } from 'react-router-dom'
import { usePostsStore } from '../store'
import { useTranslation } from '../hooks/useTranslation'

const privacyOptions = [
  { value: 'PUBLIC_TO_EVERYONE', label: 'Public', icon: '🌍' },
  { value: 'SELF_ONLY', label: 'Private (only me)', icon: '🔒' },
]

function CreatePost() {
  const t = useTranslation()
  const navigate = useNavigate()
  const { addPost, isLoading } = usePostsStore()

  const [description, setDescription] = useState('')
  const [hashtags, setHashtags] = useState('')
  const [video, setVideo] = useState<File | null>(null)
  const [thumbnail, setThumbnail] = useState<File | null>(null)
  const [videoPreview, setVideoPreview] = useState<string | null>(null)
  const [thumbnailPreview, setThumbnailPreview] = useState<string | null>(null)
  const [privacyLevel, setPrivacyLevel] = useState('SELF_ONLY')
  const [disableComment, setDisableComment] = useState(false)
  const [publishToTikTok, setPublishToTikTok] = useState(true)
  const [error, setError] = useState('')
  const [submitStatus, setSubmitStatus] = useState<string | null>(null)

  const videoRef = useRef<HTMLInputElement>(null)
  const thumbnailRef = useRef<HTMLInputElement>(null)

  const handleVideo = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (!file) return
    console.log('[CreatePost] Video selected', {
      name: file.name,
      size: file.size,
      type: file.type,
      lastModified: file.lastModified,
    })
    setVideo(file)
    setVideoPreview(URL.createObjectURL(file))
  }

  const handleThumbnail = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (!file) return
    console.log('[CreatePost] Thumbnail selected', {
      name: file.name,
      size: file.size,
      type: file.type,
    })
    setThumbnail(file)
    setThumbnailPreview(URL.createObjectURL(file))
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    console.log('[CreatePost] handleSubmit called')
    setError('')
    setSubmitStatus(null)

    // Validation
    if (!description.trim()) {
      console.log('[CreatePost] Validation failed: no description')
      setError('Description is required')
      return
    }
    if (!video) {
      console.log('[CreatePost] Validation failed: no video')
      setError('Please select a video to upload')
      return
    }

    console.log('[CreatePost] Building FormData', {
      description: description.trim(),
      hashtags: hashtags.trim(),
      videoName: video.name,
      videoSize: video.size,
      videoType: video.type,
      thumbnailName: thumbnail?.name,
      privacyLevel,
      disableComment,
      publishToTikTok,
    })

    setSubmitStatus('Uploading to Cloudinary...')

    const formData = new FormData()
    formData.append('description', description.trim())
    if (hashtags.trim()) formData.append('hashtags', hashtags.trim())
    formData.append('video', video)
    if (thumbnail) formData.append('thumbnail', thumbnail)
    formData.append('privacy_level', privacyLevel)
    formData.append('disable_comment', disableComment ? '1' : '0')
    formData.append('publish_to_tiktok', publishToTikTok ? '1' : '0')

    // Log FormData contents
    formData.forEach((value, key) => {
      if (value instanceof File) {
        console.log(`[CreatePost] FormData[${key}]: File(${value.name}, ${value.size} bytes, ${value.type})`)
      } else {
        console.log(`[CreatePost] FormData[${key}]: ${value}`)
      }
    })

    try {
      setSubmitStatus('Publishing to TikTok...')
      console.log('[CreatePost] Calling addPost...')
      const result = await addPost(formData)
      console.log('[CreatePost] addPost returned', { result, tiktokStatus: result?.tiktokStatus })

      if (result?.tiktokStatus) {
        if (result.tiktokStatus.status === 'PUBLISH_COMPLETE') {
          setSubmitStatus('✅ Published to TikTok!')
          console.log('[CreatePost] TikTok publish complete')
        } else if (result.tiktokStatus.error) {
          setSubmitStatus(`⚠️ Saved locally, TikTok: ${result.tiktokStatus.message}`)
          console.warn('[CreatePost] TikTok publish error', { status: result.tiktokStatus })
        } else {
          setSubmitStatus(`✅ Uploaded. TikTok status: ${result.tiktokStatus.status}`)
          console.log('[CreatePost] TikTok status:', result.tiktokStatus.status)
        }
      } else {
        setSubmitStatus('✅ Saved locally')
        console.log('[CreatePost] No TikTok status, saved locally')
      }
      setTimeout(() => {
        console.log('[CreatePost] Redirecting to /posts')
        navigate('/posts')
      }, 1500)
    } catch (err: any) {
      console.error('[CreatePost] handleSubmit failed', {
        error: err,
        message: err.message,
        response: err.response?.data,
        status: err.response?.status,
      })
      setError('Failed to create post')
      setSubmitStatus(null)
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
            <input ref={videoRef} type="file" accept="video/mp4,video/quicktime,video/x-msvideo" onChange={handleVideo} className="hidden" />
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
            <input ref={thumbnailRef} type="file" accept="image/jpeg,image/png" onChange={handleThumbnail} className="hidden" />
          </div>
        </div>

        <div>
          <label className="block text-sm text-[#888888] mb-1">Description</label>
          <textarea
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            rows={3}
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
        </div>

        {/* Privacy Level */}
        <div>
          <label className="block text-sm text-[#888888] mb-2">Privacy</label>
          <div className="flex gap-3">
            {privacyOptions.map((opt) => (
              <button
                key={opt.value}
                type="button"
                onClick={() => setPrivacyLevel(opt.value)}
                className={`flex-1 px-4 py-3 rounded-lg text-sm font-medium transition-colors border ${
                  privacyLevel === opt.value
                    ? 'bg-[#25f4ee]/10 border-[#25f4ee] text-[#25f4ee]'
                    : 'bg-[#121212] border-[#2e2e2e] text-[#888888] hover:text-white'
                }`}
              >
                <span className="mr-2">{opt.icon}</span>
                {opt.label}
              </button>
            ))}
          </div>
        </div>

        {/* Options */}
        <div className="flex gap-6">
          <label className="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              checked={publishToTikTok}
              onChange={(e) => {
                console.log('[CreatePost] publishToTikTok toggled:', e.target.checked)
                setPublishToTikTok(e.target.checked)
              }}
              className="w-4 h-4 rounded accent-[#fe2c55]"
            />
            <span className="text-sm text-white">Post to TikTok</span>
          </label>
          <label className="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              checked={disableComment}
              onChange={(e) => {
                console.log('[CreatePost] disableComment toggled:', e.target.checked)
                setDisableComment(e.target.checked)
              }}
              className="w-4 h-4 rounded accent-[#fe2c55]"
            />
            <span className="text-sm text-[#888888]">Disable comments</span>
          </label>
        </div>

        {error && <p className="text-sm text-[#ff1744] bg-[#ff1744]/10 border border-[#ff1744]/30 rounded-lg px-4 py-2">{error}</p>}

        {submitStatus && (
          <div className="text-sm text-[#25f4ee] bg-[#25f4ee]/10 border border-[#25f4ee]/30 rounded-lg px-4 py-2 flex items-center gap-2">
            <span className="animate-pulse">●</span>
            {submitStatus}
          </div>
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
            disabled={isLoading || !!submitStatus}
            className="px-6 py-2.5 bg-[#fe2c55] text-white rounded-lg text-sm font-medium hover:bg-[#e01e45] transition-colors disabled:opacity-50 flex items-center gap-2"
          >
            {isLoading ? (
              <>
                <span className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                Publishing...
              </>
            ) : (
              <>
                <svg className="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.04-.1z"/>
                </svg>
                Post to TikTok
              </>
            )}
          </button>
        </div>
      </form>
    </div>
  )
}

export default CreatePost
