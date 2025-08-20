import { NextResponse } from 'next/server'

interface Context {
  params: {
    id: string
    imageId: string
  }
}

export async function PUT(request: Request, context: Context) {
  try {
    const authHeader = request.headers.get('authorization')
    const { id, imageId } = await context.params
    const body = await request.json()
    
    if (!authHeader) {
      return NextResponse.json({ message: 'No authorization header' }, { status: 401 })
    }

    const response = await fetch(`http://localhost:8000/api/projects/${id}/images/${imageId}`, {
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
    console.error('Project image PUT API error:', error)
    return NextResponse.json(
      { message: 'Failed to update image' },
      { status: 500 }
    )
  }
}

export async function DELETE(request: Request, context: Context) {
  try {
    const authHeader = request.headers.get('authorization')
    const { id, imageId } = await context.params
    
    if (!authHeader) {
      return NextResponse.json({ message: 'No authorization header' }, { status: 401 })
    }

    const response = await fetch(`http://localhost:8000/api/projects/${id}/images/${imageId}`, {
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
    console.error('Project image DELETE API error:', error)
    return NextResponse.json(
      { message: 'Failed to delete image' },
      { status: 500 }
    )
  }
}