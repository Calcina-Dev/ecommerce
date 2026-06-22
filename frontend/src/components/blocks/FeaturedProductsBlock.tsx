"use client"
import { useRef } from "react";
import Link from "next/link";
import { ProductCard } from "@/components/ProductCard";
import { ChevronLeft, ChevronRight } from "lucide-react";

export function FeaturedProductsBlock({ data }: { data: any }) {
  const scrollContainerRef = useRef<HTMLDivElement>(null);

  if (!data.products || data.products.length === 0) return null;

  const isCarousel = data.products.length > 4;

  const scroll = (direction: 'left' | 'right') => {
    if (scrollContainerRef.current) {
      const { scrollLeft, clientWidth } = scrollContainerRef.current;
      const scrollAmount = clientWidth * 0.8; // Desplazar el 80% del ancho visible
      scrollContainerRef.current.scrollTo({
        left: direction === 'left' ? scrollLeft - scrollAmount : scrollLeft + scrollAmount,
        behavior: 'smooth'
      });
    }
  };

  return (
    <section className="max-w-7xl mx-auto px-6 py-12">
      <div className="flex justify-between items-end mb-10 border-b border-gray-100 pb-4">
        <h2 className="text-3xl font-bold text-gray-900">{data.title || 'Productos Destacados'}</h2>
        
        <div className="flex items-center gap-4">
          <Link href="/productos" className="text-accent hover:text-accent/80 font-bold uppercase tracking-wider text-sm flex items-center gap-1 group">
            Ver todo 
            <svg className="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </Link>
        </div>
      </div>
      
      {isCarousel ? (
        <div className="relative">
          {/* Flecha Izquierda */}
          <button 
            onClick={() => scroll('left')}
            className="hidden md:flex absolute -left-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full bg-white border border-gray-200 items-center justify-center text-gray-500 hover:text-gray-900 shadow-[0_4px_20px_rgba(0,0,0,0.08)] transition-all active:scale-95"
          >
            <ChevronLeft className="w-6 h-6" />
          </button>

          {/* Flecha Derecha */}
          <button 
            onClick={() => scroll('right')}
            className="hidden md:flex absolute -right-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full bg-white border border-gray-200 items-center justify-center text-gray-500 hover:text-gray-900 shadow-[0_4px_20px_rgba(0,0,0,0.08)] transition-all active:scale-95"
          >
            <ChevronRight className="w-6 h-6" />
          </button>

          {/* Sombra difuminada para indicar que hay más contenido (móvil) */}
          <div className="absolute top-0 bottom-8 right-0 w-12 bg-gradient-to-l from-background to-transparent z-10 pointer-events-none md:hidden"></div>
          
          <div 
            ref={scrollContainerRef}
            className="flex overflow-x-auto gap-4 sm:gap-6 pt-6 pb-10 -mx-6 px-6 snap-x snap-mandatory hide-scrollbar scroll-smooth"
          >
            {data.products.map((product: any) => (
              <div key={product.id} className="min-w-[280px] sm:min-w-[300px] w-full max-w-[320px] snap-start shrink-0">
                <ProductCard product={product} />
              </div>
            ))}
          </div>
        </div>
      ) : (
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 pt-6">
          {data.products.map((product: any) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      )}
    </section>
  );
}
