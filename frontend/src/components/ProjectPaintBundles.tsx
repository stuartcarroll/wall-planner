'use client'

import { useState, useEffect } from 'react'
import { useAuth } from '@/contexts/AuthContext'

interface PaintBundle {
  id: number
  name: string
  description?: string
  total_cost: number
  created_at: string
}

interface ProjectPaintBundlesProps {
  projectId: number
  canEdit: boolean
}

export default function ProjectPaintBundles({ projectId, canEdit }: ProjectPaintBundlesProps) {
  const [paintBundles, setPaintBundles] = useState<PaintBundle[]>([])
  const [availableBundles, setAvailableBundles] = useState<PaintBundle[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [showAddForm, setShowAddForm] = useState(false)
  const { token } = useAuth()

  useEffect(() => {
    fetchProjectBundles()
    if (canEdit) {
      fetchAvailableBundles()
    }
  }, [projectId, canEdit])

  const fetchProjectBundles = async () => {
    if (!token) return

    try {
      setLoading(true)
      const response = await fetch(`/api/projects/${projectId}/paint-bundles`, {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      
      if (response.ok) {
        const data = await response.json()
        setPaintBundles(data.data || [])
      } else {
        // If endpoint doesn't exist, show empty state
        setPaintBundles([])
      }
    } catch (err) {
      console.error('Failed to fetch project bundles:', err)
      setPaintBundles([])
    } finally {
      setLoading(false)
    }
  }

  const fetchAvailableBundles = async () => {
    if (!token) return

    try {
      const response = await fetch('/api/paint-bundles', {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      
      if (response.ok) {
        const data = await response.json()
        setAvailableBundles(data.data || [])
      }
    } catch (err) {
      console.error('Failed to fetch available bundles:', err)
    }
  }

  const addBundleToProject = async (bundleId: number) => {
    if (!token) return

    try {
      const response = await fetch(`/api/projects/${projectId}/paint-bundles`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          paint_bundle_id: bundleId,
        }),
      })
      
      if (response.ok) {
        fetchProjectBundles()
        setShowAddForm(false)
      } else {
        setError('Failed to add bundle to project')
      }
    } catch (err) {
      setError('Failed to add bundle to project')
    }
  }

  const removeBundleFromProject = async (bundleId: number) => {
    if (!token) return
    
    if (!confirm('Are you sure you want to remove this paint bundle from the project?')) return

    try {
      const response = await fetch(`/api/projects/${projectId}/paint-bundles/${bundleId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      
      if (response.ok) {
        setPaintBundles(paintBundles.filter(b => b.id !== bundleId))
      } else {
        setError('Failed to remove bundle from project')
      }
    } catch (err) {
      setError('Failed to remove bundle from project')
    }
  }

  const totalBundleCost = paintBundles.reduce((sum, bundle) => sum + bundle.total_cost, 0)

  if (loading) {
    return <div className="text-center py-4">Loading paint bundles...</div>
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-4">
        <h3 className="text-lg font-semibold text-gray-900">Paint Bundles</h3>
        {canEdit && (
          <button
            onClick={() => setShowAddForm(!showAddForm)}
            className="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700"
          >
            {showAddForm ? 'Cancel' : 'Add Bundle'}
          </button>
        )}
      </div>

      {error && (
        <div className="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm">
          {error}
        </div>
      )}

      {showAddForm && (
        <div className="mb-4 p-4 bg-gray-50 rounded-lg">
          <h4 className="font-medium text-gray-900 mb-2">Add Paint Bundle to Project</h4>
          <div className="space-y-2">
            {availableBundles
              .filter(bundle => !paintBundles.some(pb => pb.id === bundle.id))
              .map((bundle) => (
              <div key={bundle.id} className="flex justify-between items-center p-2 bg-white rounded border">
                <div>
                  <span className="font-medium">{bundle.name}</span>
                  {bundle.description && <span className="text-gray-500 text-sm ml-2">- {bundle.description}</span>}
                  <span className="text-green-600 font-medium ml-2">£{bundle.total_cost.toFixed(2)}</span>
                </div>
                <button
                  onClick={() => addBundleToProject(bundle.id)}
                  className="bg-green-600 text-white px-2 py-1 rounded text-sm hover:bg-green-700"
                >
                  Add
                </button>
              </div>
            ))}
            {availableBundles.filter(bundle => !paintBundles.some(pb => pb.id === bundle.id)).length === 0 && (
              <p className="text-gray-500 text-sm">No available bundles to add.</p>
            )}
          </div>
        </div>
      )}

      {paintBundles.length > 0 ? (
        <div className="space-y-3">
          {paintBundles.map((bundle) => (
            <div key={bundle.id} className="flex justify-between items-center p-4 bg-white border rounded-lg">
              <div>
                <h4 className="font-medium text-gray-900">{bundle.name}</h4>
                {bundle.description && (
                  <p className="text-gray-600 text-sm">{bundle.description}</p>
                )}
                <div className="flex items-center mt-1 text-sm text-gray-500">
                  <span>Created: {new Date(bundle.created_at).toLocaleDateString()}</span>
                  <span className="mx-2">•</span>
                  <span className="text-green-600 font-medium">£{bundle.total_cost.toFixed(2)}</span>
                </div>
              </div>
              <div className="flex space-x-2">
                <button
                  onClick={() => window.open(`/paint-bundles`, '_blank')}
                  className="text-blue-600 hover:text-blue-800 text-sm"
                >
                  View Details
                </button>
                {canEdit && (
                  <button
                    onClick={() => removeBundleFromProject(bundle.id)}
                    className="text-red-600 hover:text-red-800 text-sm"
                  >
                    Remove
                  </button>
                )}
              </div>
            </div>
          ))}
          
          <div className="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div className="flex justify-between items-center">
              <span className="font-medium text-gray-900">Total Bundle Cost:</span>
              <span className="text-xl font-bold text-blue-600">£{totalBundleCost.toFixed(2)}</span>
            </div>
          </div>
        </div>
      ) : (
        <div className="text-center py-6 bg-gray-50 rounded-lg">
          <p className="text-gray-500">No paint bundles assigned to this project yet.</p>
          {canEdit && (
            <p className="text-gray-400 text-sm mt-1">Add paint bundles to track materials and costs.</p>
          )}
        </div>
      )}
    </div>
  )
}