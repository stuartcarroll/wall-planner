import { NextResponse } from 'next/server'

export async function POST(request: Request) {
  try {
    const authHeader = request.headers.get('authorization')
    const body = await request.json()
    
    if (!authHeader) {
      return NextResponse.json({ message: 'No authorization header' }, { status: 401 })
    }

    const response = await fetch('http://localhost:8000/api/scraping/batch', {
      method: 'POST',
      headers: {
        'Authorization': authHeader,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(body),
    })
    
    const data = await response.json()
    
    if (!response.ok) {
      return NextResponse.json(data, { status: response.status })
    }
    
    return NextResponse.json(data)
  } catch (error) {
    console.error('Scraping batch API error:', error)
    return NextResponse.json(
      { message: 'Failed to scrape batch' },
      { status: 500 }
    )
  }
}