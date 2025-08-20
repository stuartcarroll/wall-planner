<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg">
      <h1 class="text-2xl font-bold text-green-600 mb-4">✅ Vue/Inertia Test Page</h1>
      <p class="text-gray-700">{{ message }}</p>
      <div class="mt-4">
        <a href="/login" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
          Go to Login
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  message: String
})
</script>