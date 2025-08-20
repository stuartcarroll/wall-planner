'use client'

import { useState } from 'react'
import { useAuth } from '@/contexts/AuthContext'

interface ImportError {
  row: number
  errors: string[]
}

interface ImportDuplicate {
  row: number
  product_code: string
  maker: string
  message: string
}

interface ImportResult {
  message: string
  imported: number
  total_rows: number
  errors: ImportError[]
  duplicates: ImportDuplicate[]
}

interface PaintCsvImportProps {
  onImportComplete: () => void
}

export default function PaintCsvImport({ onImportComplete }: PaintCsvImportProps) {
  const [file, setFile] = useState<File | null>(null)
  const [importing, setImporting] = useState(false)
  const [result, setResult] = useState<ImportResult | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [showImportForm, setShowImportForm] = useState(false)
  const { token } = useAuth()

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const selectedFile = e.target.files?.[0]
    if (selectedFile) {
      if (!selectedFile.name.toLowerCase().endsWith('.csv')) {
        setError('Please select a CSV file')
        return
      }
      if (selectedFile.size > 10 * 1024 * 1024) { // 10MB
        setError('File size must be less than 10MB')
        return
      }
      setFile(selectedFile)
      setError(null)
    }
  }

  const handleImport = async () => {
    if (!file || !token) return

    setImporting(true)
    setError(null)
    setResult(null)

    const formData = new FormData()
    formData.append('file', file)

    try {
      const response = await fetch('/api/paints/import', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
        body: formData,
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.error || data.message || 'Import failed')
      }

      setResult(data)
      
      if (data.imported > 0) {
        onImportComplete()
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Import failed')
    } finally {
      setImporting(false)
    }
  }

  const resetForm = () => {
    setFile(null)
    setResult(null)
    setError(null)
    setShowImportForm(false)
  }

  const downloadSampleCsv = () => {
    const sampleData = [
      'product_name,product_code,maker,form,hex_color,price_gbp,volume_ml,color_description,cmyk_c,cmyk_m,cmyk_y,cmyk_k,rgb_r,rgb_g,rgb_b',
      'Heritage Red,FB-294,Farrow & Ball,estate emulsion,#B91927,89.00,2500,A deep sophisticated red inspired by traditional English heritage colors,20,95,85,10,185,25,39',
      'Duck Egg Blue,FB-203,Farrow & Ball,modern emulsion,#9EB8D0,89.00,2500,A timeless blue-green that brings serenity to any space,35,15,0,15,158,184,208',
      'Elephants Breath,FB-229,Farrow & Ball,estate emulsion,#9C8A7D,89.00,2500,A sophisticated neutral that works beautifully in any setting,35,40,50,5,156,138,125'
    ].join('\n')

    const blob = new Blob([sampleData], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'paint_sample.csv'
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)
  }

  return (
    <div className="bg-white p-4 rounded-lg shadow mb-6">
      <div className="flex justify-between items-center mb-4">
        <h2 className="text-lg font-semibold text-gray-900">CSV Import</h2>
        <div className="flex space-x-2">
          <button
            onClick={downloadSampleCsv}
            className="text-sm text-blue-600 hover:text-blue-800"
          >
            Download Sample CSV
          </button>
          <button
            onClick={() => setShowImportForm(!showImportForm)}
            className="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700"
          >
            {showImportForm ? 'Cancel' : 'Import CSV'}
          </button>
        </div>
      </div>

      {showImportForm && (
        <div className="border-t pt-4">
          <div className="mb-4">
            <p className="text-sm text-gray-600 mb-2">
              Upload a CSV file with paint data. Required columns: product_name, product_code, maker, form, hex_color, price_gbp, volume_ml, color_description
            </p>
            <p className="text-xs text-gray-500 mb-4">
              Optional columns: cmyk_c, cmyk_m, cmyk_y, cmyk_k, rgb_r, rgb_g, rgb_b (will be calculated from hex_color if not provided)
            </p>
          </div>

          <div className="mb-4">
            <input
              type="file"
              accept=".csv"
              onChange={handleFileChange}
              className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
              disabled={importing}
            />
          </div>

          {file && (
            <div className="mb-4 p-3 bg-gray-50 rounded">
              <p className="text-sm text-gray-700">
                <strong>Selected file:</strong> {file.name} ({(file.size / 1024).toFixed(1)} KB)
              </p>
            </div>
          )}

          {error && (
            <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded">
              <p className="text-sm text-red-700">{error}</p>
            </div>
          )}

          {result && (
            <div className="mb-4 p-4 bg-green-50 border border-green-200 rounded">
              <h3 className="font-medium text-green-800 mb-2">Import Results</h3>
              <div className="text-sm text-green-700 space-y-1">
                <p><strong>Successfully imported:</strong> {result.imported} paints</p>
                <p><strong>Total rows processed:</strong> {result.total_rows}</p>
                
                {result.errors.length > 0 && (
                  <div className="mt-3">
                    <p className="font-medium text-red-700">Errors ({result.errors.length}):</p>
                    <div className="max-h-32 overflow-y-auto">
                      {result.errors.map((error, index) => (
                        <div key={index} className="text-xs text-red-600 mt-1">
                          Row {error.row}: {error.errors.join(', ')}
                        </div>
                      ))}
                    </div>
                  </div>
                )}
                
                {result.duplicates.length > 0 && (
                  <div className="mt-3">
                    <p className="font-medium text-yellow-700">Duplicates skipped ({result.duplicates.length}):</p>
                    <div className="max-h-32 overflow-y-auto">
                      {result.duplicates.map((duplicate, index) => (
                        <div key={index} className="text-xs text-yellow-600 mt-1">
                          Row {duplicate.row}: {duplicate.product_code} ({duplicate.maker}) - {duplicate.message}
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            </div>
          )}

          <div className="flex space-x-4">
            <button
              onClick={handleImport}
              disabled={!file || importing}
              className="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              {importing ? 'Importing...' : 'Start Import'}
            </button>
            <button
              onClick={resetForm}
              disabled={importing}
              className="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-400 disabled:cursor-not-allowed"
            >
              Reset
            </button>
          </div>
        </div>
      )}
    </div>
  )
}