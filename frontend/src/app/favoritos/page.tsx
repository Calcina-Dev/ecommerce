"use client"
import React, { useEffect, useState } from "react";
import Link from "next/link";
import Image from "next/image";
import { useFavoriteStore } from "@/store/useFavoriteStore";
import { useCartStore } from "@/store/useCartStore";
import { motion, AnimatePresence } from "framer-motion";
import { Heart, Trash2, ShoppingBag, ArrowRight, CheckCircle2 } from "lucide-react";
import { toast } from "sonner";

export default function FavoritosPage() {
  const { items, removeItem } = useFavoriteStore();
  const addItem = useCartStore((state) => state.addItem);
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
            <div className="flex items-center gap-2 text-sm text-muted-foreground mb-2">
              <Link href="/" className="hover:text-foreground transition-colors">Inicio</Link>
              <span>/</span>
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
          <Link
            href="/productos"
            className="inline-flex items-center gap-2 bg-muted hover:bg-muted/80 text-foreground px-4 py-2.5 rounded-xl font-bold text-sm transition-colors w-fit"
          >
            Explorar Catálogo
            <ArrowRight className="w-4 h-4" />
          </Link>
        </div>

        {/* Content */}
        {items.length === 0 ? (
          <motion.div 
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="text-center py-24 bg-white dark:bg-zinc-900 rounded-3xl border border-dashed border-border p-8 max-w-2xl mx-auto my-8 shadow-sm"
          >
            <div className="w-20 h-20 bg-rose-50 dark:bg-rose-950/40 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
              <Heart className="w-10 h-10" />
            </div>
            <h2 className="text-2xl font-bold mb-3 text-foreground">Tu lista de deseos está vacía</h2>
            <p className="text-muted-foreground mb-8 max-w-md mx-auto leading-relaxed">
              Guarda los productos que más te gusten haciendo clic en el corazón ❤️ mientras exploras nuestra tienda.
            </p>
            <Link
              href="/productos"
              className="inline-flex items-center gap-2 bg-primary text-primary-foreground hover:bg-primary/90 px-8 py-3.5 rounded-2xl font-bold shadow-lg shadow-primary/20 transition-all hover:scale-105"
            >
              <ShoppingBag className="w-5 h-5" />
              Ver Productos Disponibles
            </Link>
          </motion.div>
        ) : (
          <motion.div 
            layout
            className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
          >
            <AnimatePresence>
              {items.map((item) => {
                const imageUrl = item.image_url 
                  ? (item.image_url.startsWith('http')
                      ? item.image_url
                      : `${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/storage/${item.image_url}`)
                  : "https://images.unsplash.com/photo-1584308666744-24d5e47ac9db?q=80&w=600&auto=format&fit=crop";

                return (
                  <motion.div
                    key={item.id}
                    layout
                    initial={{ opacity: 0, scale: 0.9 }}
                    animate={{ opacity: 1, scale: 1 }}
                    exit={{ opacity: 0, scale: 0.8, transition: { duration: 0.2 } }}
                    whileHover={{ y: -4 }}
                    className="group bg-white dark:bg-zinc-900 rounded-2xl border border-border/80 shadow-sm overflow-hidden flex flex-col justify-between transition-all hover:shadow-md"
                  >
                    <div>
                      {/* Image & Remove Button */}
                      <div className="relative aspect-square bg-white p-6 border-b border-border/60">
                        <Link href={`/productos/${item.slug}`} className="block w-full h-full relative">
                          <Image
                            src={imageUrl}
                            alt={item.name}
                            fill
                            className="object-contain p-4 transition-transform duration-300 group-hover:scale-105"
                          />
                        </Link>
                        <button
                          onClick={() => {
                            removeItem(item.id);
                            toast("Eliminado de favoritos", {
                              description: `${item.name} fue quitado de tu lista.`,
                            });
                          }}
                          className="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 dark:bg-zinc-800/90 text-gray-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/50 shadow flex items-center justify-center transition-all z-10"
                          title="Eliminar de la lista"
                        >
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </div>

                      {/* Info */}
                      <div className="p-5">
                        <Link href={`/productos/${item.slug}`}>
                          <h3 className="font-bold text-foreground text-base line-clamp-2 hover:text-primary transition-colors mb-2 leading-snug">
                            {item.name}
                          </h3>
                        </Link>
                        <div className="text-xl font-extrabold text-foreground">
                          S/ {parseFloat(item.price).toFixed(2)}
                        </div>
                      </div>
                    </div>

                    {/* Action Button */}
                    <div className="p-5 pt-0 mt-auto">
                      <motion.button
                        whileTap={{ scale: 0.96 }}
                        onClick={() => {
                          addItem({
                            id: item.id,
                            name: item.name,
                            price: item.price,
                            image_url: item.image_url,
                            quantity: 1,
                            slug: item.slug,
                          });
                          toast.success("Producto agregado al carrito", {
                            description: `${item.name} está en tu carrito.`,
                            icon: <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                          });
                        }}
                        className="w-full bg-primary text-primary-foreground hover:bg-primary/90 py-3 rounded-xl font-bold text-sm shadow-sm transition-colors flex items-center justify-center gap-2"
                      >
                        <ShoppingBag className="w-4 h-4" />
                        Agregar al Carrito
                      </motion.button>
                    </div>
                  </motion.div>
                );
              })}
            </AnimatePresence>
          </motion.div>
        )}
      </div>
    </div>
  );
}
