"use client"
import React from "react";
import Link from "next/link";
import Image from "next/image";
import { useFavoriteStore } from "@/store/useFavoriteStore";
import { useCartStore } from "@/store/useCartStore";
import { motion, AnimatePresence } from "framer-motion";
import { Heart, Trash2, ShoppingBag, CheckCircle2 } from "lucide-react";
import { toast } from "sonner";

export function UserFavoritesManager() {
  const { items, removeItem } = useFavoriteStore();
  const addItem = useCartStore((state) => state.addItem);

  if (items.length === 0) {
    return (
      <motion.div 
        initial={{ opacity: 0, y: 15 }}
        animate={{ opacity: 1, y: 0 }}
        className="text-center py-16 bg-background rounded-3xl border border-dashed border-border p-8 max-w-2xl mx-auto my-4 shadow-sm"
      >
        <div className="w-16 h-16 bg-rose-50 dark:bg-rose-950/40 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
          <Heart className="w-8 h-8" />
        </div>
        <h3 className="text-xl font-bold mb-2 text-foreground">Tu lista de deseos está vacía</h3>
        <p className="text-sm text-muted-foreground mb-6 max-w-md mx-auto leading-relaxed">
          Guarda los productos que más te gusten haciendo clic en el corazón ❤️ mientras exploras nuestra tienda.
        </p>
        <Link
          href="/productos"
          className="inline-flex items-center gap-2 bg-primary text-primary-foreground hover:bg-primary/90 px-8 py-3 rounded-2xl font-bold text-sm shadow-md shadow-primary/20 transition-all hover:scale-105"
        >
          <ShoppingBag className="w-4 h-4" />
          Explorar Catálogo
        </Link>
      </motion.div>
    );
  }

  return (
    <motion.div 
      layout
      className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"
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
              className="group bg-background rounded-2xl border border-border/80 shadow-sm overflow-hidden flex flex-col justify-between transition-all hover:shadow-md"
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
                      description: `${item.name} está en tu bolsa.`,
                      icon: <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                    });
                  }}
                  className="w-full bg-primary text-primary-foreground hover:bg-primary/90 py-3 rounded-xl font-bold text-sm shadow-sm transition-colors flex items-center justify-center gap-2"
                >
                  <ShoppingBag className="w-4 h-4" />
                  Agregar a la Bolsa
                </motion.button>
              </div>
            </motion.div>
          );
        })}
      </AnimatePresence>
    </motion.div>
  );
}
