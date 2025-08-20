'use client'

import { useState, useEffect } from 'react'
import { useAuth } from '@/contexts/AuthContext'
import Navigation from '@/components/Navigation'
import ProjectImages from '@/components/ProjectImages'
import ProjectPaintBundles from '@/components/ProjectPaintBundles'
import { useRouter } from 'next/navigation'

interface Project {
  id: number
  name: string
  description?: string
  location?: string
  location_url?: string
  wall_height_cm?: number
  wall_width_cm?: number
  status?: string
  manager_email?: string
  permalink: string
  user: {
    id: number
    name: string
    email: string
  }
  created_at: string
  updated_at: string
}

interface ProjectDetailResponse {
  data: Project
  can_edit: boolean
  can_delete: boolean
}

export default function ProjectDetail({ params }: { params: { id: string } }) {
  const [project, setProject] = useState<Project | null>(null)
  const [canEdit, setCanEdit] = useState(false)
  const [canDelete, setCanDelete] = useState(false)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const { user, token } = useAuth()
  const router = useRouter()

  useEffect(() => {
    if (!user) {
      router.push('/login')
      return
    }
    fetchProject()
  }, [user, router, params.id])

  const fetchProject = async () => {
    if (!token) return

    try {
      setLoading(true)
      const response = await fetch(`/api/projects/${params.id}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}))
        if (response.status === 404) {
          throw new Error(`Project with ID ${params.id} not found. Please check the project ID and ensure it exists.`)
        }
        if (response.status === 403) {
          throw new Error('You do not have permission to view this project. Contact the project owner or administrator.')
        }
        throw new Error(`Failed to fetch project (${response.status}): ${errorData.message || response.statusText}`)
      }
      
      const data: ProjectDetailResponse = await response.json()
      setProject(data.data)
      setCanEdit(data.can_edit)
      setCanDelete(data.can_delete)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'An error occurred')
    } finally {
      setLoading(false)
    }
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50">
        <Navigation />
        <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <div className="text-center">Loading project...</div>
        </div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="min-h-screen bg-gray-50">
        <Navigation />
        <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <div className="text-red-600 text-center">Error: {error}</div>
          <div className="text-center mt-4">
            <button
              onClick={() => router.push('/projects')}
              className="text-blue-600 hover:text-blue-800"
            >
              ← Back to Projects
            </button>
          </div>
        </div>
      </div>
    )
  }

  if (!project) {
    return (
      <div className="min-h-screen bg-gray-50">
        <Navigation />
        <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <div className="text-center">Project not found</div>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <Navigation />
      
      <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div className="mb-6">
          <button
            onClick={() => router.push('/projects')}
            className="text-blue-600 hover:text-blue-800 mb-4"
          >
            ← Back to Projects
          </button>
          
          <div className="bg-white rounded-lg shadow p-6">
            <h1 className="text-3xl font-bold text-gray-900 mb-4">{project.name}</h1>
            
            {project.description && (
              <p className="text-gray-600 mb-4">{project.description}</p>
            )}
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              {project.location && (
                <p><span className="font-medium">Location:</span> {project.location}</p>
              )}
              {project.manager_email && (
                <p><span className="font-medium">Manager:</span> {project.manager_email}</p>
              )}
              <p><span className="font-medium">Owner:</span> {project.user.name}</p>
              <p><span className="font-medium">Permalink:</span> 
                <span className="text-blue-600 ml-1">/p/{project.permalink}</span>
              </p>
              <p><span className="font-medium">Created:</span> {new Date(project.created_at).toLocaleDateString()}</p>
            </div>
          </div>
        </div>

        {/* Project Images Section - Full Width and Prominent */}
        <div className="bg-white rounded-lg shadow p-6 mb-6">
          <ProjectImages projectId={project.id} canEdit={canEdit} />
        </div>

        {/* Paint Bundles Section */}
        <div className="bg-white rounded-lg shadow p-6">
          <ProjectPaintBundles projectId={project.id} canEdit={canEdit} />
        </div>
      </div>
    </div>
  )
}