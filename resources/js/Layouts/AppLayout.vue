<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <h1 class="text-xl font-bold text-gray-900">Wall Planner</h1>
            </div>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:ml-6 md:flex md:space-x-8">
              <a href="/dashboard" 
                 class="text-gray-900 hover:text-gray-600 px-3 py-2 text-sm font-medium"
                 :class="{ 'border-b-2 border-blue-500': $page.url === '/dashboard' }">
                Dashboard
              </a>
              <a href="/paints" 
                 class="text-gray-900 hover:text-gray-600 px-3 py-2 text-sm font-medium"
                 :class="{ 'border-b-2 border-blue-500': $page.url.startsWith('/paints') }">
                Paint Catalog
              </a>
              <a href="/projects" 
                 class="text-gray-900 hover:text-gray-600 px-3 py-2 text-sm font-medium"
                 :class="{ 'border-b-2 border-blue-500': $page.url.startsWith('/projects') }">
                Projects
              </a>
              <a href="/paint-bundles" 
                 class="text-gray-900 hover:text-gray-600 px-3 py-2 text-sm font-medium"
                 :class="{ 'border-b-2 border-blue-500': $page.url.startsWith('/paint-bundles') }">
                Paint Bundles
              </a>
            </div>
          </div>

          <!-- User Menu -->
          <div class="flex items-center space-x-4">
            <div class="relative">
              <button @click="showUserMenu = !showUserMenu" 
                      class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <span class="sr-only">Open user menu</span>
                <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                  <span class="text-sm font-medium text-gray-700">
                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                  </span>
                </div>
              </button>

              <!-- User Dropdown -->
              <div v-if="showUserMenu" 
                   class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                <div class="py-1">
                  <div class="px-4 py-2 text-sm text-gray-700 border-b">
                    {{ $page.props.auth.user.name }}
                    <div class="text-xs text-gray-500">{{ $page.props.auth.user.email }}</div>
                    <div v-if="$page.props.auth.user.role === 'admin'" class="text-xs text-blue-600 font-medium">Administrator</div>
                  </div>
                  <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                  <form method="POST" action="/logout" @submit.prevent="logout">
                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                      Sign out
                    </button>
                  </form>
                </div>
              </div>
            </div>

            <!-- Mobile menu button -->
            <button @click="showMobileMenu = !showMobileMenu" 
                    class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
              <span class="sr-only">Open main menu</span>
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path v-if="!showMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Mobile Navigation -->
        <div v-if="showMobileMenu" class="md:hidden">
          <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 border-t">
            <a href="/dashboard" class="text-gray-900 hover:text-gray-600 block px-3 py-2 text-base font-medium">
              Dashboard
            </a>
            <a href="/paints" class="text-gray-900 hover:text-gray-600 block px-3 py-2 text-base font-medium">
              Paint Catalog
            </a>
            <a href="/projects" class="text-gray-900 hover:text-gray-600 block px-3 py-2 text-base font-medium">
              Projects
            </a>
            <a href="/paint-bundles" class="text-gray-900 hover:text-gray-600 block px-3 py-2 text-base font-medium">
              Paint Bundles
            </a>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page Header -->
    <header v-if="$slots.header" class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <slot name="header" />
      </div>
    </header>

    <!-- Main Content -->
    <main>
      <slot />
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const showUserMenu = ref(false)
const showMobileMenu = ref(false)

const logout = () => {
  router.post('/logout')
}

// Close menus when clicking outside
document.addEventListener('click', (e) => {
  if (!e.target.closest('.relative')) {
    showUserMenu.value = false
  }
})
</script>