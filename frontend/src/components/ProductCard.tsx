import Image from "next/image";
import Link from "next/link";
import { useCartStore } from "@/store/useCartStore";
import { useFavoriteStore } from "@/store/useFavoriteStore";
import { motion } from "framer-motion";
import { toast } from "sonner";
import { useState } from "react";
import { CheckCircle2, ChevronLeft, ChevronRight, Heart } from "lucide-react";

interface Product {
  id: number;
  name: string;
  slug: string;
  price: string;
  compare_at_price: string | null;
  stock?: number;
  primary_image?: {
    image_url: string;
  };
  images?: {
    id: number;
    image_url: string;
    is_primary?: boolean;
  }[];
  brand?: {
    name: string;
  };
}

export function ProductCard({ product }: { product: Product }) {
  const addItem = useCartStore((state) => state.addItem);
  const { isFavorite, toggleItem } = useFavoriteStore();

  if (!product || !product.id || !product.name || product.price === undefined || product.price === null || isNaN(Number(product.price)) || (product.stock !== undefined && product.stock <= 0)) {
    return null;
  }

  const isFav = isFavorite(product.id);

  const handleToggleFavorite = async (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    const added = await toggleItem({
      id: product.id,
      name: product.name,
      price: product.price,
      image_url: product.primary_image?.image_url || null,
      slug: product.slug,
    });
    if (added) {
      toast.success("Añadido a tus favoritos ❤️", {
        description: `${product.name} fue guardado en tu lista de deseos.`,
      });
    } else {
      toast("Eliminado de favoritos", {
        description: `${product.name} fue quitado de tu lista.`,
      });
    }
  };

  const imageUrl = product.primary_image?.image_url 
    ? (product.primary_image.image_url.startsWith('http')
        ? product.primary_image.image_url
        : `${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/storage/${product.primary_image.image_url}`)
    : "https://images.unsplash.com/photo-1584308666744-24d5e47ac9db?q=80&w=600&auto=format&fit=crop";

  const allImages = (product.images && product.images.length > 0)
    ? product.images.map(img => img.image_url.startsWith('http') ? img.image_url : `${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/storage/${img.image_url}`)
    : [imageUrl];

  const [currentIdx, setCurrentIdx] = useState(0);

  const nextImg = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setCurrentIdx((prev) => (prev + 1) % allImages.length);
  };

  const prevImg = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setCurrentIdx((prev) => (prev - 1 + allImages.length) % allImages.length);
  };

  const handleAddToCart = (e: React.MouseEvent) => {
    e.preventDefault(); // Evitar navegación al detalle
    addItem({
      id: product.id,
      name: product.name,
      price: product.price,
      image_url: product.primary_image?.image_url || null,
      quantity: 1,
      slug: product.slug
    });
    
    // Emil Kowalski style toast
    toast.success("Producto agregado", {
      description: `${product.name} está en tu carrito.`,
      icon: <CheckCircle2 className="w-4 h-4 text-emerald-500" />
    });
  };

  return (
    <motion.div 
      whileHover={{ y: -6, scale: 1.01, transition: { type: "spring", stiffness: 400, damping: 25 } }}
      whileTap={{ scale: 0.96 }}
      className="group relative block bg-white dark:bg-zinc-900 rounded-2xl overflow-hidden shadow-sm ring-1 ring-black/5 dark:ring-white/10 transition-all duration-500 ease-[var(--spring-easing)] hover:shadow-[0_4px_20px_rgba(0,0,0,0.04),0_10px_40px_rgba(0,0,0,0.04)] dark:hover:shadow-[0_4px_20px_rgba(0,0,0,0.4)] hover:ring-black/10 dark:hover:ring-white/20 h-full flex flex-col isolate [transform:translateZ(0)]"
    >
      
      {/* Glare Effect */}
      <div className="absolute inset-0 z-30 pointer-events-none rounded-2xl bg-gradient-to-tr from-white/0 via-white/30 to-white/0 opacity-0 group-hover:opacity-100 transition-opacity duration-700 ease-[var(--spring-easing)] mix-blend-overlay"></div>

      {/* Badges */}
      <div className="absolute top-3 left-3 z-10 flex flex-col gap-2">
        {product.compare_at_price && parseFloat(product.compare_at_price) > parseFloat(product.price) && (
          <span className="bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[10px] font-bold px-2.5 py-1 rounded-full tracking-wider uppercase flex items-center gap-1.5 shadow-sm">
            <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Oferta
          </span>
        )}
      </div>

      {/* Favorite Heart Button */}
      <motion.button
        whileTap={{ scale: 0.7 }}
        whileHover={{ scale: 1.1 }}
        onClick={handleToggleFavorite}
        className={`absolute top-3 right-3 z-20 w-8 h-8 rounded-full flex items-center justify-center shadow-md transition-colors ${
          isFav 
            ? "bg-rose-500 text-white" 
            : "bg-white/90 dark:bg-zinc-800/90 text-gray-400 hover:text-rose-500 dark:text-zinc-400 dark:hover:text-rose-400"
        }`}
        title={isFav ? "Quitar de favoritos" : "Añadir a favoritos"}
      >
        <Heart className={`w-4 h-4 ${isFav ? "fill-white" : ""}`} />
      </motion.button>

      <Link href={`/productos/${product.slug}`} className="relative aspect-[4/5] bg-white dark:bg-white overflow-hidden rounded-t-2xl block p-4 group/slider border-b border-gray-100 dark:border-zinc-800/80" style={{ transform: 'translateZ(0)' }}>
        <Image
          src={allImages[currentIdx] || imageUrl}
          alt={product.name}
          fill
          className="object-contain p-5 sm:p-6 transition-transform duration-500 group-hover:scale-105"
        />

        {allImages.length > 1 && (
          <>
            <button 
              onClick={prevImg}
              className="absolute left-2 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-white/80 dark:bg-zinc-800/80 hover:bg-white dark:hover:bg-zinc-800 shadow flex items-center justify-center text-gray-700 dark:text-gray-200 opacity-0 group-hover/slider:opacity-100 transition-opacity z-20"
              title="Anterior"
            >
              <ChevronLeft className="w-4 h-4" />
            </button>
            <button 
              onClick={nextImg}
              className="absolute right-2 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-white/80 dark:bg-zinc-800/80 hover:bg-white dark:hover:bg-zinc-800 shadow flex items-center justify-center text-gray-700 dark:text-gray-200 opacity-0 group-hover/slider:opacity-100 transition-opacity z-20"
              title="Siguiente"
            >
              <ChevronRight className="w-4 h-4" />
            </button>
            <div className="absolute bottom-2 left-0 right-0 flex justify-center gap-1 z-20 pointer-events-none">
              {allImages.map((_, i) => (
                <span 
                  key={i} 
                  className={`h-1.5 rounded-full transition-all duration-300 ${i === currentIdx ? 'bg-primary dark:bg-emerald-500 w-3' : 'bg-gray-300 dark:bg-zinc-600 w-1.5'}`}
                />
              ))}
            </div>
          </>
        )}
      </Link>

      {/* Hover Quick Action - Desktop Only */}
      <div className="absolute bottom-36 left-0 right-0 px-4 translate-y-8 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300 hidden md:block z-20">
        <motion.button 
          whileTap={{ scale: 0.95 }}
          transition={{ type: "spring", stiffness: 500, damping: 30 }}
          onClick={handleAddToCart}
          className="w-full bg-accent/90 backdrop-blur-sm hover:bg-accent text-white font-bold py-2.5 rounded-xl shadow-lg transition-colors flex items-center justify-center gap-2"
        >
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Agregar
        </motion.button>
      </div>

      <Link href={`/productos/${product.slug}`} className="p-3 sm:p-5 flex flex-col flex-grow bg-white dark:bg-zinc-900 z-10">
        {product.brand && (
          <span className="text-[9px] sm:text-[11px] font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-widest mb-1 sm:mb-1.5 line-clamp-1">
            {product.brand.name}
          </span>
        )}
        <h3 className="font-semibold text-gray-900 dark:text-gray-100 text-xs sm:text-[15px] leading-snug mb-1.5 sm:mb-2 line-clamp-2 transition-colors group-hover:text-accent tracking-tight">
          {product.name ? product.name.replace(/\\r\\n/g, ' ').replace(/\\n/g, ' ').replace(/\\/g, '').replace(/\p{Extended_Pictographic}/gu, '').trim() : ''}
        </h3>
        
        <div className="mt-auto flex items-baseline gap-1.5 sm:gap-2 pt-1.5 sm:pt-2">
          <span className="text-base sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">
            S/ {parseFloat(product.price).toFixed(2)}
          </span>
          {product.compare_at_price && parseFloat(product.compare_at_price) > parseFloat(product.price) && (
            <span className="text-[10px] sm:text-xs font-medium text-gray-400 dark:text-zinc-500 line-through">
              S/ {parseFloat(product.compare_at_price).toFixed(2)}
            </span>
          )}
        </div>
      </Link>

      {/* Mobile Add to Cart (Visible always on mobile) */}
      <motion.button 
        whileTap={{ scale: 0.9 }}
        onClick={handleAddToCart}
        className="md:hidden absolute bottom-2.5 right-2.5 sm:bottom-4 sm:right-4 bg-gray-900 dark:bg-emerald-600 text-white p-2 sm:p-2.5 rounded-full shadow-md z-20"
      >
        <svg className="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
      </motion.button>

    </motion.div>
  );
}
