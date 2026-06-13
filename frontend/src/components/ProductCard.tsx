import Image from "next/image";
import Link from "next/link";
import { useCartStore } from "@/store/useCartStore";
import { motion } from "framer-motion";
import { toast } from "sonner";
import { CheckCircle2 } from "lucide-react";

interface Product {
  id: number;
  name: string;
  slug: string;
  price: string;
  compare_at_price: string | null;
  primary_image?: {
    image_url: string;
  };
  brand?: {
    name: string;
  };
}

export function ProductCard({ product }: { product: Product }) {
  const addItem = useCartStore((state) => state.addItem);

  const imageUrl = product.primary_image?.image_url 
    ? (product.primary_image.image_url.startsWith('http')
        ? product.primary_image.image_url
        : `http://localhost:8000/storage/${product.primary_image.image_url}`)
    : "https://images.unsplash.com/photo-1584308666744-24d5e47ac9db?q=80&w=600&auto=format&fit=crop";

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
      whileHover={{ y: -6, transition: { type: "spring", stiffness: 400, damping: 25 } }}
      className="group relative block bg-white rounded-2xl overflow-hidden border border-gray-100 transition-shadow duration-300 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] h-full flex flex-col isolate [transform:translateZ(0)]"
    >
      
      {/* Badges */}
      <div className="absolute top-3 left-3 z-10 flex flex-col gap-2">
        {product.compare_at_price && parseFloat(product.compare_at_price) > parseFloat(product.price) && (
          <span className="bg-red-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full tracking-wider uppercase">
            Oferta
          </span>
        )}
      </div>

      <Link href={`/productos/${product.slug}`} className="relative aspect-[4/5] bg-gray-50 overflow-hidden rounded-t-2xl block" style={{ transform: 'translateZ(0)' }}>
        <Image
          src={imageUrl}
          alt={product.name}
          fill
          className="object-cover mix-blend-multiply transition-transform duration-500 group-hover:scale-110"
        />
      </Link>

      {/* Hover Quick Action - Desktop Only */}
      <div className="absolute bottom-32 left-0 right-0 px-4 translate-y-8 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300 hidden md:block z-20">
        <motion.button 
          whileTap={{ scale: 0.95 }}
          transition={{ type: "spring", stiffness: 500, damping: 30 }}
          onClick={handleAddToCart}
          className="w-full bg-accent hover:bg-accent/90 text-white font-bold py-2.5 rounded-xl shadow-lg transition-colors flex items-center justify-center gap-2"
        >
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Agregar
        </motion.button>
      </div>

      <Link href={`/productos/${product.slug}`} className="p-5 flex flex-col flex-grow bg-white z-10">
        {product.brand && (
          <span className="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">
            {product.brand.name}
          </span>
        )}
        <h3 className="font-semibold text-gray-900 text-[15px] leading-snug mb-2 line-clamp-2 transition-colors group-hover:text-accent">
          {product.name}
        </h3>
        
        <div className="mt-auto flex items-center gap-2">
          <span className="text-lg font-bold text-gray-900">
            S/ {parseFloat(product.price).toFixed(2)}
          </span>
          {product.compare_at_price && parseFloat(product.compare_at_price) > parseFloat(product.price) && (
            <span className="text-sm font-medium text-gray-400 line-through">
              S/ {parseFloat(product.compare_at_price).toFixed(2)}
            </span>
          )}
        </div>
      </Link>

      {/* Mobile Add to Cart (Visible always on mobile) */}
      <motion.button 
        whileTap={{ scale: 0.9 }}
        onClick={handleAddToCart}
        className="md:hidden absolute bottom-4 right-4 bg-gray-900 text-white p-2.5 rounded-full shadow-md z-20"
      >
        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
      </motion.button>

    </motion.div>
  );
}
