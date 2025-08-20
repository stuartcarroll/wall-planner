import { NextResponse } from 'next/server'

export async function GET(request: Request) {
  try {
    const { searchParams } = new URL(request.url)
    const queryString = searchParams.toString()
    const apiUrl = `http://localhost:8000/api/paints${queryString ? `?${queryString}` : ''}`
    
    const response = await fetch(apiUrl, {
      headers: {
        'Content-Type': 'application/json',
      },
    })
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    
    const data = await response.json()
    return NextResponse.json(data)
  } catch (error) {
    console.error('Error fetching paints:', error)
    return NextResponse.json(
      { error: 'Failed to fetch paints' },
      { status: 500 }
    )
  }
}