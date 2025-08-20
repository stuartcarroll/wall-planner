'use client'

import { useEffect } from 'react';
import { useAuth } from "@/contexts/AuthContext";
import { useRouter } from 'next/navigation';

export default function Home() {
  const { user, isLoading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!isLoading) {
      if (user) {
        // If authenticated, redirect to dashboard
        router.push('/dashboard');
      } else {
        // If not authenticated, redirect to login
        router.push('/login');
      }
    }
  }, [user, isLoading, router]);

  // Show loading while redirecting
  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center">
      <div className="text-lg">Redirecting...</div>
    </div>
  );
}
