'use client'

import { useState, useEffect } from 'react'
import { useAuth } from '@/contexts/AuthContext'
import { useBasket } from '@/contexts/BasketContext'
import Navigation from '@/components/Navigation'
import { useRouter } from 'next/navigation'

export default function Basket() {
  const { user, token } = useAuth()
  const { basketItems, basketTotal, basketCount, updateQuantity, removeFromBasket, clearBasket } = useBasket()
  const router = useRouter()
  const [bundleName, setBundleName] = useState('')
  const [bundleDescription, setBundleDescription] = useState('')
  const [showSaveBundleForm, setShowSaveBundleForm] = useState(false)
  const [savingBundle, setSavingBundle] = useState(false)

  useEffect(() => {
    if (!user) {
      router.push('/login')
      return
    }
  }, [user, router])

  const handleQuantityChange = (paintId: number, newQuantity: number) => {
    updateQuantity(paintId, newQuantity)
  }

  const handleSaveAsBundle = async () => {
    if (!bundleName.trim()) {
      alert('Please enter a bundle name')
      return
    }

    if (basketItems.length === 0) {
      alert('Cannot save an empty basket as a bundle')
      return
    }

    setSavingBundle(true)
    try {
      const bundleData = {
        name: bundleName,
        description: bundleDescription,
        items: basketItems.map(item => ({
          paint_id: item.paint.id,
          quantity: item.quantity,
          price_per_unit: item.paint.price_gbp,
        }))
      }

      const response = await fetch('/api/paint-bundles', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(bundleData),
      })

      if (!response.ok) {
        throw new Error('Failed to save bundle')
      }

      // Clear the basket and form
      clearBasket()
      setBundleName('')
      setBundleDescription('')
      setShowSaveBundleForm(false)
      
      alert('Bundle saved successfully!')
      router.push('/paint-bundles')
    } catch (error) {
      console.error('Error saving bundle:', error)
      alert('Failed to save bundle. Please try again.')
    } finally {
      setSavingBundle(false)
    }
  }

  if (!user) return null

  if (basketItems.length === 0) {
    return (
      <div className="min-h-screen bg-gray-50">
        <Navigation />
        <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <div className="text-center py-12">
            <div className="w-24 h-24 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
              <svg className="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l-1 12H6L5 9z" />
              </svg>
            </div>
            <h2 className="text-2xl font-semibold text-gray-900 mb-2">Your basket is empty</h2>
            <p className="text-gray-600 mb-6">Add some paints from the catalog to get started!</p>
            <button
              onClick={() => router.push('/paints')}
              className="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700"
            >
              Browse Paint Catalog
            </button>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <Navigation />
      
      <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div className="mb-6">
          <h1 className="text-3xl font-bold text-gray-900">Your Basket</h1>
          <p className="text-gray-600">
            {basketCount} item{basketCount !== 1 ? 's' : ''} • Total: £{basketTotal.toFixed(2)}
          </p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Basket Items */}
          <div className="lg:col-span-2 space-y-4">
            {basketItems.map((item) => (
              <div key={item.paint.id} className="bg-white rounded-lg shadow p-6">
                <div className="flex items-start space-x-4">
                  {/* Paint Color Preview */}
                  <div 
                    className="w-16 h-16 rounded-lg flex-shrink-0" 
                    style={{ backgroundColor: item.paint.hex_color }}
                  />
                  
                  {/* Paint Details */}
                  <div className="flex-1 min-w-0">
                    <h3 className="text-lg font-medium text-gray-900 truncate">
                      {item.paint.product_name}
                    </h3>
                    <p className="text-sm text-gray-500">
                      {item.paint.maker} • {item.paint.product_code}
                    </p>
                    <p className="text-sm text-gray-500">
                      {item.paint.volume_ml}ml • £{item.paint.price_gbp.toFixed(2)} each
                    </p>
                  </div>

                  {/* Quantity Controls */}
                  <div className="flex items-center space-x-3">
                    <button
                      onClick={() => handleQuantityChange(item.paint.id, item.quantity - 1)}
                      className="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50"
                    >
                      -
                    </button>
                    <span className="w-12 text-center font-medium">{item.quantity}</span>
                    <button
                      onClick={() => handleQuantityChange(item.paint.id, item.quantity + 1)}
                      className="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50"
                    >
                      +
                    </button>
                  </div>

                  {/* Item Total */}
                  <div className="text-right">
                    <p className="text-lg font-medium text-gray-900">
                      £{(item.paint.price_gbp * item.quantity).toFixed(2)}
                    </p>
                    <button
                      onClick={() => removeFromBasket(item.paint.id)}
                      className="text-sm text-red-600 hover:text-red-700 mt-1"
                    >
                      Remove
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>

          {/* Basket Summary and Actions */}
          <div className="space-y-4">
            {/* Basket Total */}
            <div className="bg-white rounded-lg shadow p-6">
              <h2 className="text-lg font-medium text-gray-900 mb-4">Order Summary</h2>
              <div className="space-y-2">
                <div className="flex justify-between text-sm">
                  <span className="text-gray-600">Subtotal ({basketCount} items)</span>
                  <span>£{basketTotal.toFixed(2)}</span>
                </div>
                <div className="border-t pt-2">
                  <div className="flex justify-between font-medium">
                    <span>Total</span>
                    <span>£{basketTotal.toFixed(2)}</span>
                  </div>
                </div>
              </div>
            </div>

            {/* Actions */}
            <div className="space-y-3">
              <button
                onClick={() => setShowSaveBundleForm(true)}
                className="w-full bg-blue-600 text-white py-3 rounded-md hover:bg-blue-700"
              >
                Save as Paint Bundle
              </button>
              
              <button
                onClick={clearBasket}
                className="w-full bg-gray-300 text-gray-700 py-3 rounded-md hover:bg-gray-400"
              >
                Clear Basket
              </button>

              <button
                onClick={() => router.push('/paints')}
                className="w-full border border-blue-600 text-blue-600 py-3 rounded-md hover:bg-blue-50"
              >
                Continue Shopping
              </button>
            </div>
          </div>
        </div>

        {/* Save as Bundle Form */}
        {showSaveBundleForm && (
          <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div className="bg-white rounded-lg max-w-md w-full p-6">
              <h2 className="text-xl font-semibold mb-4">Save as Paint Bundle</h2>
              
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Bundle Name *
                  </label>
                  <input
                    type="text"
                    value={bundleName}
                    onChange={(e) => setBundleName(e.target.value)}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter bundle name..."
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Description
                  </label>
                  <textarea
                    value={bundleDescription}
                    onChange={(e) => setBundleDescription(e.target.value)}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    rows={3}
                    placeholder="Optional description..."
                  />
                </div>

                <div className="bg-gray-50 p-3 rounded-md">
                  <p className="text-sm text-gray-600 mb-1">Bundle contains:</p>
                  <p className="text-sm font-medium">{basketCount} paints • £{basketTotal.toFixed(2)} total</p>
                </div>
              </div>

              <div className="flex space-x-3 mt-6">
                <button
                  onClick={handleSaveAsBundle}
                  disabled={savingBundle}
                  className="flex-1 bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
                >
                  {savingBundle ? 'Saving...' : 'Save Bundle'}
                </button>
                <button
                  onClick={() => setShowSaveBundleForm(false)}
                  className="flex-1 bg-gray-300 text-gray-700 py-2 rounded-md hover:bg-gray-400"
                >
                  Cancel
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  )
}