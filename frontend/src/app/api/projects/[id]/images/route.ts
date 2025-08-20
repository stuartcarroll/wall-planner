import { NextResponse } from 'next/server'

interface Context {
  params: {
    id: string
  }
}

export async function GET(request: Request, context: Context) {
  try {
    const authHeader = request.headers.get('authorization')
    const { id } = await context.params
    
    if (!authHeader) {
      return NextResponse.json({ message: 'No authorization header' }, { status: 401 })
    }

    const response = await fetch(`http://localhost:8000/api/projects/${id}/images`, {
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
    console.error('Project images GET API error:', error)
    return NextResponse.json(
      { message: 'Failed to fetch project images' },
      { status: 500 }
    )
  }
}

export async function POST(request: Request, context: Context) {
  try {
    const authHeader = request.headers.get('authorization')
    const { id } = await context.params
    
    if (!authHeader) {
      return NextResponse.json({ message: 'No authorization header' }, { status: 401 })
    }

    // Get the form data from the request
    const formData = await request.formData()

    const response = await fetch(`http://localhost:8000/api/projects/${id}/images`, {
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
    console.error('Project images POST API error:', error)
    return NextResponse.json(
      { message: 'Failed to upload image' },
      { status: 500 }
    )
  }
}