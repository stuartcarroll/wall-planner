'use client'

import { useState, useEffect } from 'react'
import { useAuth } from '@/contexts/AuthContext'

interface ProjectImage {
  id: number
  filename: string
  original_name: string
  type: 'photo' | 'sketch' | 'inspiration'
  description?: string
  mime_type: string
  file_size: number
  width?: number
  height?: number
  url: string
  created_at: string
}

interface ProjectImagesProps {
  projectId: number
  canEdit: boolean
}

export default function ProjectImages({ projectId, canEdit }: ProjectImagesProps) {
  const [images, setImages] = useState<ProjectImage[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [uploadingType, setUploadingType] = useState<'photo' | 'sketch' | 'inspiration' | null>(null)
  const [editingImage, setEditingImage] = useState<number | null>(null)
  const [editForm, setEditForm] = useState<{[key: number]: {type: string, description: string}}>({})
  const { token } = useAuth()

  useEffect(() => {
    fetchImages()
  }, [projectId])

  const fetchImages = async () => {
    if (!token) return

    try {
      setLoading(true)
      setError(null)
      const response = await fetch(`/api/projects/${projectId}/images`, {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}))
        throw new Error(`Failed to fetch images (${response.status}): ${errorData.message || response.statusText}`)
      }
      
      const data = await response.json()
      setImages(data.data || [])
    } catch (err) {
      console.error('Error fetching images:', err)
      setError(err instanceof Error ? err.message : 'Failed to fetch images')
    } finally {
      setLoading(false)
    }
  }

  const handleFileUpload = async (file: File, type: 'photo' | 'sketch' | 'inspiration') => {
    if (!token) return

    // Validate file type and size
    if (!file.type.startsWith('image/')) {
      setError('Please select a valid image file')
      return
    }

    if (file.size > 10 * 1024 * 1024) { // 10MB
      setError('File size must be less than 10MB')
      return
    }

    const formData = new FormData()
    formData.append('image', file)
    formData.append('type', type)

    try {
      setUploadingType(type)
      setError(null)
      const response = await fetch(`/api/projects/${projectId}/images`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
        body: formData,
      })
      
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}))
        throw new Error(`Upload failed (${response.status}): ${errorData.message || response.statusText}`)
      }
      
      const result = await response.json()
      console.log('Image uploaded successfully:', result)
      fetchImages() // Refresh the images list
    } catch (err) {
      console.error('Error uploading image:', err)
      setError(err instanceof Error ? err.message : 'Failed to upload image')
    } finally {
      setUploadingType(null)
    }
  }

  const startEditing = (image: ProjectImage) => {
    setEditingImage(image.id)
    setEditForm({
      ...editForm,
      [image.id]: {
        type: image.type,
        description: image.description || ''
      }
    })
  }

  const cancelEditing = () => {
    setEditingImage(null)
    setEditForm({})
  }

  const saveImage = async (imageId: number) => {
    if (!token || !editForm[imageId]) return

    try {
      const response = await fetch(`/api/projects/${projectId}/images/${imageId}`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(editForm[imageId]),
      })
      
      if (!response.ok) throw new Error('Failed to update image')
      
      fetchImages()
      setEditingImage(null)
      setEditForm({})
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to update image')
    }
  }

  const deleteImage = async (imageId: number) => {
    if (!token) return
    
    if (!confirm('Are you sure you want to delete this image?')) return

    try {
      const response = await fetch(`/api/projects/${projectId}/images/${imageId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      
      if (!response.ok) throw new Error('Failed to delete image')
      
      fetchImages()
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to delete image')
    }
  }

  const getImagesByType = (type: 'photo' | 'sketch' | 'inspiration') => {
    return images.filter(img => img.type === type)
  }

  const renderImageSection = (type: 'photo' | 'sketch' | 'inspiration', title: string) => {
    const typeImages = getImagesByType(type)
    
    return (
      <div className="mb-8">
        <div className="flex justify-between items-center mb-4">
          <h3 className="text-lg font-semibold text-gray-900 capitalize">{title}</h3>
          {canEdit && (
            <div className="relative">
              <input
                type="file"
                accept="image/*"
                onChange={(e) => e.target.files?.[0] && handleFileUpload(e.target.files[0], type)}
                className="hidden"
                id={`upload-${type}`}
                disabled={uploadingType === type}
              />
              <label
                htmlFor={`upload-${type}`}
                className={`cursor-pointer bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors ${
                  uploadingType === type ? 'opacity-50 cursor-not-allowed' : ''
                }`}
              >
                {uploadingType === type ? 'Uploading...' : `+ Upload ${title}`}
              </label>
            </div>
          )}
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {typeImages.map((image) => {
            const isEditing = editingImage === image.id
            const currentForm = editForm[image.id] || {}
            
            return (
              <div key={image.id} className="bg-white rounded-lg shadow overflow-hidden">
                <div className="aspect-w-16 aspect-h-9 relative">
                  <img
                    src={image.url}
                    alt={image.original_name}
                    className="w-full h-48 object-cover"
                    onError={(e) => {
                      console.error('Failed to load image:', image.url)
                      const target = e.target as HTMLImageElement
                      target.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjEyMCIgdmlld0JveD0iMCAwIDIwMCAxMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMTIwIiBmaWxsPSIjRjNGNEY2Ii8+Cjx0ZXh0IHg9IjEwMCIgeT0iNjAiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxMiIgZmlsbD0iIzk0QTNBOCIgdGV4dC1hbmNob3I9Im1pZGRsZSI+SW1hZ2UgTm90IEZvdW5kPC90ZXh0Pgo8L3N2Zz4K'
                      target.alt = 'Image failed to load'
                    }}
                    onLoad={() => {
                      console.log('Image loaded successfully:', image.url)
                    }}
                  />
                </div>
                <div className="p-4">
                  <p className="text-sm text-gray-600 mb-2">{image.original_name}</p>
                  
                  {isEditing ? (
                    <div className="space-y-2">
                      <select
                        value={currentForm.type || image.type}
                        onChange={(e) => setEditForm({
                          ...editForm,
                          [image.id]: { ...currentForm, type: e.target.value }
                        })}
                        className="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                      >
                        <option value="photo">Photo</option>
                        <option value="sketch">Sketch</option>
                        <option value="inspiration">Inspiration</option>
                      </select>
                      <textarea
                        value={currentForm.description || ''}
                        onChange={(e) => setEditForm({
                          ...editForm,
                          [image.id]: { ...currentForm, description: e.target.value }
                        })}
                        placeholder="Description"
                        className="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                        rows={2}
                      />
                      <div className="flex space-x-2">
                        <button
                          onClick={() => saveImage(image.id)}
                          className="bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700"
                        >
                          Save
                        </button>
                        <button
                          onClick={cancelEditing}
                          className="bg-gray-300 text-gray-700 px-2 py-1 rounded text-xs hover:bg-gray-400"
                        >
                          Cancel
                        </button>
                      </div>
                    </div>
                  ) : (
                    <div>
                      {image.description && (
                        <p className="text-sm text-gray-800 mb-2">{image.description}</p>
                      )}
                      {canEdit && (
                        <div className="flex space-x-2">
                          <button
                            onClick={() => startEditing(image)}
                            className="text-blue-600 hover:text-blue-800 text-xs"
                          >
                            Edit
                          </button>
                          <button
                            onClick={() => deleteImage(image.id)}
                            className="text-red-600 hover:text-red-800 text-xs"
                          >
                            Delete
                          </button>
                        </div>
                      )}
                    </div>
                  )}
                </div>
              </div>
            )
          })}
        </div>
        
        {typeImages.length === 0 && (
          <div className="text-center py-8">
            <p className="text-gray-500 mb-2">No {title.toLowerCase()} uploaded yet.</p>
            {canEdit && (
              <p className="text-sm text-blue-600">Click "Upload {title}" above to add your first {type}.</p>
            )}
          </div>
        )}
      </div>
    )
  }

  if (loading) {
    return <div className="text-center py-4">Loading images...</div>
  }

  if (error) {
    return (
      <div className="bg-red-50 border border-red-200 rounded-lg p-4">
        <div className="flex items-center">
          <div className="flex-shrink-0">
            <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
            </svg>
          </div>
          <div className="ml-3">
            <h3 className="text-sm font-medium text-red-800">
              Error loading project images
            </h3>
            <div className="mt-2 text-sm text-red-700">
              <p>{error}</p>
            </div>
            <div className="mt-3">
              <button
                onClick={() => {
                  setError(null)
                  fetchImages()
                }}
                className="bg-red-100 text-red-800 px-3 py-1 rounded text-sm hover:bg-red-200"
              >
                Try Again
              </button>
            </div>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-bold text-gray-900">ART Uploads</h2>
        {canEdit && (
          <p className="text-sm text-gray-600">Upload photos, sketches, and inspiration images for this project</p>
        )}
      </div>
      
      {renderImageSection('photo', 'Photos')}
      {renderImageSection('sketch', 'Sketches')}
      {renderImageSection('inspiration', 'Inspiration')}
    </div>
  )
}