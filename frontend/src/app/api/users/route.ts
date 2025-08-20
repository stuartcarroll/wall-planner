import { NextResponse } from 'next/server'

export async function GET(request: Request) {
  try {
    const authHeader = request.headers.get('authorization')
    
    if (!authHeader) {
      return NextResponse.json({ message: 'No authorization header' }, { status: 401 })
    }

    const response = await fetch('http://localhost:8000/api/users', {
      headers: {
        'Authorization': authHeader,
        'Accept': 'application/json',
      },
    })
    
    const data = await response.json()
    
    if (!response.ok) {
      return NextResponse.json(data, { status: response.status })
    }
    
    return NextResponse.json(data)
  } catch (error) {
    console.error('Users API error:', error)
    return NextResponse.json(
      { message: 'Failed to fetch users' },
      { status: 500 }
    )
  }
}