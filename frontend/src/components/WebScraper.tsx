'use client'

import { useState } from 'react'
import { useAuth } from '@/contexts/AuthContext'

interface ScrapeResult {
  message: string
  urls?: string[]
  count?: number
  data?: any
  total_urls?: number
  successful_scrapes?: number
  saved_to_database?: number
  errors?: Array<{
    url: string
    error: string
  }>
}

interface WebScraperProps {
  onScrapingComplete?: () => void
}

export default function WebScraper({ onScrapingComplete }: WebScraperProps) {
  const [url, setUrl] = useState('https://loopcolors.com/product_category/spray-cans/')
  const [step, setStep] = useState<'input' | 'list' | 'scraping'>('input')
  const [loading, setLoading] = useState(false)
  const [result, setResult] = useState<ScrapeResult | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [productUrls, setProductUrls] = useState<string[]>([])
  const [selectedUrls, setSelectedUrls] = useState<string[]>([])
  const [saveToDatabase, setSaveToDatabase] = useState(true)
  const [scrapingProgress, setScrapingProgress] = useState(0)
  const { token, isAdmin } = useAuth()

  if (!isAdmin) {
    return (
      <div className="bg-red-50 border border-red-200 rounded-lg p-4">
        <p className="text-red-700">Admin access required for web scraping functionality.</p>
      </div>
    )
  }

  const handleExtractUrls = async () => {
    if (!token) return

    setLoading(true)
    setError(null)
    setResult(null)

    try {
      const response = await fetch('/api/scraping/product-list', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ url }),
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.error || data.message || 'Failed to extract URLs')
      }

      setResult(data)
      setProductUrls(data.urls || [])
      setSelectedUrls(data.urls || [])
      setStep('list')
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to extract URLs')
    } finally {
      setLoading(false)
    }
  }

  const handleScrapeSelected = async () => {
    if (!token || selectedUrls.length === 0) return

    setLoading(true)
    setError(null)
    setResult(null)
    setStep('scraping')
    setScrapingProgress(0)

    try {
      const response = await fetch('/api/scraping/batch', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
          urls: selectedUrls,
          save_to_database: saveToDatabase 
        }),
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.error || data.message || 'Batch scraping failed')
      }

      setResult(data)
      
      if (data.saved_to_database && data.saved_to_database > 0 && onScrapingComplete) {
        onScrapingComplete()
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Batch scraping failed')
    } finally {
      setLoading(false)
      setScrapingProgress(100)
    }
  }

  const handleUrlToggle = (urlToToggle: string) => {
    setSelectedUrls(prev => 
      prev.includes(urlToToggle)
        ? prev.filter(u => u !== urlToToggle)
        : [...prev, urlToToggle]
    )
  }

  const selectAll = () => {
    setSelectedUrls([...productUrls])
  }

  const selectNone = () => {
    setSelectedUrls([])
  }

  const resetScraper = () => {
    setStep('input')
    setResult(null)
    setError(null)
    setProductUrls([])
    setSelectedUrls([])
    setScrapingProgress(0)
  }

  const getUrlDisplayName = (url: string) => {
    const parts = url.split('/')
    const slug = parts[parts.length - 2] || parts[parts.length - 1]
    return slug.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
  }

  return (
    <div className="bg-white p-6 rounded-lg shadow">
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-xl font-bold text-gray-900">Web Scraper</h2>
        <div className="flex space-x-2">
          {step !== 'input' && (
            <button
              onClick={resetScraper}
              className="text-sm text-gray-600 hover:text-gray-800"
              disabled={loading}
            >
              Reset
            </button>
          )}
        </div>
      </div>

      {/* Step 1: URL Input */}
      {step === 'input' && (
        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Website URL (Product Category Page)
            </label>
            <input
              type="url"
              value={url}
              onChange={(e) => setUrl(e.target.value)}
              placeholder="https://loopcolors.com/product_category/spray-cans/"
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              disabled={loading}
            />
            <p className="text-xs text-gray-500 mt-1">
              Enter a category page URL to extract product links
            </p>
          </div>

          <button
            onClick={handleExtractUrls}
            disabled={!url || loading}
            className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
          >
            {loading ? 'Extracting URLs...' : 'Extract Product URLs'}
          </button>
        </div>
      )}

      {/* Step 2: URL Selection */}
      {step === 'list' && (
        <div className="space-y-4">
          <div className="flex justify-between items-center">
            <h3 className="text-lg font-medium text-gray-900">
              Found {productUrls.length} Product URLs
            </h3>
            <div className="flex space-x-2">
              <button
                onClick={selectAll}
                className="text-sm text-blue-600 hover:text-blue-800"
              >
                Select All
              </button>
              <button
                onClick={selectNone}
                className="text-sm text-blue-600 hover:text-blue-800"
              >
                Select None
              </button>
            </div>
          </div>

          <div className="max-h-64 overflow-y-auto border border-gray-200 rounded">
            {productUrls.map((productUrl, index) => (
              <div key={index} className="flex items-center p-2 border-b border-gray-100 last:border-b-0">
                <input
                  type="checkbox"
                  checked={selectedUrls.includes(productUrl)}
                  onChange={() => handleUrlToggle(productUrl)}
                  className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mr-3"
                />
                <div className="flex-1">
                  <div className="text-sm font-medium text-gray-900">
                    {getUrlDisplayName(productUrl)}
                  </div>
                  <div className="text-xs text-gray-500 truncate">
                    {productUrl}
                  </div>
                </div>
              </div>
            ))}
          </div>

          <div className="flex items-center space-x-4">
            <label className="flex items-center">
              <input
                type="checkbox"
                checked={saveToDatabase}
                onChange={(e) => setSaveToDatabase(e.target.checked)}
                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mr-2"
              />
              <span className="text-sm text-gray-700">Save to database</span>
            </label>
          </div>

          <div className="flex space-x-4">
            <button
              onClick={handleScrapeSelected}
              disabled={selectedUrls.length === 0 || loading}
              className="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              {loading ? 'Scraping...' : `Scrape ${selectedUrls.length} Products`}
            </button>
            <button
              onClick={resetScraper}
              className="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400"
            >
              Cancel
            </button>
          </div>
        </div>
      )}

      {/* Step 3: Scraping Progress and Results */}
      {step === 'scraping' && (
        <div className="space-y-4">
          <div>
            <h3 className="text-lg font-medium text-gray-900 mb-2">Scraping Progress</h3>
            {loading && (
              <div className="w-full bg-gray-200 rounded-full h-2">
                <div 
                  className="bg-blue-600 h-2 rounded-full transition-all duration-500"
                  style={{ width: '50%' }}
                ></div>
              </div>
            )}
          </div>

          {result && (
            <div className="bg-green-50 border border-green-200 rounded-lg p-4">
              <h3 className="font-medium text-green-800 mb-2">Scraping Results</h3>
              <div className="text-sm text-green-700 space-y-1">
                <p><strong>Total URLs processed:</strong> {result.total_urls}</p>
                <p><strong>Successful scrapes:</strong> {result.successful_scrapes}</p>
                {result.saved_to_database !== undefined && (
                  <p><strong>Saved to database:</strong> {result.saved_to_database}</p>
                )}
                
                {result.errors && result.errors.length > 0 && (
                  <div className="mt-3">
                    <p className="font-medium text-red-700">Errors ({result.errors.length}):</p>
                    <div className="max-h-32 overflow-y-auto">
                      {result.errors.map((error, index) => (
                        <div key={index} className="text-xs text-red-600 mt-1">
                          {getUrlDisplayName(error.url)}: {error.error}
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            </div>
          )}
        </div>
      )}

      {error && (
        <div className="mt-4 p-3 bg-red-50 border border-red-200 rounded">
          <p className="text-sm text-red-700">{error}</p>
        </div>
      )}
    </div>
  )
}