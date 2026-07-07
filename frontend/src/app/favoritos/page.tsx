"use client"
import React, { useEffect, useState } from "react";
import Link from "next/link";
import { useFavoriteStore } from "@/store/useFavoriteStore";
import { useAuthStore } from "@/store/useAuthStore";
import { Heart, ArrowRight, ArrowLeft } from "lucide-react";
import { UserFavoritesManager } from "@/components/UserFavoritesManager";

export default function FavoritosPage() {
  const { items } = useFavoriteStore();
  const { user } = useAuthStore();
  const [isClient, setIsClient] = useState(false);

  useEffect(() => {
    setIsClient(true);
  }, []);

  if (!isClient) {
    return (
      <div className="min-h-[70vh] bg-background flex items-center justify-center">
        <div className="w-8 h-8 rounded-full border-4 border-primary border-t-transparent animate-spin"></div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-background py-12">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {/* Header */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 border-b pb-6">
          <div>
            <div className="flex items-center gap-2 text-sm text-muted-foreground mb-2 flex-wrap">
              <Link href="/" className="hover:text-foreground transition-colors">Inicio</Link>
              <span>/</span>
              {user && (
                <>
                  <Link href="/mi-cuenta" className="hover:text-foreground transition-colors font-medium text-primary">Mi Perfil</Link>
                  <span>/</span>
                </>
              )}
              <span className="text-foreground font-medium">Mis Favoritos</span>
            </div>
            <h1 className="text-3xl sm:text-4xl font-extrabold tracking-tight flex items-center gap-3">
              <Heart className="w-8 h-8 text-rose-500 fill-rose-500" />
              Lista de Deseos
              <span className="text-base font-semibold bg-rose-500/10 text-rose-600 dark:text-rose-400 px-3 py-1 rounded-full">
                {items.length} {items.length === 1 ? "producto" : "productos"}
              </span>
            </h1>
          </div>
          <div className="flex items-center gap-3 flex-wrap">
            {user && (
              <Link
                href="/mi-cuenta?tab=favorites"
                className="inline-flex items-center gap-2 bg-primary/10 hover:bg-primary/20 text-primary px-4 py-2.5 rounded-xl font-bold text-sm transition-colors"
              >
                <ArrowLeft className="w-4 h-4" />
                Volver a Mi Perfil
              </Link>
            )}
            <Link
              href="/productos"
              className="inline-flex items-center gap-2 bg-muted hover:bg-muted/80 text-foreground px-4 py-2.5 rounded-xl font-bold text-sm transition-colors w-fit"
            >
              Explorar Catálogo
              <ArrowRight className="w-4 h-4" />
            </Link>
          </div>
        </div>

        {/* Content */}
        <UserFavoritesManager />
      </div>
    </div>
  );
}
