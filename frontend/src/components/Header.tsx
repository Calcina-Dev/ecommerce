"use client"
import { useState, useEffect } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useAuthStore } from "@/store/useAuthStore";
import { useCartStore } from "@/store/useCartStore";

export function Header() {
  const { user, logout } = useAuthStore();
  const { totalItems, setIsOpen } = useCartStore();
  const router = useRouter();
  const [searchQuery, setSearchQuery] = useState("");
  const [isClient, setIsClient] = useState(false);
  const [categoriesTree, setCategoriesTree] = useState<any[]>([]);
  const [showMenu, setShowMenu] = useState(false);

  useEffect(() => {
    setIsClient(true);
    fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/catalog/filters`)
      .then(res => res.json())
      .then(data => {
        if (data?.categories) setCategoriesTree(data.categories);
      })
      .catch(err => console.error("Error loading categories menu:", err));
  }, []);

  useEffect(() => {
    if (!isClient) return;
    const timer = setTimeout(() => {
      if (searchQuery.trim() !== "") {
        const encoded = encodeURIComponent(searchQuery.trim());
        if (window.location.pathname !== "/productos" || !window.location.search.includes(`search=${encoded}`)) {
          router.push(`/productos?search=${encoded}`);
        }
      } else if (searchQuery === "") {
        if (window.location.pathname === "/productos" && window.location.search.includes("search=")) {
          router.push("/productos");
        }
      }
    }, 350);
    return () => clearTimeout(timer);
  }, [searchQuery, isClient, router]);

  const handleLogout = () => {
    fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/auth/logout`, {
      method: "POST",
      headers: {
        "Authorization": `Bearer ${useAuthStore.getState().token}`
      }
    }).finally(() => {
      logout();
      router.push("/");
    });
  };

  return (
    <header className="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100 transition-all">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4 sm:gap-8">
        
        {/* Logo */}
        <Link href="/" className="flex items-center gap-2 flex-shrink-0 group">
          <span className="text-2xl font-black tracking-tight text-foreground group-hover:text-accent transition-colors duration-300 ease-[var(--spring-easing)]">
            COMPRA<span className="text-accent">SALUDABLE</span>
          </span>
        </Link>

        {/* Search Bar (Functional) */}
        <div className="flex-1 max-w-xl mx-auto hidden md:block">
          <form 
            onSubmit={(e) => {
              e.preventDefault();
              if (searchQuery.trim() !== "") {
                router.push(`/productos?search=${encodeURIComponent(searchQuery.trim())}`);
              }
            }} 
            className="relative"
          >
            <input 
              type="text" 
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="¿Qué estás buscando hoy?" 
              className="w-full bg-gray-100 border-transparent focus:bg-white focus:border-accent focus:ring-2 focus:ring-accent/20 rounded-full py-2.5 pl-6 pr-12 text-sm transition-all text-foreground"
            />
            <button type="submit" className="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-500 hover:text-accent transition-colors">
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </button>
          </form>
        </div>

        {/* Navigation & Actions */}
        <div className="flex items-center gap-6 flex-shrink-0">
          <nav className="hidden lg:flex items-center gap-6 text-sm font-semibold text-gray-600">
            <div 
              className="relative py-2"
              onMouseEnter={() => setShowMenu(true)}
              onMouseLeave={() => setShowMenu(false)}
            >
              <Link href="/productos" className="flex items-center gap-1 hover:text-accent transition-colors py-1">
                Categorías
                <svg className={`w-4 h-4 transition-transform duration-200 ${showMenu ? 'rotate-180 text-accent' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </Link>

              {showMenu && categoriesTree.length > 0 && (
                <div className="absolute top-full -left-24 w-[700px] bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 z-50 grid grid-cols-3 gap-6 animate-in fade-in slide-in-from-top-2 duration-200">
                  {categoriesTree.map((parent: any) => (
                    <div key={parent.id} className="space-y-2.5">
                      <Link 
                        href={`/productos?category=${parent.id}`} 
                        onClick={() => setShowMenu(false)}
                        className="font-bold text-gray-900 hover:text-accent transition-colors block text-base border-b border-gray-100 pb-2 flex items-center justify-between group/link"
                      >
                        <span>{parent.name}</span>
                        <span className="text-xs text-accent opacity-0 group-hover/link:opacity-100 transition-opacity">Ver todo →</span>
                      </Link>
                      {parent.children && parent.children.length > 0 && (
                        <ul className="space-y-2 pt-0.5">
                          {parent.children.map((child: any) => (
                            <li key={child.id}>
                              <Link 
                                href={`/productos?category=${child.id}`}
                                onClick={() => setShowMenu(false)}
                                className="text-sm text-gray-600 hover:text-accent transition-all block pl-1 hover:translate-x-1 duration-150 font-medium"
                              >
                                {child.name}
                              </Link>
                              {child.children && child.children.length > 0 && (
                                <ul className="pl-3 space-y-1 mt-1 border-l-2 border-accent/20">
                                  {child.children.map((sub: any) => (
                                    <li key={sub.id}>
                                      <Link 
                                        href={`/productos?category=${sub.id}`}
                                        onClick={() => setShowMenu(false)}
                                        className="text-xs text-gray-400 hover:text-accent transition-colors block py-0.5"
                                      >
                                        {sub.name}
                                      </Link>
                                    </li>
                                  ))}
                                </ul>
                              )}
                            </li>
                          ))}
                        </ul>
                      )}
                    </div>
                  ))}
                </div>
              )}
            </div>
            <Link href="/productos?ofertas=true" className="hover:text-accent text-red-500 transition-colors active:scale-95 duration-200 ease-[var(--spring-easing)] inline-block">Ofertas</Link>
            <Link href="/rastrear-pedido" className="hover:text-accent transition-colors active:scale-95 duration-200 ease-[var(--spring-easing)] inline-block">Rastrear Pedido</Link>
          </nav>

          <div className="flex items-center gap-3 border-l pl-6">
            {!isClient ? (
              <div className="w-16 h-6 animate-shimmer rounded"></div>
            ) : user ? (
              <div className="flex items-center gap-3">
                <Link href="/mi-cuenta" className="text-sm font-medium hover:text-accent transition-all active:scale-90 duration-200 ease-[var(--spring-easing)]">
                  <svg className="w-6 h-6 text-gray-700 hover:text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                </Link>
                <button onClick={handleLogout} className="text-sm text-gray-500 hover:text-red-500 transition-all active:scale-90 duration-200 ease-[var(--spring-easing)]" title="Cerrar sesión">
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                  </svg>
                </button>
              </div>
            ) : (
              <Link href="/login" className="flex items-center gap-2 hover:text-accent transition-all active:scale-95 duration-200 ease-[var(--spring-easing)]">
                <svg className="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span className="text-sm font-medium hidden sm:block">Ingresar</span>
              </Link>
            )}

            <button 
              onClick={() => setIsOpen(true)}
              className="relative p-2 ml-2 hover:bg-gray-100 rounded-full transition-all active:scale-90 duration-200 ease-[var(--spring-easing)] flex items-center gap-2 group"
            >
              <div className="relative">
                <svg className="w-6 h-6 text-gray-700 group-hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                {isClient && totalItems() > 0 && (
                  <span className="absolute -top-1.5 -right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-accent text-[10px] font-bold text-white shadow-sm">
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
