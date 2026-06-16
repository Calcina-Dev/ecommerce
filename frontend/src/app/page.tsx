"use client"
import { useEffect, useState } from "react";
import Link from "next/link";
import { ProductCard } from "@/components/ProductCard";
import { ArrowRight, Leaf } from "lucide-react";

export default function Home() {
  const [data, setData] = useState<any>(null);

  useEffect(() => {
    fetch('http://localhost:8000/api/catalog/home')
      .then(res => res.json())
      .then(data => setData(data))
      .catch(err => console.error(err));
  }, []);

  return (
    <div className="min-h-screen bg-transparent">
      {/* Hero Section Moderno */}
      <section className="relative w-full overflow-hidden bg-transparent">
        <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-emerald-100/40 via-background to-background pointer-events-none"></div>
        <div className="max-w-7xl mx-auto px-6 pt-20 pb-24 lg:pt-32 lg:pb-40 relative z-10 flex flex-col lg:flex-row items-center gap-12">
          
          {/* Texto Principal */}
          <div className="flex-1 space-y-8 text-center lg:text-left">
            <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm font-semibold">
              <span className="relative flex h-2 w-2">
                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span className="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
              </span>
              Novedades 2026
            </div>
            
            <h1 className="text-5xl lg:text-7xl font-extrabold tracking-tighter text-gray-900 leading-[1.1]">
              Tu mejor versión <br />
              <span className="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500">
                comienza desde adentro
              </span>
            </h1>
            
            <p className="text-lg text-gray-500 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
              Descubre nuestra selección premium de suplementos y vitaminas respaldadas por la ciencia. Resultados reales para atletas y personas exigentes.
            </p>
            
            <div className="flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start pt-4">
              <Link 
                href="/productos" 
                className="w-full sm:w-auto bg-gray-900 hover:bg-gray-800 text-white px-8 py-4 rounded-2xl font-bold transition-transform active:scale-[0.98] shadow-xl shadow-gray-900/20 flex items-center justify-center gap-2"
              >
                Explorar Catálogo
                <ArrowRight className="w-5 h-5" />
              </Link>
              <div className="flex items-center gap-4 text-sm font-medium text-gray-500">
                <div className="flex -space-x-2">
                  <img className="w-8 h-8 rounded-full border-2 border-white" src="https://i.pravatar.cc/100?img=1" alt="User" />
                  <img className="w-8 h-8 rounded-full border-2 border-white" src="https://i.pravatar.cc/100?img=2" alt="User" />
                  <img className="w-8 h-8 rounded-full border-2 border-white" src="https://i.pravatar.cc/100?img=3" alt="User" />
                </div>
                <span>+2,000 clientes felices</span>
              </div>
            </div>
          </div>

          {/* Imagen Hero Dinámica */}
          <div className="flex-1 relative w-full max-w-lg lg:max-w-none">
            <div className="relative aspect-square">
              {/* Círculo de fondo */}
              <div className="absolute inset-0 bg-gradient-to-tr from-emerald-100 to-teal-50 rounded-full blur-3xl opacity-70 animate-pulse"></div>
              
              <img 
                src="https://images.unsplash.com/photo-1593095948071-474c5cc2989d?q=80&w=800&auto=format&fit=crop" 
                alt="Suplementos Premium" 
                className="relative z-10 w-full h-full object-cover rounded-[3rem] shadow-2xl rotate-3 hover:rotate-0 transition-transform duration-700 ease-out"
              />
              
              {/* Tarjeta Flotante (Glassmorphism) */}
              <div className="absolute -bottom-6 -left-6 z-20 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-4 flex items-center gap-4">
                <div className="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center">
                  <Leaf className="w-6 h-6" />
                </div>
                <div>
                  <p className="text-sm font-bold text-gray-900">100% Natural</p>
                  <p className="text-xs text-gray-500">Ingredientes certificados</p>
                </div>
              </div>
            </div>
          </div>

        </div>
      </section>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-6 py-12 space-y-20">

        {/* Categorías y Productos o Skeleton */}
        {!data ? (
          <div className="space-y-20">
            <div>
              <div className="h-8 animate-shimmer rounded-full w-48 mb-8"></div>
              <div className="flex gap-3">
                {[...Array(4)].map((_, i) => (
                  <div key={i} className="h-12 w-32 animate-shimmer rounded-full"></div>
                ))}
              </div>
            </div>
            <div>
              <div className="flex justify-between items-end mb-10 border-b border-gray-100 pb-4">
                <div className="h-10 animate-shimmer rounded-full w-48"></div>
              </div>
              <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                {[...Array(4)].map((_, i) => (
                  <div key={i} className="animate-shimmer rounded-2xl aspect-[4/5] w-full"></div>
                ))}
              </div>
            </div>
          </div>
        ) : (
          <>
            {/* Categories Pills (Filtros) */}
            {data.categories && data.categories.length > 0 && (
              <section>
                <div className="flex flex-col md:flex-row items-center justify-between gap-6 mb-8">
                  <h2 className="text-2xl font-bold text-gray-900">Compra por Categoría</h2>
                </div>
                <div className="flex flex-wrap gap-3">
                  {data.categories.map((cat: any) => (
                    <Link 
                      key={cat.id} 
                      href={`/productos?category=${cat.id}`}
                      className="group px-6 py-3 bg-white border border-gray-200 rounded-full font-semibold text-sm text-gray-700 hover:border-accent hover:text-accent hover:shadow-md transition-all flex items-center gap-2"
                    >
                      <span className="w-2 h-2 rounded-full bg-gray-300 group-hover:bg-accent transition-colors"></span>
                      {cat.name}
                    </Link>
                  ))}
                </div>
              </section>
            )}

            {/* Featured Products */}
            {data.featured_products && data.featured_products.length > 0 && (
              <section>
                <div className="flex justify-between items-end mb-10 border-b border-gray-100 pb-4">
                  <h2 className="text-3xl font-bold text-gray-900">Tendencias</h2>
                  <Link href="/productos" className="text-accent hover:text-accent/80 font-bold uppercase tracking-wider text-sm flex items-center gap-1 group">
                    Ver todo 
                    <svg className="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path></svg>
                  </Link>
                </div>
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                  {data.featured_products.map((product: any) => (
                    <ProductCard key={product.id} product={product} />
                  ))}
                </div>
              </section>
            )}
          </>
        )}
      </main>

      {/* Value Proposition Bottom */}
      <section className="bg-gray-900 text-white py-20">
        <div className="max-w-7xl mx-auto px-6 text-center space-y-6">
          <h2 className="text-3xl md:text-5xl font-bold">La calidad que tu cuerpo merece</h2>
          <p className="text-gray-400 max-w-2xl mx-auto text-lg">Únete a miles de personas que ya confían en Compra Saludable para alcanzar sus metas físicas y mejorar su calidad de vida.</p>
          <div className="pt-8">
            <Link href="/productos" className="inline-block border-2 border-white text-white hover:bg-white hover:text-gray-900 px-8 py-3 rounded-full font-bold transition-colors">
              Conocer más
            </Link>
          </div>
        </div>
      </section>
    </div>
  );
}
