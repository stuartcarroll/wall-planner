'use client'

import { useEffect, useState } from 'react'

export default function TestApi() {
  const [testResult, setTestResult] = useState<string>('')
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    async function testApi() {
      try {
        console.log('Testing API connection...')
        
        // Test basic API endpoint
        const testResponse = await fetch('http://localhost:8000/api/test')
        const testData = await testResponse.json()
        console.log('Test API response:', testData)
        
        // Test paints API endpoint
        const paintsResponse = await fetch('http://localhost:8000/api/paints')
        const paintsData = await paintsResponse.json()
        console.log('Paints API response:', paintsData)
        
        setTestResult(`✅ API Working! Test: ${JSON.stringify(testData)}, Paints count: ${paintsData.data.length}`)
      } catch (error) {
        console.error('API Test failed:', error)
        setTestResult(`❌ API Failed: ${error}`)
      } finally {
        setLoading(false)
      }
    }
    
    testApi()
  }, [])

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-4xl mx-auto">
        <h1 className="text-3xl font-bold mb-6">API Test</h1>
        <div className="bg-white p-6 rounded-lg shadow">
          {loading ? (
            <p>Testing API connection...</p>
          ) : (
            <pre className="whitespace-pre-wrap">{testResult}</pre>
          )}
        </div>
      </div>
    </div>
  )
}