<template>
  <AppLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Paint Catalog
        </h2>
        
        <!-- Action buttons that will ALWAYS show -->
        <div class="flex flex-wrap gap-2">
          <button 
            v-if="isAdmin"
            @click="showCreateModal = true"
            class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-colors text-sm"
          >
            Add New Paint
          </button>
          
          <button 
            v-if="isAdmin"
            @click="triggerCsvImport"
            class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors text-sm"
          >
            📋 CSV Import
          </button>
          <input 
            ref="csvInput" 
            type="file" 
            accept=".csv" 
            class="hidden" 
            @change="handleCsvImport"
          >
          
          <button 
            @click="$inertia.visit('/paint-bundles')"
            class="bg-purple-500 text-white px-4 py-2 rounded-md hover:bg-purple-600 transition-colors text-sm"
          >
            🎨 Paint Bundles
          </button>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Success/Error Messages -->
        <div v-if="$page.props.flash.success" 
             class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
          {{ $page.props.flash.success }}
        </div>
        
        <div v-if="$page.props.flash.error" 
             class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
          {{ $page.props.flash.error }}
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            
            <!-- Search and Filters -->
            <div class="mb-6 space-y-4">
              <div class="flex flex-col sm:flex-row gap-4">
                <input 
                  v-model="search"
                  type="text" 
                  placeholder="Search paints by name, maker, or code..." 
                  class="flex-1 border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                <button 
                  @click="performSearch"
                  class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 transition-colors whitespace-nowrap"
                >
                  Search
                </button>
                <button 
                  v-if="hasFilters"
                  @click="clearFilters"
                  class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors whitespace-nowrap"
                >
                  Clear All
                </button>
              </div>

              <!-- Filter Row -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Manufacturer</label>
                  <select v-model="filters.manufacturer" @change="performSearch" 
                          class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Manufacturers</option>
                    <option v-for="manufacturer in manufacturers" :key="manufacturer" :value="manufacturer">
                      {{ manufacturer }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Color Family</label>
                  <select v-model="filters.color_filter" @change="performSearch"
                          class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Colors</option>
                    <option value="red">Reds</option>
                    <option value="blue">Blues</option>
                    <option value="green">Greens</option>
                    <option value="yellow">Yellows</option>
                    <option value="orange">Oranges</option>
                    <option value="purple">Purples</option>
                    <option value="pink">Pinks</option>
                    <option value="brown">Browns</option>
                    <option value="gray">Grays</option>
                    <option value="white">Whites</option>
                    <option value="black">Blacks</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Column Visibility</label>
                  <button @click="showColumnSettings = !showColumnSettings" 
                          class="w-full bg-gray-100 border border-gray-300 rounded-md px-3 py-2 text-left hover:bg-gray-200 transition-colors">
                    <span class="flex items-center justify-between">
                      <span>Customize Columns</span>
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                      </svg>
                    </span>
                  </button>
                </div>
              </div>

              <!-- Column Settings Panel -->
              <div v-if="showColumnSettings" 
                   class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-3">Show/Hide Columns</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                  <label v-for="column in columnOptions" :key="column.key" class="flex items-center">
                    <input v-model="visibleColumns[column.key]" type="checkbox" class="mr-2">
                    <span class="text-sm">{{ column.label }}</span>
                  </label>
                </div>
              </div>

              <!-- Bulk Actions -->
              <div v-if="selectedPaints.length > 0" 
                   class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                  <div class="flex flex-wrap items-center gap-4">
                    <span class="font-medium text-blue-800">
                      {{ selectedPaints.length }} paint{{ selectedPaints.length !== 1 ? 's' : '' }} selected
                    </span>
                    <button @click="addToBundle" 
                            class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition-colors text-sm">
                      🎨 Add to Bundle
                    </button>
                    <button v-if="isAdmin" @click="bulkDelete" 
                            class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors text-sm">
                      Delete Selected
                    </button>
                  </div>
                  <button @click="clearSelection" 
                          class="text-blue-600 hover:text-blue-800 transition-colors text-sm">
                    Clear Selection
                  </button>
                </div>
              </div>
            </div>

            <!-- Paints Display -->
            <div v-if="filteredPaints.length > 0">
              <!-- Desktop Table View -->
              <div class="hidden lg:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-3 py-3 text-left">
                        <input 
                          v-model="selectAll" 
                          type="checkbox" 
                          class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                      </th>
                      <th v-if="visibleColumns.color" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Color
                      </th>
                      <th v-if="visibleColumns.product" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Product Details
                      </th>
                      <th v-if="visibleColumns.specs" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Specifications
                      </th>
                      <th v-if="visibleColumns.price" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Price & Volume
                      </th>
                      <th v-if="visibleColumns.description" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Description
                      </th>
                      <th v-if="isAdmin" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="paint in filteredPaints" :key="paint.id" class="hover:bg-gray-50">
                      <td class="px-3 py-4 whitespace-nowrap">
                        <input 
                          v-model="selectedPaints" 
                          :value="paint.id" 
                          type="checkbox" 
                          class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                      </td>
                      
                      <td v-if="visibleColumns.color" class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div 
                            class="w-12 h-12 rounded-lg border border-gray-300 shadow-sm" 
                            :style="{ backgroundColor: paint.hex_color }"
                          ></div>
                          <div class="ml-3">
                            <div class="text-xs font-mono text-gray-500">{{ paint.hex_color }}</div>
                          </div>
                        </div>
                      </td>
                      
                      <td v-if="visibleColumns.product" class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ paint.product_name }}</div>
                        <div class="text-sm text-gray-500">{{ paint.maker }}</div>
                        <div class="text-xs text-gray-400">Code: {{ paint.product_code }}</div>
                      </td>
                      
                      <td v-if="visibleColumns.specs" class="px-6 py-4">
                        <div class="text-sm text-gray-900">{{ paint.form }}</div>
                      </td>
                      
                      <td v-if="visibleColumns.price" class="px-6 py-4 whitespace-nowrap">
                        <div class="text-lg font-bold text-green-600">£{{ formatPrice(paint.price_gbp) }}</div>
                        <div class="text-sm text-gray-500">
                          {{ formatVolume(paint.volume_ml) }}
                        </div>
                      </td>

                      <td v-if="visibleColumns.description" class="px-6 py-4">
                        <div class="text-sm text-gray-600 max-w-xs truncate">
                          {{ paint.color_description || 'No description' }}
                        </div>
                      </td>
                      
                      <td v-if="isAdmin" class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                          <button class="text-blue-600 hover:text-blue-900 transition-colors">
                            View
                          </button>
                          <button class="text-indigo-600 hover:text-indigo-900 transition-colors">
                            Edit
                          </button>
                          <button @click="deletePaint(paint)" 
                                  class="text-red-600 hover:text-red-900 transition-colors">
                            Delete
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Mobile Card View -->
              <div class="lg:hidden space-y-4">
                <div v-for="paint in filteredPaints" :key="paint.id" 
                     class="border border-gray-200 rounded-lg p-4 bg-white shadow-sm">
                  <div class="flex items-start space-x-4">
                    <input 
                      v-model="selectedPaints" 
                      :value="paint.id" 
                      type="checkbox" 
                      class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    
                    <div 
                      class="w-16 h-16 rounded-lg border border-gray-300 flex-shrink-0" 
                      :style="{ backgroundColor: paint.hex_color }"
                    ></div>
                    
                    <div class="flex-1 min-w-0">
                      <h3 class="text-lg font-semibold text-gray-900 truncate">{{ paint.product_name }}</h3>
                      <p class="text-gray-600">{{ paint.maker }}</p>
                      <p class="text-sm text-gray-500">Code: {{ paint.product_code }}</p>
                      <p class="text-sm text-gray-500">{{ paint.form }}</p>
                      
                      <div class="mt-2 flex justify-between items-center">
                        <span class="text-lg font-bold text-green-600">£{{ formatPrice(paint.price_gbp) }}</span>
                        <span class="text-sm text-gray-500">{{ formatVolume(paint.volume_ml) }}</span>
                      </div>
                      
                      <div class="text-xs text-gray-400 mt-1">{{ paint.hex_color }}</div>
                    </div>
                  </div>
                  
                  <div v-if="isAdmin" class="mt-4 pt-4 border-t border-gray-200 flex space-x-4">
                    <button class="text-blue-600 hover:text-blue-900 text-sm font-medium">View</button>
                    <button class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</button>
                    <button @click="deletePaint(paint)" 
                            class="text-red-600 hover:text-red-900 text-sm font-medium">Delete</button>
                  </div>
                </div>
              </div>

              <div class="mt-6 text-center text-sm text-gray-500">
                Showing {{ filteredPaints.length }} paints
              </div>

            </div>
            
            <!-- Empty State -->
            <div v-else class="text-center py-12">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-8 0 4 4 0 018 0zM7 21h10a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No paints found</h3>
              <p v-if="hasFilters" class="mt-1 text-sm text-gray-500">No paints match your search criteria.</p>
              <p v-else class="mt-1 text-sm text-gray-500">Get started by adding your first paint to the catalog.</p>
              
              <div class="mt-6">
                <button v-if="hasFilters" @click="clearFilters" 
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700">
                  View All Paints
                </button>
                <button v-else-if="isAdmin" @click="showCreateModal = true" 
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                  Add First Paint
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'

// Props from Laravel controller
const props = defineProps({
  paints: Array,
  auth: Object,
})

// Reactive data
const search = ref('')
const filters = ref({
  manufacturer: '',
  color_filter: ''
})
const selectedPaints = ref([])
const showColumnSettings = ref(false)
const showCreateModal = ref(false)
const csvInput = ref(null)

// Column visibility
const visibleColumns = ref({
  color: true,
  product: true,
  specs: true,
  price: true,
  description: false,
  cmyk: false,
  rgb: false
})

const columnOptions = [
  { key: 'color', label: 'Color Swatch' },
  { key: 'product', label: 'Product Details' },
  { key: 'specs', label: 'Specifications' },
  { key: 'price', label: 'Price & Volume' },
  { key: 'description', label: 'Description' },
  { key: 'cmyk', label: 'CMYK Values' },
  { key: 'rgb', label: 'RGB Values' }
]

// Computed properties
const isAdmin = computed(() => {
  return props.auth?.user?.role === 'admin'
})

const manufacturers = computed(() => {
  const allManufacturers = props.paints.map(paint => paint.maker)
  return [...new Set(allManufacturers)].sort()
})

const filteredPaints = computed(() => {
  let filtered = props.paints

  if (search.value) {
    const searchLower = search.value.toLowerCase()
    filtered = filtered.filter(paint => 
      paint.product_name.toLowerCase().includes(searchLower) ||
      paint.maker.toLowerCase().includes(searchLower) ||
      paint.product_code.toLowerCase().includes(searchLower)
    )
  }

  if (filters.value.manufacturer) {
    filtered = filtered.filter(paint => paint.maker === filters.value.manufacturer)
  }

  if (filters.value.color_filter) {
    filtered = filtered.filter(paint => {
      const colorFamily = getColorFamily(paint.hex_color)
      return colorFamily === filters.value.color_filter
    })
  }

  return filtered
})

const hasFilters = computed(() => {
  return search.value || filters.value.manufacturer || filters.value.color_filter
})

const selectAll = computed({
  get: () => selectedPaints.value.length === filteredPaints.value.length && filteredPaints.value.length > 0,
  set: (value) => {
    if (value) {
      selectedPaints.value = filteredPaints.value.map(paint => paint.id)
    } else {
      selectedPaints.value = []
    }
  }
})

// Methods
const formatPrice = (price) => {
  return Number(price).toFixed(2)
}

const formatVolume = (volumeMl) => {
  return volumeMl >= 1000 ? `${volumeMl / 1000}L` : `${volumeMl}ml`
}

const getColorFamily = (hexColor) => {
  const hex = hexColor.replace('#', '')
  const r = parseInt(hex.substr(0, 2), 16)
  const g = parseInt(hex.substr(2, 2), 16)
  const b = parseInt(hex.substr(4, 2), 16)
  
  const max = Math.max(r, g, b)
  const min = Math.min(r, g, b)
  
  if (max - min < 50) {
    if (max < 100) return 'black'
    if (min > 200) return 'white'
    return 'gray'
  }
  
  if (r === max) {
    if (g > b) return g > 150 ? 'yellow' : 'red'
    return r > 200 && b > 150 ? 'pink' : 'red'
  }
  if (g === max) return b > r ? 'blue' : 'green'
  if (b === max) return r > g ? 'purple' : 'blue'
  
  return 'other'
}

const performSearch = () => {
  router.get('/paints', {
    search: search.value,
    manufacturer: filters.value.manufacturer,
    color_filter: filters.value.color_filter
  }, {
    preserveState: true,
    replace: true
  })
}

const clearFilters = () => {
  search.value = ''
  filters.value.manufacturer = ''
  filters.value.color_filter = ''
  router.get('/paints', {}, { replace: true })
}

const clearSelection = () => {
  selectedPaints.value = []
}

const triggerCsvImport = () => {
  csvInput.value.click()
}

const handleCsvImport = async (event) => {
  const file = event.target.files[0]
  if (!file) return

  if (!file.name.toLowerCase().endsWith('.csv')) {
    alert('Please select a valid CSV file.')
    return
  }

  const formData = new FormData()
  formData.append('csv_file', file)

  try {
    await router.post('/paints/csv-import', formData, {
      onSuccess: () => {
        alert('CSV imported successfully!')
      },
      onError: () => {
        alert('CSV import failed. Please check your file format.')
      },
      onFinish: () => {
        event.target.value = ''
      }
    })
  } catch (error) {
    alert('Import failed: ' + error.message)
  }
}

const addToBundle = () => {
  if (selectedPaints.value.length === 0) {
    alert('Please select at least one paint to add to bundle.')
    return
  }

  const bundleName = prompt(`Create new paint bundle with ${selectedPaints.value.length} paint${selectedPaints.value.length !== 1 ? 's' : ''}.

Bundle name:`, 'New Paint Bundle')
  
  if (!bundleName) return

  router.post('/paint-bundles/add-to-bundle', {
    paint_ids: selectedPaints.value,
    bundle_name: bundleName
  })
}

const bulkDelete = () => {
  if (selectedPaints.value.length === 0) {
    alert('Please select at least one paint to delete.')
    return
  }

  const count = selectedPaints.value.length
  if (!confirm(`Are you sure you want to delete ${count} selected paint${count !== 1 ? 's' : ''}? This action cannot be undone.`)) {
    return
  }

  router.post('/paints/bulk-delete', {
    paint_ids: selectedPaints.value
  })
}

const deletePaint = (paint) => {
  if (!confirm(`Are you sure you want to delete "${paint.product_name}"?`)) return
  
  router.delete(`/paints/${paint.id}`)
}
</script>