import { NextResponse } from 'next/server'

export async function POST(request: Request) {
  try {
    const authHeader = request.headers.get('authorization')
    
    if (!authHeader) {
      return NextResponse.json({ message: 'No authorization header' }, { status: 401 })
    }

    // Get the form data from the request
    const formData = await request.formData()

    const response = await fetch('http://localhost:8000/api/paints/import', {
      method: 'POST',
      headers: {
        'Authorization': authHeader,
        'Accept': 'application/json',
      },
      body: formData,
    })
    
    const data = await response.json()
    
    if (!response.ok) {
      return NextResponse.json(data, { status: response.status })
    }
    
    return NextResponse.json(data)
  } catch (error) {
    console.error('Paint import API error:', error)
    return NextResponse.json(
      { message: 'Failed to import paints' },
      { status: 500 }
    )
  }
}