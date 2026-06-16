"use client"
import { useState, useEffect } from "react";
import Link from "next/link";
import { useAuthStore } from "@/store/useAuthStore";
import { useCartStore } from "@/store/useCartStore";

export function Header() {
  const { user, logout } = useAuthStore();
  const { totalItems, setIsOpen } = useCartStore();
  const [isClient, setIsClient] = useState(false);

  useEffect(() => {
    setIsClient(true);
  }, []);

  const handleLogout = () => {
    // Aquí idealmente llamarías a la API de logout también
    fetch("http://localhost:8000/api/auth/logout", {
      method: "POST",
      headers: {
        "Authorization": `Bearer ${useAuthStore.getState().token}`
      }
    }).catch(console.error);
    logout();
  };

  return (
    <header className="fixed top-0 left-0 right-0 z-40 w-full border-b border-gray-100 bg-white/70 backdrop-blur-xl transition-all [transform:translateZ(0)]">
      <div className="container mx-auto px-4 h-16 flex items-center justify-between">
        
        {/* Logo */}
        <Link href="/" className="flex-shrink-0 flex items-center">
          <img 
            src="https://comprasaludable.com/wp-content/uploads/2020/10/LOGO-COMPRASALUDABLE-800x800-1.png" 
            alt="Compra Saludable" 
            className="w-20 h-20 object-contain"
          />
        </Link>

        {/* Search Bar (Bexo Style) */}
        <div className="flex-1 max-w-2xl w-full">
          <div className="relative">
            <input 
              type="text" 
              placeholder="¿Qué estás buscando hoy?" 
              className="w-full bg-gray-100 border-transparent focus:bg-white focus:border-accent focus:ring-2 focus:ring-accent/20 rounded-full py-2.5 pl-6 pr-12 text-sm transition-all"
            />
            <button className="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-500 hover:text-accent transition-colors">
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </button>
          </div>
        </div>

        {/* Navigation & Actions */}
        <div className="flex items-center gap-6 flex-shrink-0">
          <nav className="hidden lg:flex gap-6 text-sm font-semibold text-gray-600">
            <Link href="/productos" className="hover:text-accent transition-colors">Categorías</Link>
            <Link href="/productos?ofertas=true" className="hover:text-accent transition-colors text-red-500">Ofertas</Link>
          </nav>

          <div className="flex items-center gap-3 border-l pl-6">
            {!isClient ? (
              <div className="w-16 h-6 animate-pulse bg-gray-200 rounded"></div>
            ) : user ? (
              <div className="flex items-center gap-3">
                <Link href="/mi-cuenta" className="text-sm font-medium hover:text-accent transition-colors">
                  <svg className="w-6 h-6 text-gray-700 hover:text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                </Link>
                <button onClick={handleLogout} className="text-sm text-gray-500 hover:text-red-500 transition-colors" title="Cerrar sesión">
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                  </svg>
                </button>
              </div>
            ) : (
              <Link href="/login" className="flex items-center gap-2 hover:text-accent transition-colors">
                <svg className="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span className="text-sm font-medium hidden sm:block">Ingresar</span>
              </Link>
            )}

            <button 
              onClick={() => setIsOpen(true)}
              className="relative p-2 ml-2 hover:bg-gray-100 rounded-full transition-colors flex items-center gap-2 group"
            >
              <div className="relative">
                <svg className="w-6 h-6 text-gray-700 group-hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                {isClient && totalItems() > 0 && (
                  <span className="absolute -top-1.5 -right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-accent text-[10px] font-bold text-white">
                    {totalItems()}
                  </span>
                )}
              </div>
            </button>
          </div>
        </div>
      </div>
    </header>
  );
}
