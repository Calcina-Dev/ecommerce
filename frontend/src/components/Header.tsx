"use client"
import Link from "next/link";
import { useAuthStore } from "@/store/useAuthStore";
import { useCartStore } from "@/store/useCartStore";
import { Button } from "./ui/button";

export function Header() {
  const { user, logout } = useAuthStore();
  const { totalItems, setIsOpen } = useCartStore();

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
    <header className="sticky top-0 z-50 w-full border-b bg-background/80 backdrop-blur">
      <div className="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
        <div className="flex items-center gap-6">
          <Link href="/" className="font-bold text-xl tracking-tight">
            VITA<span className="text-primary">SUPPS</span>
          </Link>
          <nav className="hidden md:flex gap-6 text-sm font-medium text-muted-foreground">
            <Link href="/" className="hover:text-foreground transition-colors">Inicio</Link>
            <Link href="/productos" className="hover:text-foreground transition-colors">Catálogo</Link>
          </nav>
        </div>

        <div className="flex items-center gap-4">
          {user ? (
            <div className="flex items-center gap-4">
              <span className="text-sm font-medium hidden sm:inline-block">Hola, {user.name}</span>
              <Button variant="outline" size="sm" onClick={handleLogout} className="rounded-xl">
                Cerrar Sesión
              </Button>
            </div>
          ) : (
            <Link href="/login">
              <Button variant="default" size="sm" className="rounded-xl px-6">
                Iniciar Sesión
              </Button>
            </Link>
          )}

          <button 
            onClick={() => setIsOpen(true)}
            className="relative p-2 ml-2 hover:bg-muted rounded-full transition-colors"
          >
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            {totalItems() > 0 && (
              <span className="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-primary rounded-full">
                {totalItems()}
              </span>
            )}
          </button>
        </div>
      </div>
    </header>
  );
}
