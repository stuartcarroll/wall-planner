'use client'

import { createContext, useContext, useState, useEffect, ReactNode } from 'react'

interface Paint {
  id: number
  product_name: string
  maker: string
  product_code: string
  form: string
  hex_color: string
  price_gbp: number
  volume_ml: number
  color_description: string
}

interface BasketItem {
  paint: Paint
  quantity: number
}

interface BasketContextType {
  basketItems: BasketItem[]
  basketCount: number
  basketTotal: number
  addToBasket: (paint: Paint, quantity?: number) => void
  removeFromBasket: (paintId: number) => void
  updateQuantity: (paintId: number, quantity: number) => void
  clearBasket: () => void
  getItemQuantity: (paintId: number) => number
  getTotalCost: () => number
}

const BasketContext = createContext<BasketContextType | undefined>(undefined)

export function BasketProvider({ children }: { children: ReactNode }) {
  const [basketItems, setBasketItems] = useState<BasketItem[]>([])
  const [isHydrated, setIsHydrated] = useState(false)

  // Load basket from localStorage on mount (client-side only)
  useEffect(() => {
    setIsHydrated(true)
    const savedBasket = localStorage.getItem('paint_basket')
    if (savedBasket) {
      try {
        setBasketItems(JSON.parse(savedBasket))
      } catch (error) {
        console.error('Error loading basket from localStorage:', error)
        localStorage.removeItem('paint_basket')
      }
    }
  }, [])

  // Save basket to localStorage whenever it changes (client-side only)
  useEffect(() => {
    if (isHydrated) {
      localStorage.setItem('paint_basket', JSON.stringify(basketItems))
    }
  }, [basketItems, isHydrated])

  const basketCount = basketItems.reduce((total, item) => total + item.quantity, 0)
  const basketTotal = basketItems.reduce((total, item) => total + (item.paint.price_gbp * item.quantity), 0)

  const addToBasket = (paint: Paint, quantity = 1) => {
    setBasketItems(prevItems => {
      const existingItem = prevItems.find(item => item.paint.id === paint.id)
      
      if (existingItem) {
        return prevItems.map(item =>
          item.paint.id === paint.id
            ? { ...item, quantity: item.quantity + quantity }
            : item
        )
      }
      
      return [...prevItems, { paint, quantity }]
    })
  }

  const removeFromBasket = (paintId: number) => {
    setBasketItems(prevItems => prevItems.filter(item => item.paint.id !== paintId))
  }

  const updateQuantity = (paintId: number, quantity: number) => {
    if (quantity <= 0) {
      removeFromBasket(paintId)
      return
    }
    
    setBasketItems(prevItems =>
      prevItems.map(item =>
        item.paint.id === paintId
          ? { ...item, quantity }
          : item
      )
    )
  }

  const clearBasket = () => {
    setBasketItems([])
  }

  const getItemQuantity = (paintId: number): number => {
    const item = basketItems.find(item => item.paint.id === paintId)
    return item ? item.quantity : 0
  }

  const getTotalCost = (): number => {
    return basketTotal
  }

  return (
    <BasketContext.Provider value={{
      basketItems,
      basketCount,
      basketTotal,
      addToBasket,
      removeFromBasket,
      updateQuantity,
      clearBasket,
      getItemQuantity,
      getTotalCost,
    }}>
      {children}
    </BasketContext.Provider>
  )
}

export const useBasket = () => {
  const context = useContext(BasketContext)
  if (context === undefined) {
    throw new Error('useBasket must be used within a BasketProvider')
  }
  return context
}