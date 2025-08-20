'use client'

import Link from "next/link";
import { useAuth } from "@/contexts/AuthContext";
import { useBasket } from "@/contexts/BasketContext";
import { useRouter } from 'next/navigation';
import { useState } from 'react';

export default function Navigation() {
  const { user, isAdmin, logout } = useAuth();
  const { basketCount } = useBasket();
  const router = useRouter();
  const [isPaintDropdownOpen, setIsPaintDropdownOpen] = useState(false);

  const handleLogout = async () => {
    await logout();
    router.push('/');
  };

  return (
    <nav className="bg-white/90 backdrop-blur-sm shadow-lg border-b border-gray-200/50 sticky top-0 z-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between h-16">
          <div className="flex items-center">
            <Link href="/" className="text-xl font-bold text-gray-900 hover:text-blue-600 transition-colors">
              🎨 Wall Planner
            </Link>
          </div>
          
          <div className="flex items-center space-x-4">
            {user ? (
              <>
                <Link 
                  href="/dashboard" 
                  className="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-3 py-2 rounded-lg text-sm font-medium transition-all"
                >
                  📊 Dashboard
                </Link>
                <Link 
                  href="/projects" 
                  className="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-3 py-2 rounded-lg text-sm font-medium transition-all"
                >
                  📁 Projects
                </Link>
                
                {/* Paint Dropdown */}
                <div className="relative">
                  <button
                    onClick={() => setIsPaintDropdownOpen(!isPaintDropdownOpen)}
                    onBlur={() => setTimeout(() => setIsPaintDropdownOpen(false), 150)}
                    className="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-3 py-2 rounded-lg text-sm font-medium flex items-center transition-all"
                  >
                    🎨 Paint
                    <svg className={`ml-1 h-4 w-4 transition-transform ${isPaintDropdownOpen ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>
                  
                  {isPaintDropdownOpen && (
                    <div className="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-200/50 z-50 animate-fade-in">
                      <div className="py-2">
                        <Link 
                          href="/paints" 
                          className="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors"
                          onClick={() => setIsPaintDropdownOpen(false)}
                        >
                          🛍️ <span className="ml-3">Paint Catalog</span>
                        </Link>
                        <Link 
                          href="/paint-bundles" 
                          className="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors"
                          onClick={() => setIsPaintDropdownOpen(false)}
                        >
                          📦 <span className="ml-3">Paint Bundles</span>
                        </Link>
                      </div>
                    </div>
                  )}
                </div>
                
                <Link 
                  href="/basket" 
                  className="relative text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-3 py-2 rounded-lg text-sm font-medium transition-all"
                >
                  🛒 Basket
                  {basketCount > 0 && (
                    <span className="absolute -top-1 -right-1 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-semibold shadow-lg animate-pulse">
                      {basketCount}
                    </span>
                  )}
                </Link>
                {isAdmin && (
                  <>
                    <Link 
                      href="/admin/users" 
                      className="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                    >
                      Users
                    </Link>
                    <Link 
                      href="/admin/user-groups" 
                      className="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                    >
                      User Groups
                    </Link>
                  </>
                )}
                <span className="text-gray-500 text-sm">
                  {user.name} {isAdmin && '(Admin)'}
                </span>
                <button
                  onClick={handleLogout}
                  className="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                >
                  Logout
                </button>
              </>
            ) : (
              <>
                <Link 
                  href="/login" 
                  className="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                >
                  Login
                </Link>
                <Link 
                  href="/register" 
                  className="bg-blue-600 text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium"
                >
                  Register
                </Link>
              </>
            )}
          </div>
        </div>
      </div>
    </nav>
  );
}