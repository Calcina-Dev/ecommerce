"use client"
import { useEffect, useState } from "react";
import Link from "next/link";
import { ProductCard } from "@/components/ProductCard";
import { KowalskiShowcase } from "@/components/KowalskiShowcase";

export default function Home() {
  const [data, setData] = useState<any>(null);

  useEffect(() => {
    fetch('http://localhost:8000/api/catalog/home')
      .then(res => res.json())
      .then(data => setData(data))
      .catch(err => console.error(err));
  }, []);

  return (
    <div className="min-h-screen bg-white">
      {/* Hero Banner Bexo-style */}
      <section className="relative w-full h-[500px] md:h-[600px] bg-gray-50 overflow-hidden">
        {/* Background Image/Pattern */}
        <div className="absolute inset-0 z-0">
          <img 
            src="https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=2000&auto=format&fit=crop" 
            alt="Fitness & Health" 
            className="w-full h-full object-cover opacity-90 object-center mix-blend-multiply"
          />
          <div className="absolute inset-0 bg-gradient-to-r from-gray-900/90 via-gray-900/70 to-transparent"></div>
        </div>
        
        <div className="relative z-10 max-w-7xl mx-auto px-6 h-full flex flex-col justify-center">
          <div className="max-w-2xl text-white space-y-6">
            <span className="inline-block px-4 py-1.5 bg-accent text-white text-xs font-bold tracking-widest uppercase rounded-full">
              Novedades 2026
            </span>
            <h1 className="text-4xl md:text-6xl font-extrabold leading-tight tracking-tight">
              Lleva tu rendimiento al siguiente nivel
            </h1>
            <p className="text-lg text-gray-200 md:text-xl max-w-lg font-light">
              Descubre nuestra selección premium de suplementos y vitaminas importadas. Resultados reales para atletas reales.
            </p>
            <div className="pt-4 flex gap-4">
              <Link 
                href="/productos" 
                className="bg-accent hover:bg-accent/90 text-white px-8 py-4 rounded-xl font-bold transition-all shadow-[0_0_20px_rgba(101,32,130,0.3)] hover:shadow-[0_0_30px_rgba(101,32,130,0.5)] flex items-center gap-2"
              >
                Comprar Ahora
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
              </Link>
            </div>
          </div>
        </div>
      </section>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-6 py-12 space-y-20">
        
        <KowalskiShowcase />

        {/* Categories Pills (Filtros) */}
        {data?.categories && data.categories.length > 0 && (
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
        {data?.featured_products && data.featured_products.length > 0 && (
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
