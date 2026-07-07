"use client"
import { useState, useEffect, useRef } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useAuthStore } from "@/store/useAuthStore";
import { useCartStore } from "@/store/useCartStore";
import { useCatalogStore } from "@/store/useCatalogStore";
import { useFavoriteStore } from "@/store/useFavoriteStore";
import { useAddressStore } from "@/store/useAddressStore";
import { Heart, MapPin } from "lucide-react";
import { toast } from "sonner";

export function Header() {
  const { user, logout } = useAuthStore();
  const { totalItems, setIsOpen } = useCartStore();
  const { setFilterData } = useCatalogStore();
  const { items: favoriteItems, syncWithBackend } = useFavoriteStore();
  const { savedAddresses, selectedAddress, fetchAddresses, setSelectedAddress } = useAddressStore();
  const router = useRouter();
  const [searchQuery, setSearchQuery] = useState("");
  const [isClient, setIsClient] = useState(false);
  const [categoriesTree, setCategoriesTree] = useState<any[]>([]);
  const [showMenu, setShowMenu] = useState(false);
  const [showLocationModal, setShowLocationModal] = useState(false);

  // Autocomplete state
  const [suggestions, setSuggestions] = useState<any[]>([]);
  const [loadingSuggestions, setLoadingSuggestions] = useState(false);
  const [showSuggestions, setShowSuggestions] = useState(false);
  const searchContainerRef = useRef<HTMLDivElement>(null);
  const mobileSearchContainerRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    setIsClient(true);
    if (user) {
      syncWithBackend();
      fetchAddresses();
    }
    fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/catalog/filters`)
      .then(res => res.json())
      .then(data => {
        if (Array.isArray(data?.categories)) {
          setCategoriesTree(data.categories);
          setFilterData(data);
        }
      })
      .catch(err => console.error("Error loading categories menu:", err));

    // Click outside handler for autocomplete suggestions
    const handleClickOutside = (e: MouseEvent) => {
      if (
        searchContainerRef.current &&
        !searchContainerRef.current.contains(e.target as Node) &&
        mobileSearchContainerRef.current &&
        !mobileSearchContainerRef.current.contains(e.target as Node)
      ) {
        setShowSuggestions(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, [user]);

  // Live Autocomplete Fetch with Debounce
  useEffect(() => {
    if (!isClient || !searchQuery.trim()) {
      setSuggestions([]);
      setShowSuggestions(false);
      return;
    }
    const timer = setTimeout(() => {
      setLoadingSuggestions(true);
      fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/catalog/products?search=${encodeURIComponent(searchQuery.trim())}&per_page=5`)
        .then(res => res.json())
        .then(data => {
          if (data && Array.isArray(data.data)) {
            setSuggestions(data.data.slice(0, 5));
            setShowSuggestions(true);
          } else {
            setSuggestions([]);
            setShowSuggestions(true);
          }
        })
        .catch(err => console.error("Error loading suggestions:", err))
        .finally(() => setLoadingSuggestions(false));
    }, 250);
    return () => clearTimeout(timer);
  }, [searchQuery, isClient]);

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

  const renderSuggestionsDropdown = () => {
    if (!showSuggestions || !searchQuery.trim()) return null;
    return (
      <div className="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-gray-100 dark:border-zinc-800 overflow-hidden z-50 animate-in fade-in slide-in-from-top-2 duration-150">
        {loadingSuggestions ? (
          <div className="p-4 text-center text-xs text-muted-foreground flex items-center justify-center gap-2">
            <span className="w-4 h-4 border-2 border-accent border-t-transparent rounded-full animate-spin"></span>
            Buscando sugerencias...
          </div>
        ) : suggestions.length > 0 ? (
          <div>
            <ul className="divide-y divide-gray-100 dark:divide-zinc-800/80">
              {suggestions.map((prod) => {
                const price = parseFloat(prod.price).toFixed(2);
                const comparePrice = prod.compare_at_price ? parseFloat(prod.compare_at_price).toFixed(2) : null;
                const img = prod.primary_image?.image_url 
                  ? (prod.primary_image.image_url.startsWith('http') ? prod.primary_image.image_url : `${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/storage/${prod.primary_image.image_url}`)
                  : "https://images.unsplash.com/photo-1584308666744-24d5e47ac9db?q=80&w=200&auto=format&fit=crop";
                return (
                  <li key={prod.id}>
                    <button
                      type="button"
                      onClick={() => {
                        setShowSuggestions(false);
                        setSearchQuery("");
                        router.push(`/productos/${prod.slug}`);
                      }}
                      className="w-full p-3 flex items-center gap-3 hover:bg-muted/40 transition-colors text-left group"
                    >
                      <img src={img} alt={prod.name} className="w-10 h-10 object-contain rounded-lg bg-white p-1 border border-gray-100 dark:border-zinc-800 flex-shrink-0" />
                      <div className="flex-1 min-w-0">
                        <p className="text-xs font-semibold text-foreground group-hover:text-accent truncate transition-colors">{prod.name}</p>
                        <div className="flex items-center gap-1.5 mt-0.5">
                          <span className="text-xs font-bold text-foreground">S/ {price}</span>
                          {comparePrice && parseFloat(comparePrice) > parseFloat(price) && (
                            <span className="text-[10px] text-muted-foreground line-through">S/ {comparePrice}</span>
                          )}
                        </div>
                      </div>
                    </button>
                  </li>
                );
              })}
            </ul>
            <div className="p-2.5 bg-muted/20 border-t border-gray-100 dark:border-zinc-800 text-center">
              <button
                type="button"
                onClick={() => {
                  setShowSuggestions(false);
                  router.push(`/productos?search=${encodeURIComponent(searchQuery.trim())}`);
                }}
                className="text-xs font-bold text-accent hover:underline inline-flex items-center gap-1"
              >
                Ver todos los resultados para &quot;{searchQuery}&quot; →
              </button>
            </div>
          </div>
        ) : (
          <div className="p-4 text-center text-xs text-muted-foreground">
            No se encontraron productos para &quot;{searchQuery}&quot;.
          </div>
        )}
      </div>
    );
  };

  return (
    <header className="sticky top-0 z-50 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md border-b border-gray-100 dark:border-zinc-800 transition-all">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4 sm:gap-8">
        
        {/* Logo */}
        <Link href="/" className="flex items-center gap-2 flex-shrink-0 group">
          <span className="text-2xl font-black tracking-tight text-foreground group-hover:text-accent transition-colors duration-300 ease-[var(--spring-easing)]">
            COMPRA<span className="text-accent">SALUDABLE</span>
          </span>
        </Link>

        {/* Mercado Libre Style Location Indicator */}
        <div 
          onClick={() => setShowLocationModal(true)}
          className="hidden sm:flex items-center gap-2 cursor-pointer hover:bg-black/5 dark:hover:bg-white/5 px-3 py-1.5 rounded-xl transition-all border border-transparent hover:border-gray-200 dark:hover:border-zinc-700 flex-shrink-0 group/loc"
          title="Cambiar ubicación de envío"
        >
          <div className="w-8 h-8 rounded-full bg-accent/10 text-accent flex items-center justify-center flex-shrink-0 group-hover/loc:scale-110 transition-transform">
            <MapPin className="w-4 h-4" />
          </div>
          <div className="text-left leading-tight">
            <p className="text-[10px] text-muted-foreground font-bold uppercase tracking-wider">
              {user && selectedAddress ? `Enviar a ${selectedAddress.recipient_name?.split(' ')[0] || user.name?.split(' ')[0]}` : "Enviar a"}
            </p>
            <p className="text-xs font-black text-foreground mt-0.5 line-clamp-1 max-w-[150px] group-hover/loc:text-accent transition-colors">
              {selectedAddress ? `${selectedAddress.district || selectedAddress.province || selectedAddress.address}` : (user ? "Ingresa tu ubicación" : "Lima Metropolitana")}
            </p>
          </div>
        </div>

        {/* Search Bar Desktop with Autocomplete */}
        <div ref={searchContainerRef} className="flex-1 max-w-xl mx-auto hidden md:block relative">
          <form 
            onSubmit={(e) => {
              e.preventDefault();
              if (searchQuery.trim() !== "") {
                setShowSuggestions(false);
                router.push(`/productos?search=${encodeURIComponent(searchQuery.trim())}`);
              }
            }} 
            className="relative"
          >
            <input 
              type="text" 
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              onFocus={() => {
                if (suggestions.length > 0 || searchQuery.trim()) setShowSuggestions(true);
              }}
              onKeyDown={(e) => {
                if (e.key === "Escape") setShowSuggestions(false);
              }}
              placeholder="¿Qué estás buscando hoy?" 
              className="w-full bg-gray-100 dark:bg-zinc-800 border-transparent focus:bg-white dark:focus:bg-zinc-900 focus:border-accent focus:ring-2 focus:ring-accent/20 rounded-full py-2.5 pl-6 pr-12 text-sm transition-all text-foreground"
            />
            <button type="submit" className="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-500 hover:text-accent transition-colors">
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </button>
          </form>
          {renderSuggestionsDropdown()}
        </div>

        {/* Navigation & Actions */}
        <div className="flex items-center gap-6 flex-shrink-0">
          <nav className="hidden lg:flex items-center gap-6 text-sm font-semibold text-gray-600 dark:text-zinc-300">
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
                <div className="absolute top-full -left-24 w-[700px] bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-gray-100 dark:border-zinc-800 p-6 z-50 grid grid-cols-3 gap-6 animate-in fade-in slide-in-from-top-2 duration-200">
                  {categoriesTree.map((parent: any) => (
                    <div key={parent.id} className="space-y-2.5">
                      <Link 
                        href={`/productos?category=${parent.id}`} 
                        onClick={() => setShowMenu(false)}
                        className="font-bold text-gray-900 dark:text-white hover:text-accent transition-colors block text-base border-b border-gray-100 dark:border-zinc-800 pb-2 flex items-center justify-between group/link"
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
                                className="text-sm text-gray-600 dark:text-zinc-400 hover:text-accent transition-all block pl-1 hover:translate-x-1 duration-150 font-medium"
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

          <div className="flex items-center gap-3 border-l border-gray-200 dark:border-zinc-800 pl-6">
            {!isClient ? (
              <div className="w-16 h-6 animate-shimmer rounded"></div>
            ) : user ? (
              <div className="flex items-center gap-3">
                <Link href="/mi-cuenta" className="text-sm font-medium hover:text-accent transition-all active:scale-90 duration-200 ease-[var(--spring-easing)]">
                  <svg className="w-6 h-6 text-gray-700 dark:text-zinc-300 hover:text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <svg className="w-6 h-6 text-gray-700 dark:text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span className="text-sm font-medium hidden sm:block">Ingresar</span>
              </Link>
            )}

            {/* Botón de Favoritos ❤️ */}
            <Link 
              href="/favoritos"
              className="relative p-2 ml-1 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-full transition-all active:scale-90 duration-200 ease-[var(--spring-easing)] flex items-center group"
              title="Mis Favoritos / Lista de Deseos"
            >
              <div className="relative">
                <Heart className="w-6 h-6 text-gray-700 dark:text-zinc-300 group-hover:text-rose-500 transition-colors" />
                {isClient && favoriteItems.length > 0 && (
                  <span className="absolute -top-1.5 -right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white shadow-sm">
                    {favoriteItems.length}
                  </span>
                )}
              </div>
            </Link>

            <button 
              onClick={() => setIsOpen(true)}
              className="relative p-2 ml-2 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-full transition-all active:scale-90 duration-200 ease-[var(--spring-easing)] flex items-center gap-2 group"
            >
              <div className="relative">
                <svg className="w-6 h-6 text-gray-700 dark:text-zinc-300 group-hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

      {/* Mobile Search Bar with Autocomplete */}
      <div ref={mobileSearchContainerRef} className="md:hidden px-4 pb-3 pt-1 border-t border-gray-100 dark:border-zinc-800 bg-white/95 dark:bg-zinc-900/95 relative">
        <form 
          onSubmit={(e) => {
            e.preventDefault();
            if (searchQuery.trim() !== "") {
              setShowSuggestions(false);
              router.push(`/productos?search=${encodeURIComponent(searchQuery.trim())}`);
            }
          }} 
          className="relative"
        >
          <input 
            type="text" 
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            onFocus={() => {
              if (suggestions.length > 0 || searchQuery.trim()) setShowSuggestions(true);
            }}
            onKeyDown={(e) => {
              if (e.key === "Escape") setShowSuggestions(false);
            }}
            placeholder="¿Qué estás buscando hoy?" 
            className="w-full bg-gray-100/90 dark:bg-zinc-800/90 border border-gray-200/60 dark:border-zinc-700/60 focus:bg-white dark:focus:bg-zinc-900 focus:border-accent focus:ring-2 focus:ring-accent/20 rounded-2xl py-2 pl-9 pr-10 text-xs transition-all text-foreground shadow-inner"
          />
          <svg className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          {searchQuery && (
            <button type="button" onClick={() => setSearchQuery("")} className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
          )}
        </form>
        {renderSuggestionsDropdown()}
      </div>
    </header>
  );
}
