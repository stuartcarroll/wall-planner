'use client'

import { useState, useEffect } from 'react'
import { useAuth } from '@/contexts/AuthContext'
import Navigation from '@/components/Navigation'
import { useRouter } from 'next/navigation'

interface PaintBundle {
  id: number
  name: string
  description?: string
  total_cost: number
  item_count: number
  user: {
    id: number
    name: string
    email: string
  }
  created_at: string
  updated_at: string
}

interface PaintBundleResponse {
  data: PaintBundle[]
  can_create: boolean
  is_admin: boolean
}

export default function PaintBundles() {
  const [bundles, setBundles] = useState<PaintBundle[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const { user, token } = useAuth()
  const router = useRouter()

  useEffect(() => {
    if (!user) {
      router.push('/login')
      return
    }
    fetchBundles()
  }, [user, router])

  const fetchBundles = async () => {
    if (!token) return

    try {
      setLoading(true)
      const response = await fetch('/api/paint-bundles', {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      
      if (!response.ok) throw new Error('Failed to fetch paint bundles')
      
      const data: PaintBundleResponse = await response.json()
      setBundles(data.data)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'An error occurred')
    } finally {
      setLoading(false)
    }
  }

  const handleDeleteBundle = async (bundleId: number) => {
    if (!confirm('Are you sure you want to delete this paint bundle?')) {
      return
    }

    try {
      const response = await fetch(`/api/paint-bundles/${bundleId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })

      if (!response.ok) throw new Error('Failed to delete bundle')
      
      setBundles(bundles.filter(bundle => bundle.id !== bundleId))
    } catch (err) {
      alert('Failed to delete bundle. Please try again.')
    }
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50">
        <Navigation />
        <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <div className="text-center">Loading paint bundles...</div>
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
        <div className="flex justify-between items-center mb-6">
          <h1 className="text-3xl font-bold text-gray-900">Paint Bundles</h1>
          <button
            onClick={() => router.push('/paints')}
            className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
          >
            Create Bundle from Catalog
          </button>
        </div>

        {bundles.length === 0 ? (
          <div className="text-center py-12">
            <div className="w-24 h-24 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
              <svg className="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <h2 className="text-2xl font-semibold text-gray-900 mb-2">No paint bundles yet</h2>
            <p className="text-gray-600 mb-6">Create your first bundle by adding paints to your basket from the catalog!</p>
            <div className="space-x-4">
              <button
                onClick={() => router.push('/paints')}
                className="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700"
              >
                Browse Paint Catalog
              </button>
              <button
                onClick={() => router.push('/basket')}
                className="bg-gray-300 text-gray-700 px-6 py-3 rounded-md hover:bg-gray-400"
              >
                View Basket
              </button>
            </div>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {bundles.map((bundle) => (
              <div key={bundle.id} className="bg-white rounded-lg shadow-lg overflow-hidden">
                <div className="p-6">
                  <h3 className="text-lg font-semibold text-gray-900 mb-2">
                    {bundle.name}
                  </h3>
                  {bundle.description && (
                    <p className="text-gray-600 mb-3 text-sm">
                      {bundle.description}
                    </p>
                  )}
                  <div className="space-y-2 text-sm">
                    <div className="flex justify-between">
                      <span className="text-gray-500">Items:</span>
                      <span className="font-medium">{bundle.item_count} paints</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-gray-500">Total Cost:</span>
                      <span className="font-bold text-green-600">£{bundle.total_cost}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-gray-500">Created:</span>
                      <span>{new Date(bundle.created_at).toLocaleDateString()}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-gray-500">Owner:</span>
                      <span>{bundle.user.name}</span>
                    </div>
                  </div>
                  
                  <div className="mt-6 flex space-x-2">
                    <button 
                      onClick={() => router.push(`/paint-bundles/${bundle.id}`)}
                      className="flex-1 bg-blue-600 text-white py-2 px-3 rounded text-sm hover:bg-blue-700"
                    >
                      View Details
                    </button>
                    {(user?.id === bundle.user.id || user?.role === 'admin') && (
                      <button
                        onClick={() => handleDeleteBundle(bundle.id)}
                        className="bg-red-600 text-white py-2 px-3 rounded text-sm hover:bg-red-700"
                      >
                        Delete
                      </button>
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}

        {/* Quick Action Cards */}
        <div className="mt-12 grid grid-cols-1 md:grid-cols-2 gap-6">
          <div className="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div className="flex items-center">
              <div className="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l-1 12H6L5 9z" />
                </svg>
              </div>
              <div className="ml-4">
                <h3 className="text-lg font-medium text-blue-900">Create New Bundle</h3>
                <p className="text-blue-700 text-sm">Add paints to your basket and save as a bundle</p>
              </div>
            </div>
            <div className="mt-4">
              <button
                onClick={() => router.push('/paints')}
                className="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700"
              >
                Browse Paints
              </button>
            </div>
          </div>

          <div className="bg-green-50 border border-green-200 rounded-lg p-6">
            <div className="flex items-center">
              <div className="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div className="ml-4">
                <h3 className="text-lg font-medium text-green-900">Assign to Project</h3>
                <p className="text-green-700 text-sm">Link bundles to your wall planning projects</p>
              </div>
            </div>
            <div className="mt-4">
              <button
                onClick={() => router.push('/projects')}
                className="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700"
              >
                View Projects
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}