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
      whileHover={{ y: -6, scale: 1.01, transition: { type: "spring", stiffness: 400, damping: 25 } }}
      whileTap={{ scale: 0.98 }}
      className="group relative block bg-white rounded-2xl overflow-hidden shadow-[0_2px_10px_rgb(0,0,0,0.02)] ring-1 ring-black/[0.03] transition-all duration-300 hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] hover:ring-black/[0.08] h-full flex flex-col isolate [transform:translateZ(0)]"
    >
      
      {/* Badges */}
      <div className="absolute top-3 left-3 z-10 flex flex-col gap-2">
        {product.compare_at_price && parseFloat(product.compare_at_price) > parseFloat(product.price) && (
          <span className="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2.5 py-1 rounded-full tracking-wider uppercase flex items-center gap-1.5 shadow-sm">
            <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
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

      <Link href={`/productos/${product.slug}`} className="p-5 flex flex-col flex-grow bg-white z-10">
        {/* Rating Stars (Trust Signal) */}
        <div className="flex items-center gap-1 mb-2">
          {[...Array(5)].map((_, i) => (
            <svg key={i} className={`w-3.5 h-3.5 ${i < 4 ? 'text-amber-400 fill-amber-400' : 'text-gray-300 fill-gray-300'}`} viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
          ))}
          <span className="text-[10px] text-muted-foreground ml-1 font-medium">(12)</span>
        </div>

        {product.brand && (
          <span className="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">
            {product.brand.name}
          </span>
        )}
        <h3 className="font-semibold text-gray-900 text-[15px] leading-snug mb-2 line-clamp-2 transition-colors group-hover:text-accent tracking-tight">
          {product.name}
        </h3>
        
        <div className="mt-auto flex items-baseline gap-2 pt-2">
          <span className="text-xl font-bold text-gray-900 tracking-tight">
            S/ {parseFloat(product.price).toFixed(2)}
          </span>
          {product.compare_at_price && parseFloat(product.compare_at_price) > parseFloat(product.price) && (
            <span className="text-xs font-medium text-gray-400 line-through">
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
