<template>
  <AppLayout>
    <template #header>
      <div class="flex items-center">
        <h2 class="text-2xl font-bold text-gray-900">
          Dashboard
        </h2>
        <div class="ml-3 text-sm text-gray-500">
          Welcome back, {{ userName || 'User' }}!
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Welcome Message -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6 text-gray-900">
            <h3 class="text-lg font-semibold mb-2">
              Welcome back, {{ userName }}!
            </h3>
            <p class="text-gray-600">
              Ready to plan your next wall project? Here's what you can do:
            </p>
          </div>
        </div>

        <!-- Quick Actions Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
          <!-- Browse Paint Catalog -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-8 0 4 4 0 018 0zM7 21h10a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900">Paint Catalog</h3>
                  <p class="text-sm text-gray-500">Browse available paints and colors</p>
                </div>
              </div>
              <div class="mt-4">
                <Link href="/paints" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                  Browse Paints →
                </Link>
              </div>
            </div>
          </div>

          <!-- Manage Projects -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900">Projects</h3>
                  <p class="text-sm text-gray-500">Create and manage wall projects</p>
                </div>
              </div>
              <div class="mt-4">
                <Link href="/projects" class="text-green-600 hover:text-green-800 text-sm font-medium">
                  View Projects →
                </Link>
              </div>
            </div>
          </div>

          <!-- Paint Bundles -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900">Paint Bundles</h3>
                  <p class="text-sm text-gray-500">Manage your paint collections</p>
                </div>
              </div>
              <div class="mt-4">
                <Link href="/paint-bundles" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                  View Bundles →
                </Link>
              </div>
            </div>
          </div>

          <!-- Admin Panel (for admins only) -->
          <div v-if="isAdmin" class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900">Admin Panel</h3>
                  <p class="text-sm text-gray-500">Manage users and settings</p>
                </div>
              </div>
              <div class="mt-4 space-x-4">
                <Link href="/admin/users" class="text-red-600 hover:text-red-800 text-sm font-medium">
                  Manage Users →
                </Link>
                <Link href="/admin/user-groups" class="text-red-600 hover:text-red-800 text-sm font-medium">
                  User Groups →
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- User Info & Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- User Information -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Account</h3>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-600">Role:</span>
                  <span class="font-medium">
                    <span v-if="isAdmin" 
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                      Administrator
                    </span>
                    <span v-else 
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                      User
                    </span>
                  </span>
                </div>
                <div v-if="userGroups && userGroups.length > 0" class="flex justify-between">
                  <span class="text-gray-600">Groups:</span>
                  <div class="flex flex-wrap gap-1">
                    <span v-for="group in userGroups" :key="group.id"
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {{ group.name }}
                    </span>
                  </div>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Member since:</span>
                  <span class="font-medium">{{ formatDate(user.created_at) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Stats -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Stats</h3>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-600">Total Paints:</span>
                  <span class="font-medium">{{ stats.paintCount }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Your Projects:</span>
                  <span class="font-medium">{{ stats.userProjectCount }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Total Users:</span>
                  <span class="font-medium">{{ stats.userCount }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '../Layouts/AppLayout.vue'

// Props from Laravel controller
const props = defineProps({
  auth: Object,
  stats: Object,
})

const page = usePage()

// Safely access auth data with computed properties
const auth = computed(() => props.auth || page.props.auth || {})
const user = computed(() => auth.value.user || {})
const userName = computed(() => user.value.name || '')
const isAdmin = computed(() => user.value.role === 'admin')
const userGroups = computed(() => user.value.user_groups || [])

// Methods
const formatDate = (dateString) => {
  if (!dateString) return 'Unknown'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short' })
}
</script>