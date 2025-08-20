'use client'

import { useState, useEffect } from 'react'
import { useAuth } from '@/contexts/AuthContext'
import Navigation from '@/components/Navigation'
import { useRouter } from 'next/navigation'

interface User {
  id: number
  name: string
  email: string
}

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

interface ProjectResponse {
  data: Project[]
  can_create: boolean
  is_admin: boolean
}

export default function Projects() {
  const [projects, setProjects] = useState<Project[]>([])
  const [users, setUsers] = useState<User[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [showCreateForm, setShowCreateForm] = useState(false)
  const [isAdmin, setIsAdmin] = useState(false)
  const [editingProject, setEditingProject] = useState<number | null>(null)
  const [editForm, setEditForm] = useState<{[key: number]: Partial<Project>}>({})
  const [saving, setSaving] = useState<number | null>(null)
  const [viewMode, setViewMode] = useState<'cards' | 'list'>('cards')
  const [selectedProjects, setSelectedProjects] = useState<number[]>([])
  const [searchTerm, setSearchTerm] = useState('')
  const [statusFilter, setStatusFilter] = useState<string>('')
  const [bulkAction, setBulkAction] = useState('')
  const { user, token } = useAuth()
  const router = useRouter()

  const [newProject, setNewProject] = useState({
    name: '',
    description: '',
    location: '',
    location_url: '',
    wall_height_cm: '',
    wall_width_cm: '',
    status: 'active',
    manager_email: ''
  })

  useEffect(() => {
    if (!user) {
      router.push('/login')
      return
    }
    fetchProjects()
    fetchUsers()
  }, [user, router])

  const fetchProjects = async () => {
    if (!token) return

    try {
      setLoading(true)
      const response = await fetch('/api/projects', {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      
      if (!response.ok) throw new Error('Failed to fetch projects')
      
      const data: ProjectResponse = await response.json()
      setProjects(data.data)
      setIsAdmin(data.is_admin)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'An error occurred')
    } finally {
      setLoading(false)
    }
  }

  const fetchUsers = async () => {
    if (!token) return

    try {
      const response = await fetch('/api/users', {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      
      if (!response.ok) throw new Error('Failed to fetch users')
      
      const data = await response.json()
      setUsers(data.data)
    } catch (err) {
      console.error('Failed to fetch users:', err)
    }
  }

  const handleCreateProject = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!token) return

    try {
      const response = await fetch('/api/projects', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          name: newProject.name,
          description: newProject.description || null,
          location: newProject.location || null,
          location_url: newProject.location_url || null,
          wall_height_cm: newProject.wall_height_cm ? parseFloat(newProject.wall_height_cm) : null,
          wall_width_cm: newProject.wall_width_cm ? parseFloat(newProject.wall_width_cm) : null,
          status: newProject.status,
          manager_email: newProject.manager_email || null,
        }),
      })
      
      if (!response.ok) throw new Error('Failed to create project')
      
      setNewProject({
        name: '',
        description: '',
        location: '',
        location_url: '',
        wall_height_cm: '',
        wall_width_cm: '',
        status: 'active',
        manager_email: ''
      })
      setShowCreateForm(false)
      fetchProjects()
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to create project')
    }
  }

  const startEditing = (project: Project) => {
    setEditingProject(project.id)
    setEditForm({
      ...editForm,
      [project.id]: {
        name: project.name,
        description: project.description || '',
        location: project.location || '',
        location_url: project.location_url || '',
        wall_height_cm: project.wall_height_cm || '',
        wall_width_cm: project.wall_width_cm || '',
        status: project.status || 'active',
        manager_email: project.manager_email || ''
      }
    })
  }

  const cancelEditing = () => {
    setEditingProject(null)
    setEditForm({})
  }

  const handleEditFormChange = (projectId: number, field: string, value: string) => {
    setEditForm({
      ...editForm,
      [projectId]: {
        ...editForm[projectId],
        [field]: value
      }
    })
  }

  const saveProject = async (projectId: number) => {
    if (!token || !editForm[projectId]) return

    try {
      setSaving(projectId)
      const response = await fetch(`/api/projects/${projectId}`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(editForm[projectId]),
      })
      
      if (!response.ok) throw new Error('Failed to update project')
      
      // Update the project in the local state
      setProjects(projects.map(p => 
        p.id === projectId 
          ? { ...p, ...editForm[projectId] }
          : p
      ))
      
      setEditingProject(null)
      setEditForm({})
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to update project')
    } finally {
      setSaving(null)
    }
  }

  const deleteProject = async (projectId: number) => {
    if (!token) return
    
    if (!confirm('Are you sure you want to delete this project?')) return

    try {
      const response = await fetch(`/api/projects/${projectId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      
      if (!response.ok) throw new Error('Failed to delete project')
      
      setProjects(projects.filter(p => p.id !== projectId))
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to delete project')
    }
  }

  const toggleProjectSelection = (projectId: number) => {
    setSelectedProjects(prev => 
      prev.includes(projectId) 
        ? prev.filter(id => id !== projectId)
        : [...prev, projectId]
    )
  }

  const toggleSelectAll = () => {
    const filteredProjects = getFilteredProjects()
    const allSelected = filteredProjects.every(project => selectedProjects.includes(project.id))
    
    if (allSelected) {
      setSelectedProjects([])
    } else {
      setSelectedProjects(filteredProjects.map(p => p.id))
    }
  }

  const getFilteredProjects = () => {
    return projects.filter(project => {
      const matchesSearch = searchTerm === '' || 
        project.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        (project.description && project.description.toLowerCase().includes(searchTerm.toLowerCase())) ||
        (project.location && project.location.toLowerCase().includes(searchTerm.toLowerCase())) ||
        project.user.name.toLowerCase().includes(searchTerm.toLowerCase())
      
      const matchesStatus = statusFilter === '' || project.status === statusFilter
      
      return matchesSearch && matchesStatus
    })
  }

  const handleBulkAction = async () => {
    if (!bulkAction || selectedProjects.length === 0) return

    const confirmMessage = bulkAction === 'delete' 
      ? `Are you sure you want to delete ${selectedProjects.length} project(s)?`
      : `Are you sure you want to change the status of ${selectedProjects.length} project(s) to ${bulkAction}?`
    
    if (!confirm(confirmMessage)) return

    try {
      if (bulkAction === 'delete') {
        // Delete selected projects
        await Promise.all(selectedProjects.map(async (projectId) => {
          const response = await fetch(`/api/projects/${projectId}`, {
            method: 'DELETE',
            headers: {
              'Authorization': `Bearer ${token}`,
            },
          })
          if (!response.ok) throw new Error(`Failed to delete project ${projectId}`)
        }))
        
        setProjects(projects.filter(p => !selectedProjects.includes(p.id)))
      } else {
        // Update status of selected projects
        await Promise.all(selectedProjects.map(async (projectId) => {
          const response = await fetch(`/api/projects/${projectId}`, {
            method: 'PUT',
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ status: bulkAction }),
          })
          if (!response.ok) throw new Error(`Failed to update project ${projectId}`)
        }))
        
        setProjects(projects.map(p => 
          selectedProjects.includes(p.id) 
            ? { ...p, status: bulkAction }
            : p
        ))
      }
      
      setSelectedProjects([])
      setBulkAction('')
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to perform bulk action')
    }
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50">
        <Navigation />
        <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <div className="text-center">Loading projects...</div>
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
          <h1 className="text-3xl font-bold text-gray-900">Projects</h1>
          <button
            onClick={() => setShowCreateForm(!showCreateForm)}
            className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
          >
            {showCreateForm ? 'Cancel' : 'Create Project'}
          </button>
        </div>

        {/* Search and Filter Controls */}
        <div className="bg-white p-4 rounded-lg shadow mb-6">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
              <input
                type="text"
                placeholder="Search projects..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <select
                value={statusFilter}
                onChange={(e) => setStatusFilter(e.target.value)}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="on_hold">On Hold</option>
                <option value="completed">Completed</option>
              </select>
            </div>
            <div className="flex space-x-2">
              <button
                onClick={() => setViewMode('cards')}
                className={`px-3 py-2 rounded text-sm ${viewMode === 'cards' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`}
              >
                Cards
              </button>
              <button
                onClick={() => setViewMode('list')}
                className={`px-3 py-2 rounded text-sm ${viewMode === 'list' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`}
              >
                List
              </button>
            </div>
            <div>
              <button
                onClick={() => {
                  setSearchTerm('')
                  setStatusFilter('')
                  setSelectedProjects([])
                }}
                className="w-full bg-gray-300 text-gray-700 px-3 py-2 rounded text-sm hover:bg-gray-400"
              >
                Clear Filters
              </button>
            </div>
          </div>

          {/* Bulk Actions */}
          {selectedProjects.length > 0 && (
            <div className="flex items-center space-x-4 p-3 bg-blue-50 rounded border">
              <span className="text-sm text-gray-700">
                {selectedProjects.length} project(s) selected
              </span>
              <select
                value={bulkAction}
                onChange={(e) => setBulkAction(e.target.value)}
                className="border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select action...</option>
                <option value="active">Set to Active</option>
                <option value="on_hold">Set to On Hold</option>
                <option value="completed">Set to Completed</option>
                <option value="delete">Delete Projects</option>
              </select>
              <button
                onClick={handleBulkAction}
                disabled={!bulkAction}
                className="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 disabled:bg-gray-400"
              >
                Apply
              </button>
            </div>
          )}
        </div>

        {showCreateForm && (
          <div className="bg-white p-6 rounded-lg shadow mb-6">
            <h2 className="text-xl font-semibold mb-4">Create New Project</h2>
            <form onSubmit={handleCreateProject} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Project Name *
                </label>
                <input
                  type="text"
                  required
                  value={newProject.name}
                  onChange={(e) => setNewProject({...newProject, name: e.target.value})}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Description
                </label>
                <textarea
                  value={newProject.description}
                  onChange={(e) => setNewProject({...newProject, description: e.target.value})}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  rows={3}
                />
              </div>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Location
                  </label>
                  <input
                    type="text"
                    value={newProject.location}
                    onChange={(e) => setNewProject({...newProject, location: e.target.value})}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Location URL
                  </label>
                  <input
                    type="url"
                    value={newProject.location_url}
                    onChange={(e) => setNewProject({...newProject, location_url: e.target.value})}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="https://maps.google.com/..."
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Wall Height (cm)
                  </label>
                  <input
                    type="number"
                    step="0.01"
                    min="0"
                    value={newProject.wall_height_cm}
                    onChange={(e) => setNewProject({...newProject, wall_height_cm: e.target.value})}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="e.g., 250"
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Wall Width (cm)
                  </label>
                  <input
                    type="number"
                    step="0.01"
                    min="0"
                    value={newProject.wall_width_cm}
                    onChange={(e) => setNewProject({...newProject, wall_width_cm: e.target.value})}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="e.g., 400"
                  />
                </div>
              </div>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Status
                  </label>
                  <select
                    value={newProject.status}
                    onChange={(e) => setNewProject({...newProject, status: e.target.value})}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="active">Active</option>
                    <option value="on_hold">On Hold</option>
                    <option value="completed">Completed</option>
                  </select>
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Manager
                  </label>
                  <select
                    value={newProject.manager_email}
                    onChange={(e) => setNewProject({...newProject, manager_email: e.target.value})}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">Select a manager (optional)</option>
                    {users.map((user) => (
                      <option key={user.id} value={user.email}>
                        {user.name} ({user.email})
                      </option>
                    ))}
                  </select>
                </div>
              </div>
              
              <div className="flex space-x-4">
                <button
                  type="submit"
                  className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
                >
                  Create Project
                </button>
                <button
                  type="button"
                  onClick={() => setShowCreateForm(false)}
                  className="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        )}

        {/* Projects Display */}
        {viewMode === 'list' ? (
          <div className="bg-white shadow rounded-lg overflow-hidden">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-3 py-3 text-left">
                    <input
                      type="checkbox"
                      onChange={toggleSelectAll}
                      checked={getFilteredProjects().length > 0 && getFilteredProjects().every(project => selectedProjects.includes(project.id))}
                      className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Project Name
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Owner
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Manager
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Location
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {getFilteredProjects().map((project) => {
                  const canEdit = isAdmin || project.user.id === user?.id
                  return (
                    <tr key={project.id} className={selectedProjects.includes(project.id) ? 'bg-blue-50' : 'hover:bg-gray-50'}>
                      <td className="px-3 py-4">
                        <input
                          type="checkbox"
                          checked={selectedProjects.includes(project.id)}
                          onChange={() => toggleProjectSelection(project.id)}
                          className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div>
                          <div className="text-sm font-medium text-gray-900">{project.name}</div>
                          {project.description && (
                            <div className="text-sm text-gray-500 max-w-xs truncate">{project.description}</div>
                          )}
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                          project.status === 'completed' 
                            ? 'bg-green-100 text-green-800'
                            : project.status === 'on_hold'
                            ? 'bg-yellow-100 text-yellow-800'
                            : 'bg-blue-100 text-blue-800'
                        }`}>
                          {project.status === 'on_hold' ? 'On Hold' : project.status?.charAt(0).toUpperCase() + project.status?.slice(1)}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {project.user.name}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {project.manager_email || '—'}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {project.location ? (
                          <div>
                            {project.location}
                            {project.location_url && (
                              <a href={project.location_url} target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:text-blue-800 ml-1">
                                📍
                              </a>
                            )}
                          </div>
                        ) : '—'}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div className="flex space-x-2">
                          <button 
                            onClick={() => router.push(`/projects/${project.id}`)}
                            className="text-blue-600 hover:text-blue-800"
                          >
                            View
                          </button>
                          {canEdit && (
                            <button 
                              onClick={() => startEditing(project)}
                              className="text-green-600 hover:text-green-800"
                            >
                              Edit
                            </button>
                          )}
                          {canEdit && (
                            <button 
                              onClick={() => deleteProject(project.id)}
                              className="text-red-600 hover:text-red-800"
                            >
                              Delete
                            </button>
                          )}
                        </div>
                      </td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {getFilteredProjects().map((project) => {
              const isEditing = editingProject === project.id
              const currentForm = editForm[project.id] || {}
              const canEdit = isAdmin || project.user.id === user?.id
            
            return (
              <div key={project.id} className={`bg-white rounded-lg shadow-lg overflow-hidden ${selectedProjects.includes(project.id) ? 'ring-2 ring-blue-500' : ''}`}>
                <div className="p-6">
                  {/* Checkbox for selection */}
                  <div className="flex justify-between items-start mb-4">
                    <input
                      type="checkbox"
                      checked={selectedProjects.includes(project.id)}
                      onChange={() => toggleProjectSelection(project.id)}
                      className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                  </div>
                  {/* Project Name */}
                  {isEditing ? (
                    <input
                      type="text"
                      value={currentForm.name || ''}
                      onChange={(e) => handleEditFormChange(project.id, 'name', e.target.value)}
                      className="w-full text-lg font-semibold text-gray-900 mb-2 border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Project Name"
                    />
                  ) : (
                    <h3 className="text-lg font-semibold text-gray-900 mb-2">
                      {project.name}
                    </h3>
                  )}

                  {/* Description */}
                  {isEditing ? (
                    <textarea
                      value={currentForm.description || ''}
                      onChange={(e) => handleEditFormChange(project.id, 'description', e.target.value)}
                      className="w-full text-gray-600 mb-3 text-sm border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Description"
                      rows={2}
                    />
                  ) : (
                    project.description && (
                      <p className="text-gray-600 mb-3 text-sm">
                        {project.description}
                      </p>
                    )
                  )}

                  <div className="space-y-1 text-sm">
                    {/* Location */}
                    {isEditing ? (
                      <div>
                        <span className="font-medium">Location:</span>
                        <input
                          type="text"
                          value={currentForm.location || ''}
                          onChange={(e) => handleEditFormChange(project.id, 'location', e.target.value)}
                          className="ml-2 border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Location"
                        />
                      </div>
                    ) : (
                      project.location && (
                        <p><span className="font-medium">Location:</span> {project.location}</p>
                      )
                    )}

                    {/* Location URL */}
                    {isEditing ? (
                      <div>
                        <span className="font-medium">Location URL:</span>
                        <input
                          type="url"
                          value={currentForm.location_url || ''}
                          onChange={(e) => handleEditFormChange(project.id, 'location_url', e.target.value)}
                          className="ml-2 border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="https://maps.google.com/..."
                        />
                      </div>
                    ) : (
                      project.location_url && (
                        <p><span className="font-medium">Location URL:</span> 
                          <a href={project.location_url} target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:text-blue-800 ml-1">
                            View Map
                          </a>
                        </p>
                      )
                    )}

                    {/* Wall Dimensions */}
                    {isEditing ? (
                      <div className="space-y-1">
                        <div>
                          <span className="font-medium">Wall Height (cm):</span>
                          <input
                            type="number"
                            step="0.01"
                            min="0"
                            value={currentForm.wall_height_cm || ''}
                            onChange={(e) => handleEditFormChange(project.id, 'wall_height_cm', e.target.value)}
                            className="ml-2 w-20 border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="250"
                          />
                        </div>
                        <div>
                          <span className="font-medium">Wall Width (cm):</span>
                          <input
                            type="number"
                            step="0.01"
                            min="0"
                            value={currentForm.wall_width_cm || ''}
                            onChange={(e) => handleEditFormChange(project.id, 'wall_width_cm', e.target.value)}
                            className="ml-2 w-20 border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="400"
                          />
                        </div>
                      </div>
                    ) : (
                      (project.wall_height_cm || project.wall_width_cm) && (
                        <p><span className="font-medium">Wall Size:</span> 
                          {project.wall_width_cm && `${project.wall_width_cm}cm`}
                          {project.wall_width_cm && project.wall_height_cm && ' × '}
                          {project.wall_height_cm && `${project.wall_height_cm}cm`}
                          {project.wall_width_cm && project.wall_height_cm && (
                            <span className="text-gray-500 text-xs ml-1">
                              ({(project.wall_width_cm * project.wall_height_cm / 10000).toFixed(2)}m²)
                            </span>
                          )}
                        </p>
                      )
                    )}

                    {/* Status */}
                    {isEditing ? (
                      <div>
                        <span className="font-medium">Status:</span>
                        <select
                          value={currentForm.status || 'active'}
                          onChange={(e) => handleEditFormChange(project.id, 'status', e.target.value)}
                          className="ml-2 border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                          <option value="active">Active</option>
                          <option value="on_hold">On Hold</option>
                          <option value="completed">Completed</option>
                        </select>
                      </div>
                    ) : (
                      <p><span className="font-medium">Status:</span> 
                        <span className={`ml-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                          project.status === 'completed' 
                            ? 'bg-green-100 text-green-800'
                            : project.status === 'on_hold'
                            ? 'bg-yellow-100 text-yellow-800'
                            : 'bg-blue-100 text-blue-800'
                        }`}>
                          {project.status === 'on_hold' ? 'On Hold' : project.status?.charAt(0).toUpperCase() + project.status?.slice(1)}
                        </span>
                      </p>
                    )}

                    {/* Manager */}
                    {isEditing ? (
                      <div>
                        <span className="font-medium">Manager:</span>
                        <select
                          value={currentForm.manager_email || ''}
                          onChange={(e) => handleEditFormChange(project.id, 'manager_email', e.target.value)}
                          className="ml-2 border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                          <option value="">No manager</option>
                          {users.map((user) => (
                            <option key={user.id} value={user.email}>
                              {user.name} ({user.email})
                            </option>
                          ))}
                        </select>
                      </div>
                    ) : (
                      project.manager_email && (
                        <p><span className="font-medium">Manager:</span> {project.manager_email}</p>
                      )
                    )}

                    <p><span className="font-medium">Owner:</span> {project.user.name}</p>
                    <p><span className="font-medium">Permalink:</span> 
                      <span className="text-blue-600 ml-1">/p/{project.permalink}</span>
                    </p>
                  </div>

                  {/* Action Buttons */}
                  <div className="mt-4 flex space-x-2">
                    {isEditing ? (
                      <>
                        <button
                          onClick={() => saveProject(project.id)}
                          disabled={saving === project.id}
                          className="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 disabled:bg-gray-400"
                        >
                          {saving === project.id ? 'Saving...' : 'Save'}
                        </button>
                        <button
                          onClick={cancelEditing}
                          disabled={saving === project.id}
                          className="bg-gray-300 text-gray-700 px-3 py-1 rounded text-sm hover:bg-gray-400 disabled:bg-gray-200"
                        >
                          Cancel
                        </button>
                      </>
                    ) : (
                      <>
                        <button 
                          onClick={() => router.push(`/projects/${project.id}`)}
                          className="text-blue-600 hover:text-blue-800 text-sm"
                        >
                          View Images
                        </button>
                        {canEdit && (
                          <button 
                            onClick={() => startEditing(project)}
                            className="text-green-600 hover:text-green-800 text-sm"
                          >
                            Edit
                          </button>
                        )}
                        {canEdit && (
                          <button 
                            onClick={() => deleteProject(project.id)}
                            className="text-red-600 hover:text-red-800 text-sm"
                          >
                            Delete
                          </button>
                        )}
                      </>
                    )}
                  </div>
                </div>
              </div>
            )
          })}
          </div>
        )}

        {getFilteredProjects().length === 0 && projects.length > 0 && (
          <div className="text-center py-12">
            <p className="text-gray-500">No projects match your current filters.</p>
          </div>
        )}

        {projects.length === 0 && (
          <div className="text-center py-12">
            <p className="text-gray-500">No projects found. Create your first project!</p>
          </div>
        )}
      </div>
    </div>
  )
}