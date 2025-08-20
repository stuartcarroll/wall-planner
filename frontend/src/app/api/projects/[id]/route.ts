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

    const response = await fetch(`http://localhost:8000/api/projects/${id}`, {
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
    console.error('Project GET API error:', error)
    return NextResponse.json(
      { message: 'Failed to fetch project' },
      { status: 500 }
    )
  }
}

export async function PUT(request: Request, context: Context) {
  try {
    const authHeader = request.headers.get('authorization')
    const { id } = await context.params
    const body = await request.json()
    
    if (!authHeader) {
      return NextResponse.json({ message: 'No authorization header' }, { status: 401 })
    }

    const response = await fetch(`http://localhost:8000/api/projects/${id}`, {
      method: 'PUT',
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
    console.error('Project PUT API error:', error)
    return NextResponse.json(
      { message: 'Failed to update project' },
      { status: 500 }
    )
  }
}

export async function DELETE(request: Request, context: Context) {
  try {
    const authHeader = request.headers.get('authorization')
    const { id } = await context.params
    
    if (!authHeader) {
      return NextResponse.json({ message: 'No authorization header' }, { status: 401 })
    }

    const response = await fetch(`http://localhost:8000/api/projects/${id}`, {
      method: 'DELETE',
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
    console.error('Project DELETE API error:', error)
    return NextResponse.json(
      { message: 'Failed to delete project' },
      { status: 500 }
    )
  }
}