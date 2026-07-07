"use client"
import { useState, useEffect, useRef } from "react";
import { createPortal } from "react-dom";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useAuthStore } from "@/store/useAuthStore";
import { useCartStore } from "@/store/useCartStore";
import { useCatalogStore } from "@/store/useCatalogStore";
import { useFavoriteStore } from "@/store/useFavoriteStore";
import { useAddressStore } from "@/store/useAddressStore";
import { Heart, MapPin } from "lucide-react";
import { toast } from "sonner";
import ubigeosData from "@/data/ubigeos_peru.json";
import dynamic from "next/dynamic";

const AddressMapSelector = dynamic(() => import("@/components/AddressMapSelector").then(mod => mod.AddressMapSelector), { ssr: false });

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

  // Estado para formulario modal de agregar dirección en vivo
  const [isAddingAddress, setIsAddingAddress] = useState(false);
  const [newAddrForm, setNewAddrForm] = useState({
    recipient_name: "",
    phone: "",
    address: "",
    department: "Lima",
    province: "Lima",
    district: "Miraflores",
    alias: "Casa"
  });

  const [locDept, setLocDept] = useState("Lima");
  const [locProv, setLocProv] = useState("Lima");
  const [locDist, setLocDist] = useState("Miraflores");

  const departments = Array.from(new Set(ubigeosData.map((u: any) => u.department))).sort();
  const provinces = Array.from(new Set(ubigeosData.filter((u: any) => u.department === locDept).map((u: any) => u.province))).sort();
  const districts = Array.from(new Set(ubigeosData.filter((u: any) => u.province === locProv && u.department === locDept).map((u: any) => u.district))).sort();

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
      <div className="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-2 sm:gap-8">
        
        {/* Logo */}
        <Link href="/" className="flex items-center gap-1.5 flex-shrink-0 group">
          <span className="text-xl sm:text-2xl font-black tracking-tight text-foreground group-hover:text-accent transition-colors duration-300 ease-[var(--spring-easing)]">
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

        {/* Search Bar Desktop with Autocomplete - WIDE & PREMIUM */}
        <div ref={searchContainerRef} className="flex-1 max-w-2xl mx-4 lg:mx-8 hidden md:block relative">
          <form 
            onSubmit={(e) => {
              e.preventDefault();
              if (searchQuery.trim() !== "") {
                setShowSuggestions(false);
                router.push(`/productos?search=${encodeURIComponent(searchQuery.trim())}`);
              }
            }} 
            className="relative flex items-center"
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
              className="w-full bg-gray-100/90 dark:bg-zinc-800/90 border border-gray-200/80 dark:border-zinc-700/80 hover:bg-white dark:hover:bg-zinc-800 hover:border-gray-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-900 focus:border-accent focus:ring-4 focus:ring-accent/15 rounded-full py-2.5 pl-5 pr-28 text-sm transition-all duration-200 text-foreground placeholder:text-gray-400 dark:placeholder:text-zinc-500 shadow-sm"
            />
            {searchQuery && (
              <button 
                type="button" 
                onClick={() => setSearchQuery("")} 
                className="absolute right-24 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 transition-colors"
                title="Limpiar búsqueda"
              >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path></svg>
              </button>
            )}
            <button 
              type="submit" 
              className="absolute right-1.5 top-1/2 -translate-y-1/2 px-4 py-1.5 bg-primary hover:bg-primary/90 text-primary-foreground font-bold text-xs rounded-full shadow-sm flex items-center gap-1.5 transition-all hover:scale-105 active:scale-95"
            >
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              <span>Buscar</span>
            </button>
          </form>
          {renderSuggestionsDropdown()}
        </div>

        {/* Actions (User, Heart, Cart) */}
        <div className="flex items-center gap-1 sm:gap-3 flex-shrink-0">
          <div className="flex items-center gap-1 sm:gap-3 border-l border-gray-200 dark:border-zinc-800 pl-1.5 sm:pl-6">
            {!isClient ? (
              <div className="w-16 h-6 animate-shimmer rounded"></div>
            ) : user ? (
              <div className="flex items-center gap-1 sm:gap-3">
                <Link href="/mi-cuenta" className="text-sm font-medium hover:text-accent transition-all active:scale-90 duration-200 ease-[var(--spring-easing)]">
                  <svg className="w-5 h-5 sm:w-6 sm:h-6 text-gray-700 dark:text-zinc-300 hover:text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                </Link>
                <button onClick={handleLogout} className="text-sm text-gray-500 hover:text-red-500 transition-all active:scale-90 duration-200 ease-[var(--spring-easing)]" title="Cerrar sesión">
                  <svg className="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                  </svg>
                </button>
              </div>
            ) : (
              <Link href="/login" className="flex items-center gap-1 sm:gap-2 hover:text-accent transition-all active:scale-95 duration-200 ease-[var(--spring-easing)]">
                <svg className="w-5 h-5 sm:w-6 sm:h-6 text-gray-700 dark:text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span className="text-sm font-medium hidden sm:block">Ingresar</span>
              </Link>
            )}

            {/* Botón de Favoritos ❤️ */}
            <Link 
              href="/favoritos"
              className="relative p-1.5 sm:p-2 ml-0.5 sm:ml-1 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-full transition-all active:scale-90 duration-200 ease-[var(--spring-easing)] flex items-center group"
              title="Mis Favoritos / Lista de Deseos"
            >
              <div className="relative">
                <Heart className="w-5 h-5 sm:w-6 sm:h-6 text-gray-700 dark:text-zinc-300 group-hover:text-rose-500 transition-colors" />
                {isClient && favoriteItems.length > 0 && (
                  <span className="absolute -top-1.5 -right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white shadow-sm">
                    {favoriteItems.length}
                  </span>
                )}
              </div>
            </Link>

            <button 
              onClick={() => setIsOpen(true)}
              className="relative p-1.5 sm:p-2 ml-0.5 sm:ml-2 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-full transition-all active:scale-90 duration-200 ease-[var(--spring-easing)] flex items-center gap-2 group"
            >
              <div className="relative">
                <svg className="w-5 h-5 sm:w-6 sm:h-6 text-gray-700 dark:text-zinc-300 group-hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

      {/* Secondary Navigation Bar (Estilo Mercado Libre / Amazon) */}
      <div className="hidden md:block bg-gray-50/80 dark:bg-zinc-900/80 border-t border-gray-100 dark:border-zinc-800/80">
        <div className="max-w-7xl mx-auto px-6 lg:px-8 h-10 flex items-center justify-between text-xs sm:text-sm font-semibold text-gray-600 dark:text-zinc-300">
          <nav className="flex items-center gap-6 lg:gap-8">
            <div 
              className="relative py-2"
              onMouseEnter={() => setShowMenu(true)}
              onMouseLeave={() => setShowMenu(false)}
            >
              <Link href="/productos" className="flex items-center gap-1.5 text-foreground hover:text-accent transition-colors font-bold py-1">
                <svg className="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                Categorías
                <svg className={`w-3.5 h-3.5 transition-transform duration-200 ${showMenu ? 'rotate-180 text-accent' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </Link>

              {showMenu && categoriesTree.length > 0 && (
                <div className="absolute top-full left-0 w-[700px] bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-gray-100 dark:border-zinc-800 p-6 z-50 grid grid-cols-3 gap-6 animate-in fade-in slide-in-from-top-2 duration-200">
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

            <Link href="/productos?ofertas=true" className="hover:text-accent text-red-500 transition-colors font-bold flex items-center gap-1">
              <span>🔥</span> Ofertas
            </Link>
            <Link href="/rastrear-pedido" className="hover:text-accent transition-colors">Rastrear Pedido</Link>
          </nav>

          <div className="flex items-center gap-4 text-xs text-muted-foreground font-medium">
            <span className="flex items-center gap-1.5">
              <svg className="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              Compra 100% Segura
            </span>
            <span className="hidden lg:inline text-gray-300 dark:text-zinc-700">|</span>
            <span className="hidden lg:flex items-center gap-1">
              <svg className="w-3.5 h-3.5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
              Envío Rápido y Garantizado
            </span>
          </div>
        </div>
      </div>

      {/* Mobile Search Bar with Autocomplete */}
      <div ref={mobileSearchContainerRef} className="md:hidden px-3 pb-3 pt-1.5 border-t border-gray-100 dark:border-zinc-800 bg-white/95 dark:bg-zinc-900/95 relative shadow-sm">
        <form 
          onSubmit={(e) => {
            e.preventDefault();
            if (searchQuery.trim() !== "") {
              setShowSuggestions(false);
              router.push(`/productos?search=${encodeURIComponent(searchQuery.trim())}`);
            }
          }} 
          className="relative flex items-center"
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
            className="w-full bg-gray-100/90 dark:bg-zinc-800/90 border border-gray-200/80 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-900 focus:border-accent focus:ring-2 focus:ring-accent/20 rounded-full py-2.5 pl-9 pr-20 text-sm transition-all text-foreground shadow-inner placeholder:text-gray-400"
          />
          <svg className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          {searchQuery && (
            <button type="button" onClick={() => setSearchQuery("")} className="absolute right-14 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
          )}
          <button 
            type="submit" 
            className="absolute right-1.5 top-1/2 -translate-y-1/2 px-3.5 py-1.5 bg-primary hover:bg-primary/90 text-primary-foreground font-bold text-xs rounded-full shadow-sm flex items-center transition-all"
          >
            <span>Buscar</span>
          </button>
        </form>
        {renderSuggestionsDropdown()}
      </div>



      {/* Modal de Ubicación (Estilo Mercado Libre) - Renderizado vía Portal al Body para evitar recortes por Header/backdrop-blur */}
      {isClient && showLocationModal && createPortal(
        <div 
          onClick={() => setShowLocationModal(false)}
          className="fixed inset-0 z-[99999] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-200 cursor-pointer"
        >
          <div 
            className="bg-background text-foreground rounded-3xl max-w-md w-full shadow-2xl border border-border overflow-hidden animate-in zoom-in-95 duration-200 cursor-default"
            onClick={(e) => e.stopPropagation()}
          >
            {/* Header del Modal - Estilo Limpio y Minimalista de la Tienda */}
            <div className="px-6 pt-6 pb-4 border-b border-gray-100 dark:border-zinc-800 flex items-center justify-between bg-background">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-2xl bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-primary shrink-0">
                  <MapPin className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="font-extrabold text-base sm:text-lg text-foreground tracking-tight">Elige dónde recibir tus compras</h3>
                  <p className="text-xs text-muted-foreground font-medium">Podrás ver costos y tiempos de envío exactos</p>
                </div>
              </div>
              <button
                onClick={() => { setShowLocationModal(false); setIsAddingAddress(false); }}
                className="w-8 h-8 rounded-full bg-muted/60 hover:bg-muted text-muted-foreground hover:text-foreground flex items-center justify-center font-bold transition-all"
              >
                ✕
              </button>
            </div>

            <div className="p-6 max-h-[80vh] overflow-y-auto">
              {isAddingAddress ? (
                <div className="space-y-4 animate-in fade-in duration-200">
                  <div className="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-3">
                    <h4 className="font-black text-sm text-foreground flex items-center gap-2">
                      <span className="w-2 h-2 rounded-full bg-accent"></span>
                      Nueva dirección de envío
                    </h4>
                    <button 
                      type="button"
                      onClick={() => setIsAddingAddress(false)}
                      className="text-xs font-bold text-muted-foreground hover:text-foreground underline"
                    >
                      ← Volver al listado
                    </button>
                  </div>

                  {/* Mapa Interactivo Leaflet para seleccionar la dirección */}
                  <AddressMapSelector
                    selectedDepartment={newAddrForm.department}
                    selectedProvince={newAddrForm.province}
                    selectedDistrict={newAddrForm.district}
                    onSelectLocation={(loc) => {
                      setNewAddrForm((prev) => ({
                        ...prev,
                        address: loc.address || prev.address,
                        district: loc.district || prev.district,
                        province: loc.province || prev.province,
                        department: loc.department || prev.department,
                      }));
                    }}
                  />

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-left">
                    <div>
                      <label className="block text-[11px] font-bold uppercase text-muted-foreground mb-1">Etiqueta</label>
                      <select
                        value={newAddrForm.alias}
                        onChange={(e) => setNewAddrForm({ ...newAddrForm, alias: e.target.value })}
                        className="w-full px-3 py-2 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-accent"
                      >
                        <option value="Casa">🏠 Casa</option>
                        <option value="Trabajo">💼 Trabajo</option>
                        <option value="Oficina">🏢 Oficina</option>
                        <option value="Otro">📍 Otro</option>
                      </select>
                    </div>
                    <div>
                      <label className="block text-[11px] font-bold uppercase text-muted-foreground mb-1">Teléfono / Celular</label>
                      <input
                        type="tel"
                        required
                        maxLength={9}
                        placeholder="Ej: 987654321"
                        value={newAddrForm.phone}
                        onChange={(e) => setNewAddrForm({ ...newAddrForm, phone: e.target.value })}
                        className="w-full px-3 py-2 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-accent"
                      />
                    </div>
                  </div>

                  <div className="text-left">
                    <label className="block text-[11px] font-bold uppercase text-muted-foreground mb-1">Nombre de quien recibe</label>
                    <input
                      type="text"
                      required
                      placeholder="Nombre y Apellido"
                      value={newAddrForm.recipient_name}
                      onChange={(e) => setNewAddrForm({ ...newAddrForm, recipient_name: e.target.value })}
                      className="w-full px-3 py-2 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-accent"
                    />
                  </div>

                  <div className="text-left">
                    <label className="block text-[11px] font-bold uppercase text-muted-foreground mb-1">Dirección (Calle, número, dpto)</label>
                    <input
                      type="text"
                      required
                      placeholder="Av. Principal 123, Dpto 401"
                      value={newAddrForm.address}
                      onChange={(e) => setNewAddrForm({ ...newAddrForm, address: e.target.value })}
                      className="w-full px-3 py-2 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-accent"
                    />
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-3 gap-2 text-left">
                    <div>
                      <label className="block text-[11px] font-bold uppercase text-muted-foreground mb-1">Dpto</label>
                      <select 
                        value={newAddrForm.department} 
                        onChange={(e) => setNewAddrForm({ ...newAddrForm, department: e.target.value, province: "", district: "" })} 
                        className="w-full px-2.5 py-2 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-accent"
                      >
                        {departments.map(d => <option key={d} value={d}>{d}</option>)}
                      </select>
                    </div>
                    <div>
                      <label className="block text-[11px] font-bold uppercase text-muted-foreground mb-1">Provincia</label>
                      <select 
                        value={newAddrForm.province} 
                        onChange={(e) => setNewAddrForm({ ...newAddrForm, province: e.target.value, district: "" })} 
                        disabled={!newAddrForm.department}
                        className="w-full px-2.5 py-2 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-accent disabled:opacity-50"
                      >
                        {Array.from(new Set(ubigeosData.filter((u: any) => u.department === newAddrForm.department).map((u: any) => u.province))).sort().map(p => <option key={p} value={p}>{p}</option>)}
                      </select>
                    </div>
                    <div>
                      <label className="block text-[11px] font-bold uppercase text-muted-foreground mb-1">Distrito</label>
                      <select 
                        value={newAddrForm.district} 
                        onChange={(e) => setNewAddrForm({ ...newAddrForm, district: e.target.value })} 
                        disabled={!newAddrForm.province}
                        className="w-full px-2.5 py-2 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-accent disabled:opacity-50"
                      >
                        {Array.from(new Set(ubigeosData.filter((u: any) => u.province === newAddrForm.province && u.department === newAddrForm.department).map((u: any) => u.district))).sort().map(d => <option key={d} value={d}>{d}</option>)}
                      </select>
                    </div>
                  </div>

                  <div className="pt-2 flex gap-2">
                    <button
                      type="button"
                      onClick={() => setIsAddingAddress(false)}
                      className="flex-1 py-2.5 rounded-xl border border-gray-300 dark:border-zinc-700 text-xs font-bold hover:bg-gray-100 dark:hover:bg-zinc-800 transition-all"
                    >
                      Cancelar
                    </button>
                    <button
                      type="button"
                      disabled={!newAddrForm.address || !newAddrForm.phone || !newAddrForm.recipient_name || !newAddrForm.district}
                      onClick={async () => {
                        const token = useAuthStore.getState().token;
                        if (token) {
                          try {
                            const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/user/addresses`, {
                              method: "POST",
                              headers: {
                                "Authorization": `Bearer ${token}`,
                                "Content-Type": "application/json",
                                "Accept": "application/json"
                              },
                              body: JSON.stringify({
                                ...newAddrForm,
                                postal_code: "000000",
                                is_default: savedAddresses.length === 0
                              })
                            });
                            if (res.ok) {
                              await useAddressStore.getState().fetchAddresses();
                              const newAddrObj = {
                                id: Date.now(),
                                ...newAddrForm,
                                postal_code: "000000",
                                is_default: false
                              };
                              setSelectedAddress(newAddrObj);
                              toast.success("¡Dirección guardada y seleccionada!", {
                                description: `Enviaremos tus pedidos a ${newAddrForm.alias}: ${newAddrForm.address}, ${newAddrForm.district}.`
                              });
                              setIsAddingAddress(false);
                              setShowLocationModal(false);
                            } else {
                              toast.error("Error al guardar la dirección");
                            }
                          } catch (e) {
                            console.error(e);
                            toast.error("Error de conexión al guardar");
                          }
                        }
                      }}
                      className="flex-2 px-6 py-2.5 rounded-xl bg-accent text-white text-xs font-bold hover:bg-accent/90 transition-all shadow-md disabled:opacity-50"
                    >
                      Guardar y usar para envíos
                    </button>
                  </div>
                </div>
              ) : (
                <>
                  {/* Sección 1: Direcciones guardadas (si está logueado) */}
                  {user && savedAddresses.length > 0 && (
                    <div className="space-y-3">
                      <p className="text-xs font-black text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                        <span className="w-2 h-2 rounded-full bg-accent"></span>
                        Mis direcciones guardadas
                      </p>
                      <div className="grid grid-cols-1 gap-3">
                        {savedAddresses.map((addr) => {
                          const isSel = selectedAddress?.id === addr.id;
                          return (
                            <div
                              key={addr.id}
                              onClick={() => {
                                setSelectedAddress(addr);
                                toast.success("¡Ubicación actualizada!", {
                                  description: `Tus compras se calcularán para envío a ${addr.alias || 'tu dirección'}.`,
                                });
                                setShowLocationModal(false);
                              }}
                              className={`p-4 sm:p-5 rounded-2xl border transition-all cursor-pointer ${
                                isSel
                                  ? "border-emerald-500 bg-white dark:bg-zinc-900 ring-1 ring-emerald-500 shadow-sm"
                                  : "border-gray-200 hover:border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-900"
                              }`}
                            >
                              <div className="flex items-center justify-between mb-3">
                                <div className="flex items-center gap-2">
                                  <span className="bg-slate-900 text-white dark:bg-white dark:text-slate-900 px-3 py-1 rounded-full text-xs font-bold">
                                    {addr.alias || "Casa"}
                                  </span>
                                  {addr.is_default && (
                                    <span className="bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 px-3 py-1 rounded-full text-xs font-medium flex items-center gap-1">
                                      ★ Predeterminada
                                    </span>
                                  )}
                                </div>
                                {isSel && (
                                  <div className="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center">
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M5 13l4 4L19 7"/></svg>
                                  </div>
                                )}
                              </div>

                              <p className="text-base font-bold text-gray-900 dark:text-white mt-3 text-left">{addr.recipient_name}</p>
                              
                              <div className="flex items-start gap-2 mt-2 text-sm text-gray-600 dark:text-gray-300 text-left">
                                <svg className="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <div>
                                  <p>{addr.address}</p>
                                  <p className="text-gray-500 dark:text-gray-400">{addr.district}, {addr.province}</p>
                                </div>
                              </div>

                              <div className="flex items-center gap-2 mt-2 text-sm font-medium text-gray-700 dark:text-gray-300 text-left">
                                <svg className="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span>{addr.phone}</span>
                              </div>

                              <div className="border-t border-gray-200 dark:border-zinc-800 mt-4 pt-4 flex items-center justify-between">
                                <button
                                  type="button"
                                  onClick={(e) => {
                                    e.stopPropagation();
                                    setSelectedAddress(addr);
                                    toast.success("¡Ubicación actualizada!", {
                                      description: `Tus compras se calcularán para envío a ${addr.alias || 'tu dirección'}.`,
                                    });
                                    setShowLocationModal(false);
                                  }}
                                  className="bg-slate-900 hover:bg-slate-800 text-white dark:bg-white dark:text-slate-900 dark:hover:bg-gray-100 font-semibold text-sm py-2 px-5 rounded-xl transition-all shadow-sm"
                                >
                                  Usar esta dirección
                                </button>
                                <button
                                  type="button"
                                  onClick={(e) => {
                                    e.stopPropagation();
                                    setShowLocationModal(false);
                                    router.push("/mi-cuenta");
                                  }}
                                  className="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white font-medium text-sm px-4 py-2 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-xl transition-all"
                                >
                                  Editar
                                </button>
                              </div>
                            </div>
                          );
                        })}
                      </div>
                      <div className="pt-1 flex flex-col sm:flex-row gap-2">
                        <button 
                          type="button"
                          className="flex-1 rounded-xl font-bold py-2.5 px-3 border-2 border-accent/20 bg-accent/5 hover:bg-accent text-accent hover:text-white transition-all text-xs uppercase tracking-wider flex items-center justify-center gap-1.5 shadow-sm"
                          onClick={() => {
                            setIsAddingAddress(true);
                            setNewAddrForm({
                              recipient_name: user?.name || "",
                              phone: "",
                              address: "",
                              department: locDept || "Lima",
                              province: locProv || "Lima",
                              district: locDist || "Miraflores",
                              alias: "Casa"
                            });
                          }}
                        >
                          + Agregar nueva dirección aquí
                        </button>
                        <button 
                          type="button"
                          className="rounded-xl font-semibold py-2.5 px-3 border border-gray-200 dark:border-zinc-700 hover:border-gray-400 text-muted-foreground hover:text-foreground transition-all text-xs flex items-center justify-center"
                          onClick={() => {
                            setShowLocationModal(false);
                            router.push("/mi-cuenta");
                          }}
                        >
                          ⚙️ Mi Cuenta
                        </button>
                      </div>
                    </div>
                  )}

                  {/* Si el usuario está logueado pero no tiene direcciones guardadas aún */}
                  {user && savedAddresses.length === 0 && (
                    <div className="text-center py-5 px-4 bg-accent/5 rounded-3xl border-2 border-dashed border-accent/30 space-y-3">
                      <p className="text-sm font-bold text-foreground">Aún no tienes direcciones guardadas</p>
                      <p className="text-xs text-muted-foreground">Agrega una dirección ahora para que tus compras se autocompleten siempre</p>
                      <button
                        type="button"
                        onClick={() => {
                          setIsAddingAddress(true);
                          setNewAddrForm({
                            recipient_name: user?.name || "",
                            phone: "",
                            address: "",
                            department: locDept || "Lima",
                            province: locProv || "Lima",
                            district: locDist || "Miraflores",
                            alias: "Casa"
                          });
                        }}
                        className="bg-accent text-white px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider shadow-md hover:bg-accent/90 transition-all"
                      >
                        + Agregar mi primera dirección
                      </button>
                    </div>
                  )}

                  {/* Sección 2: Selector Interactivo de Ubigeo / Distrito (Solo si no ha iniciado sesión) */}
                  {!user && (
                    <div className="space-y-4">
                      {/* Mapa interactivo visible directamente en el selector general */}
                      <AddressMapSelector
                        selectedDepartment={locDept}
                        selectedProvince={locProv}
                        selectedDistrict={locDist}
                        onSelectLocation={(loc) => {
                          if (loc.department) setLocDept(loc.department);
                          if (loc.province) setLocProv(loc.province);
                          if (loc.district) setLocDist(loc.district);
                        }}
                      />

                      <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                        <div>
                          <label className="block text-[11px] font-bold uppercase text-muted-foreground mb-1">Departamento</label>
                          <select 
                            value={locDept} 
                            onChange={(e) => { 
                              setLocDept(e.target.value); 
                              setLocProv(""); 
                              setLocDist(""); 
                            }} 
                            className="w-full px-3 py-2 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold text-foreground outline-none focus:ring-2 focus:ring-accent"
                          >
                            {departments.map(d => <option key={d} value={d}>{d}</option>)}
                          </select>
                        </div>
                        <div>
                          <label className="block text-[11px] font-bold uppercase text-muted-foreground mb-1">Provincia</label>
                          <select 
                            value={locProv} 
                            onChange={(e) => { 
                              setLocProv(e.target.value); 
                              setLocDist(""); 
                            }} 
                            disabled={!locDept}
                            className="w-full px-3 py-2 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold text-foreground outline-none focus:ring-2 focus:ring-accent disabled:opacity-50"
                          >
                            {provinces.map(p => <option key={p} value={p}>{p}</option>)}
                          </select>
                        </div>
                        <div>
                          <label className="block text-[11px] font-bold uppercase text-muted-foreground mb-1">Distrito</label>
                          <select 
                            value={locDist} 
                            onChange={(e) => setLocDist(e.target.value)} 
                            disabled={!locProv}
                            className="w-full px-3 py-2 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold text-foreground outline-none focus:ring-2 focus:ring-accent disabled:opacity-50"
                          >
                            {districts.map(d => <option key={d} value={d}>{d}</option>)}
                          </select>
                        </div>
                      </div>

                      <button
                        type="button"
                        disabled={!locDist}
                        onClick={() => {
                          setSelectedAddress({
                            id: 0,
                            alias: locDist,
                            recipient_name: "Ubicación seleccionada",
                            phone: "",
                            department: locDept,
                            province: locProv,
                            district: locDist,
                            address: `${locDist}, ${locProv}`,
                            postal_code: "",
                            is_default: false
                          });
                          toast.success(`📍 Ubicación cambiada a ${locDist}, ${locProv}`, {
                            description: "Los tiempos de entrega y envíos se calcularán para esta zona."
                          });
                          setShowLocationModal(false);
                        }}
                        className="w-full mt-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold py-2.5 rounded-xl text-xs uppercase tracking-wider hover:bg-accent hover:text-white dark:hover:bg-accent dark:hover:text-white transition-all disabled:opacity-50 flex items-center justify-center gap-2 shadow-sm"
                      >
                        <MapPin className="w-4 h-4" />
                        Aplicar esta ubicación para envíos
                      </button>
                    </div>
                  )}

                  {/* Si no ha iniciado sesión */}
                  {!user && (
                    <div className="text-center py-4 px-4 bg-amber-500/10 rounded-2xl border border-amber-500/20 flex flex-col sm:flex-row items-center justify-between gap-3">
                      <div className="text-left">
                        <h5 className="font-bold text-xs sm:text-sm text-foreground">¿Ya tienes direcciones guardadas?</h5>
                        <p className="text-[11px] text-muted-foreground">Inicia sesión para ver tu lista como en Mercado Libre</p>
                      </div>
                      <button 
                        type="button"
                        className="rounded-xl font-bold bg-accent hover:bg-accent/90 text-white px-5 py-2 text-xs transition-all shadow-sm flex-shrink-0"
                        onClick={() => {
                          setShowLocationModal(false);
                          router.push("/login");
                        }}
                      >
                        Ingresar a mi cuenta
                      </button>
                    </div>
                  )}
                </>
              )}
            </div>

            {/* Footer */}
            <div className="p-4 bg-muted/30 border-t border-gray-100 dark:border-zinc-800 text-center text-xs text-muted-foreground flex items-center justify-center gap-1.5 font-medium">
              <span className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
              Envíos rápidos y seguros a todos los distritos del Perú
            </div>
          </div>
        </div>,
        document.body
      )}
    </header>
  );
}
