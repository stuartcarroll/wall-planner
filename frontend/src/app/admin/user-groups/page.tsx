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

interface UserGroup {
  id: number
  name: string
  users: User[]
  created_at: string
}

interface UserGroupResponse {
  data: UserGroup[]
}

export default function AdminUserGroups() {
  const [userGroups, setUserGroups] = useState<UserGroup[]>([])
  const [users, setUsers] = useState<User[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [showCreateForm, setShowCreateForm] = useState(false)
  const [newGroupName, setNewGroupName] = useState('')
  const { user, token, isAdmin } = useAuth()
  const router = useRouter()

  useEffect(() => {
    if (!user) {
      router.push('/login')
      return
    }
    if (!isAdmin) {
      router.push('/dashboard')
      return
    }
    fetchUserGroups()
    fetchUsers()
  }, [user, isAdmin, router])

  const fetchUserGroups = async () => {
    if (!token) return

    try {
      setLoading(true)
      const response = await fetch('/api/admin/user-groups', {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      
      if (!response.ok) {
        // If endpoint doesn't exist, create mock data
        if (response.status === 404) {
          setUserGroups([])
          return
        }
        throw new Error('Failed to fetch user groups')
      }
      
      const data: UserGroupResponse = await response.json()
      setUserGroups(data.data)
    } catch (err) {
      console.error('User groups error:', err)
      // Create mock data for now
      setUserGroups([])
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

  const handleCreateGroup = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!token || !newGroupName.trim()) return

    try {
      const response = await fetch('/api/admin/user-groups', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          name: newGroupName.trim(),
        }),
      })
      
      if (!response.ok) throw new Error('Failed to create user group')
      
      setNewGroupName('')
      setShowCreateForm(false)
      fetchUserGroups()
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to create user group')
    }
  }

  const deleteUserGroup = async (groupId: number) => {
    if (!token) return
    
    if (!confirm('Are you sure you want to delete this user group?')) return

    try {
      const response = await fetch(`/api/admin/user-groups/${groupId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      
      if (!response.ok) throw new Error('Failed to delete user group')
      
      setUserGroups(userGroups.filter(g => g.id !== groupId))
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to delete user group')
    }
  }

  const addUserToGroup = async (groupId: number, userId: number) => {
    if (!token) return

    try {
      const response = await fetch(`/api/admin/user-groups/${groupId}/users`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          user_id: userId,
        }),
      })
      
      if (!response.ok) throw new Error('Failed to add user to group')
      
      fetchUserGroups()
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to add user to group')
    }
  }

  const removeUserFromGroup = async (groupId: number, userId: number) => {
    if (!token) return

    try {
      const response = await fetch(`/api/admin/user-groups/${groupId}/users/${userId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      
      if (!response.ok) throw new Error('Failed to remove user from group')
      
      fetchUserGroups()
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to remove user from group')
    }
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50">
        <Navigation />
        <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <div className="text-center">Loading user groups...</div>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <Navigation />
      
      <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center mb-6">
          <h1 className="text-3xl font-bold text-gray-900">User Group Management</h1>
          <button
            onClick={() => setShowCreateForm(!showCreateForm)}
            className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
          >
            {showCreateForm ? 'Cancel' : 'Create Group'}
          </button>
        </div>

        {error && (
          <div className="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {error}
          </div>
        )}

        {showCreateForm && (
          <div className="bg-white p-6 rounded-lg shadow mb-6">
            <h2 className="text-xl font-semibold mb-4">Create New User Group</h2>
            <form onSubmit={handleCreateGroup} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Group Name *
                </label>
                <input
                  type="text"
                  required
                  value={newGroupName}
                  onChange={(e) => setNewGroupName(e.target.value)}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div className="flex space-x-4">
                <button
                  type="submit"
                  className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
                >
                  Create Group
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

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {userGroups.map((group) => (
            <div key={group.id} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-center mb-4">
                <h3 className="text-lg font-semibold text-gray-900">{group.name}</h3>
                <button
                  onClick={() => deleteUserGroup(group.id)}
                  className="text-red-600 hover:text-red-800 text-sm"
                >
                  Delete Group
                </button>
              </div>

              <div className="mb-4">
                <h4 className="text-sm font-medium text-gray-700 mb-2">Members ({group.users.length})</h4>
                {group.users.length > 0 ? (
                  <div className="space-y-2">
                    {group.users.map((groupUser) => (
                      <div key={groupUser.id} className="flex justify-between items-center bg-gray-50 p-2 rounded">
                        <span className="text-sm">{groupUser.name} ({groupUser.email})</span>
                        <button
                          onClick={() => removeUserFromGroup(group.id, groupUser.id)}
                          className="text-red-600 hover:text-red-800 text-xs"
                        >
                          Remove
                        </button>
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="text-gray-500 text-sm">No members in this group</p>
                )}
              </div>

              <div>
                <h4 className="text-sm font-medium text-gray-700 mb-2">Add User to Group</h4>
                <select
                  onChange={(e) => {
                    if (e.target.value) {
                      addUserToGroup(group.id, parseInt(e.target.value))
                      e.target.value = ''
                    }
                  }}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Select a user to add...</option>
                  {users
                    .filter(u => !group.users.some(gu => gu.id === u.id))
                    .map((availableUser) => (
                    <option key={availableUser.id} value={availableUser.id}>
                      {availableUser.name} ({availableUser.email})
                    </option>
                  ))}
                </select>
              </div>

              <div className="mt-4 text-xs text-gray-500">
                Created: {new Date(group.created_at).toLocaleDateString()}
              </div>
            </div>
          ))}
        </div>

        {userGroups.length === 0 && !loading && (
          <div className="text-center py-12">
            <p className="text-gray-500">No user groups found. Create your first group!</p>
          </div>
        )}
      </div>
    </div>
  )
}