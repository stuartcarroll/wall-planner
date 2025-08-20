'use client'

import { useState, useEffect } from 'react'
import AdminLayout from '@/components/AdminLayout'
import { useAuth } from '@/contexts/AuthContext'

interface AdminStats {
  totalUsers: number
  totalProjects: number
  totalPaintBundles: number
  totalImages: number
  recentActivity: {
    type: string
    description: string
    timestamp: string
  }[]
}

export default function AdminDashboard() {
  const [stats, setStats] = useState<AdminStats | null>(null)
  const [loading, setLoading] = useState(true)
  const { token } = useAuth()

  useEffect(() => {
    fetchStats()
  }, [])

  const fetchStats = async () => {
    if (!token) return

    try {
      setLoading(true)
      // Mock data for now - in a real app, you'd fetch from an API
      await new Promise(resolve => setTimeout(resolve, 1000)) // Simulate API call
      
      setStats({
        totalUsers: 42,
        totalProjects: 128,
        totalPaintBundles: 67,
        totalImages: 324,
        recentActivity: [
          {
            type: 'user',
            description: 'New user John Doe registered',
            timestamp: '2025-08-20T10:30:00Z'
          },
          {
            type: 'project',
            description: 'Project "Living Room Renovation" was created',
            timestamp: '2025-08-20T09:15:00Z'
          },
          {
            type: 'bundle',
            description: 'Paint bundle "Modern Neutrals" was updated',
            timestamp: '2025-08-20T08:45:00Z'
          }
        ]
      })
    } catch (err) {
      console.error('Failed to fetch admin stats:', err)
    } finally {
      setLoading(false)
    }
  }

  const StatCard = ({ title, value, icon, color }: { title: string, value: number, icon: string, color: string }) => (
    <div className={`bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow`}>
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm font-medium text-gray-600">{title}</p>
          <p className="text-3xl font-bold text-gray-900 mt-1">{value.toLocaleString()}</p>
        </div>
        <div className={`w-12 h-12 rounded-lg bg-gradient-to-r ${color} flex items-center justify-center text-white text-xl`}>
          {icon}
        </div>
      </div>
    </div>
  )

  const getActivityIcon = (type: string) => {
    switch (type) {
      case 'user': return '👤'
      case 'project': return '📁'
      case 'bundle': return '📦'
      default: return '📝'
    }
  }

  const formatTimestamp = (timestamp: string) => {
    return new Date(timestamp).toLocaleString()
  }

  if (loading) {
    return (
      <AdminLayout>
        <div className="animate-pulse">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {[...Array(4)].map((_, i) => (
              <div key={i} className="bg-gray-200 h-32 rounded-xl"></div>
            ))}
          </div>
          <div className="bg-gray-200 h-64 rounded-xl"></div>
        </div>
      </AdminLayout>
    )
  }

  return (
    <AdminLayout>
      <div className="space-y-8">
        {/* Header */}
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
          <p className="text-gray-600">Overview of your Wall Planner system</p>
        </div>

        {/* Stats Grid */}
        {stats && (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <StatCard
              title="Total Users"
              value={stats.totalUsers}
              icon="👥"
              color="from-blue-500 to-blue-600"
            />
            <StatCard
              title="Total Projects"
              value={stats.totalProjects}
              icon="📁"
              color="from-green-500 to-green-600"
            />
            <StatCard
              title="Paint Bundles"
              value={stats.totalPaintBundles}
              icon="📦"
              color="from-purple-500 to-purple-600"
            />
            <StatCard
              title="Images Uploaded"
              value={stats.totalImages}
              icon="🖼️"
              color="from-orange-500 to-orange-600"
            />
          </div>
        )}

        {/* Recent Activity */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-200">
          <div className="px-6 py-4 border-b border-gray-200">
            <h2 className="text-lg font-semibold text-gray-900">Recent Activity</h2>
          </div>
          <div className="p-6">
            {stats?.recentActivity && stats.recentActivity.length > 0 ? (
              <div className="space-y-4">
                {stats.recentActivity.map((activity, index) => (
                  <div key={index} className="flex items-start space-x-3 p-3 hover:bg-gray-50 rounded-lg transition-colors">
                    <div className="flex-shrink-0">
                      <div className="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                        {getActivityIcon(activity.type)}
                      </div>
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm text-gray-900">{activity.description}</p>
                      <p className="text-xs text-gray-500 mt-1">{formatTimestamp(activity.timestamp)}</p>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-gray-500 text-center py-8">No recent activity</p>
            )}
          </div>
        </div>

        {/* Quick Actions */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-200">
          <div className="px-6 py-4 border-b border-gray-200">
            <h2 className="text-lg font-semibold text-gray-900">Quick Actions</h2>
          </div>
          <div className="p-6">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <button className="flex items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all group">
                <div className="text-center">
                  <div className="text-2xl mb-2 group-hover:scale-110 transition-transform">👤</div>
                  <p className="text-sm font-medium text-gray-700">Create User</p>
                </div>
              </button>
              <button className="flex items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition-all group">
                <div className="text-center">
                  <div className="text-2xl mb-2 group-hover:scale-110 transition-transform">📦</div>
                  <p className="text-sm font-medium text-gray-700">New Bundle</p>
                </div>
              </button>
              <button className="flex items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-all group">
                <div className="text-center">
                  <div className="text-2xl mb-2 group-hover:scale-110 transition-transform">🏢</div>
                  <p className="text-sm font-medium text-gray-700">User Group</p>
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </AdminLayout>
  )
}