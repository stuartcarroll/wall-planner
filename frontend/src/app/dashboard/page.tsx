'use client'

import { useState, useEffect } from 'react'
import { useAuth } from '@/contexts/AuthContext'
import Navigation from '@/components/Navigation'
import { useRouter } from 'next/navigation'

interface DashboardStats {
  total_projects: number
  active_projects: number
  completed_projects: number
  total_paint_bundles: number
  recent_projects: Array<{
    id: number
    name: string
    location?: string
    created_at: string
  }>
}

export default function Dashboard() {
  const [stats, setStats] = useState<DashboardStats | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const { user, token } = useAuth()
  const router = useRouter()

  useEffect(() => {
    if (!user) {
      router.push('/login')
      return
    }
    fetchDashboardData()
  }, [user, router])

  const fetchDashboardData = async () => {
    if (!token) return

    try {
      setLoading(true)
      const response = await fetch('/api/dashboard', {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      
      if (!response.ok) {
        // If dashboard API doesn't exist, create mock data
        if (response.status === 404) {
          setStats({
            total_projects: 0,
            active_projects: 0,
            completed_projects: 0,
            total_paint_bundles: 0,
            recent_projects: []
          })
          return
        }
        throw new Error('Failed to fetch dashboard data')
      }
      
      const data = await response.json()
      setStats(data)
    } catch (err) {
      console.error('Dashboard error:', err)
      // Create mock data for now
      setStats({
        total_projects: 0,
        active_projects: 0,
        completed_projects: 0,
        total_paint_bundles: 0,
        recent_projects: []
      })
    } finally {
      setLoading(false)
    }
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50">
        <Navigation />
        <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <div className="text-center">Loading dashboard...</div>
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
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <Navigation />
      
      <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">
            Welcome back, {user?.name}!
          </h1>
          <p className="mt-2 text-gray-600">
            Here's an overview of your wall planning projects
          </p>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div className="bg-white overflow-hidden shadow rounded-lg">
            <div className="p-5">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                    <span className="text-white text-sm font-medium">P</span>
                  </div>
                </div>
                <div className="ml-5 w-0 flex-1">
                  <dl>
                    <dt className="text-sm font-medium text-gray-500 truncate">
                      Total Projects
                    </dt>
                    <dd className="text-lg font-medium text-gray-900">
                      {stats?.total_projects || 0}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div className="bg-white overflow-hidden shadow rounded-lg">
            <div className="p-5">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                    <span className="text-white text-sm font-medium">A</span>
                  </div>
                </div>
                <div className="ml-5 w-0 flex-1">
                  <dl>
                    <dt className="text-sm font-medium text-gray-500 truncate">
                      Active Projects
                    </dt>
                    <dd className="text-lg font-medium text-gray-900">
                      {stats?.active_projects || 0}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div className="bg-white overflow-hidden shadow rounded-lg">
            <div className="p-5">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                    <span className="text-white text-sm font-medium">C</span>
                  </div>
                </div>
                <div className="ml-5 w-0 flex-1">
                  <dl>
                    <dt className="text-sm font-medium text-gray-500 truncate">
                      Completed Projects
                    </dt>
                    <dd className="text-lg font-medium text-gray-900">
                      {stats?.completed_projects || 0}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div className="bg-white overflow-hidden shadow rounded-lg">
            <div className="p-5">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center">
                    <span className="text-white text-sm font-medium">B</span>
                  </div>
                </div>
                <div className="ml-5 w-0 flex-1">
                  <dl>
                    <dt className="text-sm font-medium text-gray-500 truncate">
                      Paint Bundles
                    </dt>
                    <dd className="text-lg font-medium text-gray-900">
                      {stats?.total_paint_bundles || 0}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Quick Actions */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <div className="bg-white shadow rounded-lg p-6">
            <h2 className="text-lg font-medium text-gray-900 mb-4">Quick Actions</h2>
            <div className="space-y-3">
              <button
                onClick={() => router.push('/projects')}
                className="w-full text-left px-4 py-3 border border-gray-200 rounded-md hover:bg-gray-50 flex items-center"
              >
                <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                  <span className="text-blue-600 text-sm">+</span>
                </div>
                <div>
                  <div className="font-medium text-gray-900">Create New Project</div>
                  <div className="text-sm text-gray-500">Start planning a new wall project</div>
                </div>
              </button>
              
              <button
                onClick={() => router.push('/paints')}
                className="w-full text-left px-4 py-3 border border-gray-200 rounded-md hover:bg-gray-50 flex items-center"
              >
                <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                  <span className="text-green-600 text-sm">🎨</span>
                </div>
                <div>
                  <div className="font-medium text-gray-900">Browse Paint Catalog</div>
                  <div className="text-sm text-gray-500">Explore available paint colors</div>
                </div>
              </button>
            </div>
          </div>

          <div className="bg-white shadow rounded-lg p-6">
            <h2 className="text-lg font-medium text-gray-900 mb-4">Recent Projects</h2>
            {stats?.recent_projects && stats.recent_projects.length > 0 ? (
              <div className="space-y-3">
                {stats.recent_projects.slice(0, 3).map((project) => (
                  <div key={project.id} className="flex items-center py-2">
                    <div className="w-2 h-2 bg-blue-400 rounded-full mr-3"></div>
                    <div className="flex-1 min-w-0">
                      <div className="font-medium text-gray-900 truncate">
                        {project.name}
                      </div>
                      <div className="text-sm text-gray-500">
                        {project.location && `${project.location} • `}
                        {new Date(project.created_at).toLocaleDateString()}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center py-6">
                <p className="text-gray-500">No projects yet</p>
                <button
                  onClick={() => router.push('/projects')}
                  className="mt-2 text-blue-600 hover:text-blue-700 text-sm font-medium"
                >
                  Create your first project
                </button>
              </div>
            )}
          </div>
        </div>

        {/* Getting Started */}
        <div className="bg-blue-50 border border-blue-200 rounded-lg p-6">
          <div className="flex items-start">
            <div className="flex-shrink-0">
              <div className="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                <span className="text-white text-sm">💡</span>
              </div>
            </div>
            <div className="ml-4">
              <h3 className="text-lg font-medium text-blue-900">Getting Started</h3>
              <p className="mt-1 text-blue-700">
                Welcome to your wall planning dashboard! Start by creating a new project, 
                then explore our paint catalog to find the perfect colors for your space.
              </p>
              <div className="mt-4 flex space-x-4">
                <button
                  onClick={() => router.push('/projects')}
                  className="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700"
                >
                  Create Project
                </button>
                <button
                  onClick={() => router.push('/paints')}
                  className="bg-white text-blue-600 px-4 py-2 rounded-md text-sm font-medium border border-blue-300 hover:bg-blue-50"
                >
                  Browse Catalog
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}