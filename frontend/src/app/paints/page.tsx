'use client'

import { useState, useEffect } from 'react'
import { useAuth } from '@/contexts/AuthContext'
import { useBasket } from '@/contexts/BasketContext'
import { useRouter } from 'next/navigation'
import Navigation from '@/components/Navigation'
import PaintCsvImport from '@/components/PaintCsvImport'
import WebScraper from '@/components/WebScraper'

interface Paint {
  id: number
  product_name: string
  maker: string
  product_code: string
  form: string
  hex_color: string
  price_gbp: number
  volume_ml: number
  color_description: string
  cmyk_c?: number
  cmyk_m?: number
  cmyk_y?: number
  cmyk_k?: number
  rgb_r?: number
  rgb_g?: number
  rgb_b?: number
  created_at?: string
  updated_at?: string
}

interface PaintResponse {
  data: Paint[]
  manufacturers: string[]
}

export default function Paints() {
  const [paints, setPaints] = useState<Paint[]>([])
  const [manufacturers, setManufacturers] = useState<string[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [searchTerm, setSearchTerm] = useState('')
  const [selectedManufacturer, setSelectedManufacturer] = useState('')
  const [quantities, setQuantities] = useState<{[key: number]: number}>({})
  const { user } = useAuth()
  const { addToBasket, getItemQuantity, basketItems, getTotalCost } = useBasket()
  const router = useRouter()

  useEffect(() => {
    if (!user) {
      router.push('/login')
      return
    }
    fetchPaints()
  }, [searchTerm, selectedManufacturer, user, router])

  const fetchPaints = async () => {
    try {
      setLoading(true)
      const params = new URLSearchParams()
      if (searchTerm) params.append('search', searchTerm)
      if (selectedManufacturer) params.append('manufacturer', selectedManufacturer)
      
      const response = await fetch(`/api/paints?${params}`)
      if (!response.ok) throw new Error('Failed to fetch paints')
      
      const data: PaintResponse = await response.json()
      setPaints(data.data)
      setManufacturers(data.manufacturers)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'An error occurred')
    } finally {
      setLoading(false)
    }
  }

  const handleQuantityChange = (paintId: number, quantity: number) => {
    setQuantities(prev => ({
      ...prev,
      [paintId]: Math.max(0, quantity)
    }))
  }

  const handleAddToBasket = (paint: Paint) => {
    const quantity = quantities[paint.id] || 1
    if (quantity > 0) {
      addToBasket(paint, quantity)
      // Reset quantity after adding
      setQuantities(prev => ({
        ...prev,
        [paint.id]: 0
      }))
    }
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-lg">Loading paints...</div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-red-600">Error: {error}</div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <Navigation />
      <div className="max-w-full mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div className="mb-6">
          <h1 className="text-3xl font-bold text-gray-900 mb-4">Paint Catalog</h1>
          
          {/* Search and Filter Controls */}
          <div className="bg-white p-4 rounded-lg shadow mb-6">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Search Paints
                </label>
                <input
                  type="text"
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  placeholder="Search by name, maker, or product code..."
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Filter by Manufacturer
                </label>
                <select
                  value={selectedManufacturer}
                  onChange={(e) => setSelectedManufacturer(e.target.value)}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">All Manufacturers</option>
                  {manufacturers.map((manufacturer) => (
                    <option key={manufacturer} value={manufacturer}>
                      {manufacturer}
                    </option>
                  ))}
                </select>
              </div>
              <div className="flex items-end">
                <div className="text-sm text-gray-600">
                  <span className="font-medium">{paints.length}</span> paints found
                </div>
              </div>
            </div>
          </div>
          
          {/* CSV Import Section */}
          <PaintCsvImport onImportComplete={fetchPaints} />
          
          {/* Web Scraper Section (Admin only) */}
          <WebScraper onScrapingComplete={fetchPaints} />
        </div>

        <div className="flex gap-6">
          {/* Paint Table - Left Column */}
          <div className="flex-1">
            <div className="bg-white rounded-lg shadow overflow-hidden">
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">
                        Color
                      </th>
                      <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Paint Name
                      </th>
                      <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Maker
                      </th>
                      <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Code
                      </th>
                      <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Form
                      </th>
                      <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Size
                      </th>
                      <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Price
                      </th>
                      <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Qty
                      </th>
                      <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Action
                      </th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {paints.map((paint) => (
                      <tr key={paint.id} className="hover:bg-gray-50">
                        <td className="px-3 py-4">
                          <div 
                            className="w-8 h-8 rounded border border-gray-300" 
                            style={{ backgroundColor: paint.hex_color }}
                            title={paint.hex_color}
                          />
                        </td>
                        <td className="px-4 py-4">
                          <div className="text-sm font-medium text-gray-900">
                            {paint.product_name}
                          </div>
                          {paint.color_description && (
                            <div className="text-xs text-gray-500 mt-1">
                              {paint.color_description}
                            </div>
                          )}
                        </td>
                        <td className="px-4 py-4 text-sm text-gray-900">
                          {paint.maker}
                        </td>
                        <td className="px-4 py-4 text-sm font-mono text-gray-700">
                          {paint.product_code}
                        </td>
                        <td className="px-4 py-4 text-sm text-gray-600">
                          {paint.form}
                        </td>
                        <td className="px-4 py-4 text-sm text-gray-600">
                          {paint.volume_ml}ml
                        </td>
                        <td className="px-4 py-4 text-sm font-semibold text-green-600">
                          £{Number(paint.price_gbp).toFixed(2)}
                        </td>
                        <td className="px-4 py-4">
                          <input
                            type="number"
                            min="0"
                            max="99"
                            value={quantities[paint.id] || ''}
                            onChange={(e) => handleQuantityChange(paint.id, parseInt(e.target.value) || 0)}
                            placeholder="1"
                            className="w-16 border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                          />
                        </td>
                        <td className="px-4 py-4">
                          <div className="flex flex-col gap-1">
                            <button
                              onClick={() => handleAddToBasket(paint)}
                              disabled={(quantities[paint.id] || 1) <= 0}
                              className="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
                            >
                              Add
                            </button>
                            {getItemQuantity(paint.id) > 0 && (
                              <div className="text-xs text-gray-600">
                                In basket: {getItemQuantity(paint.id)}
                              </div>
                            )}
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>

              {paints.length === 0 && (
                <div className="text-center py-12">
                  <p className="text-gray-500">No paints found matching your criteria.</p>
                </div>
              )}
            </div>
          </div>

          {/* Basket Sidebar - Right Column */}
          <div className="w-80">
            <div className="bg-white rounded-lg shadow p-6 sticky top-6">
              <h2 className="text-lg font-semibold text-gray-900 mb-4">
                Current Basket
                {basketItems.length > 0 && (
                  <span className="ml-2 bg-blue-100 text-blue-800 text-sm px-2 py-1 rounded-full">
                    {basketItems.length} item{basketItems.length !== 1 ? 's' : ''}
                  </span>
                )}
              </h2>

              {basketItems.length === 0 ? (
                <div className="text-gray-500 text-sm">
                  Your basket is empty. Add paints from the catalog to get started.
                </div>
              ) : (
                <div className="space-y-3">
                  <div className="max-h-96 overflow-y-auto">
                    {basketItems.map((item) => (
                      <div key={item.paint.id} className="flex items-center gap-3 p-3 bg-gray-50 rounded">
                        <div 
                          className="w-6 h-6 rounded border border-gray-300 flex-shrink-0" 
                          style={{ backgroundColor: item.paint.hex_color }}
                        />
                        <div className="flex-1 min-w-0">
                          <div className="text-sm font-medium text-gray-900 truncate">
                            {item.paint.product_name}
                          </div>
                          <div className="text-xs text-gray-500">
                            {item.paint.maker} • £{Number(item.paint.price_gbp).toFixed(2)}
                          </div>
                        </div>
                        <div className="text-sm font-semibold text-gray-900">
                          {item.quantity}x
                        </div>
                      </div>
                    ))}
                  </div>

                  <div className="border-t pt-3">
                    <div className="flex justify-between items-center text-lg font-semibold">
                      <span>Total:</span>
                      <span className="text-green-600">£{getTotalCost().toFixed(2)}</span>
                    </div>
                  </div>

                  <div className="space-y-2">
                    <button
                      onClick={() => router.push('/basket')}
                      className="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition-colors"
                    >
                      View Full Basket
                    </button>
                    <button
                      onClick={() => router.push('/basket')}
                      className="w-full bg-green-600 text-white py-2 rounded-md hover:bg-green-700 transition-colors"
                    >
                      Save as Bundle
                    </button>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}