"use client"
import { useEffect, useState } from "react";
import Link from "next/link";
import { ProductCard } from "@/components/ProductCard";

export default function Home() {
  const [data, setData] = useState<any>(null);

  useEffect(() => {
    fetch('http://localhost:8000/api/catalog/home')
      .then(res => res.json())
      .then(data => setData(data))
      .catch(err => console.error(err));
  }, []);

  return (
    <div className="min-h-screen bg-background">
      {/* Hero Section */}
      <section className="bg-primary text-primary-foreground py-20 px-6 sm:px-12 md:py-32">
        <div className="max-w-5xl mx-auto text-center space-y-8">
          <h1 className="text-4xl md:text-6xl font-bold tracking-tight">
            Vitaminas y Suplementos <br /> de Alta Calidad
          </h1>
          <p className="text-lg md:text-xl text-primary-foreground/80 max-w-2xl mx-auto">
            Potencia tu salud con nuestra selección premium. Encuentra lo que necesitas para tu bienestar diario.
          </p>
          <div className="pt-4">
            <Link 
              href="/productos" 
              className="bg-background text-foreground px-8 py-4 rounded-2xl font-medium text-lg hover:bg-muted transition-colors inline-block"
            >
              Explorar Catálogo
            </Link>
          </div>
        </div>
      </section>

      <main className="max-w-7xl mx-auto px-6 sm:px-12 py-16 space-y-24">
        {/* Categories */}
        {data?.categories && data.categories.length > 0 && (
          <section>
            <h2 className="text-2xl font-bold mb-8">Categorías Principales</h2>
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
              {data.categories.map((cat: any) => (
                <Link 
                  key={cat.id} 
                  href={`/productos?category=${cat.id}`}
                  className="bg-card border rounded-2xl p-6 text-center hover:border-primary transition-colors hover:shadow-sm"
                >
                  <span className="font-medium">{cat.name}</span>
                </Link>
              ))}
            </div>
          </section>
        )}

        {/* Featured Products */}
        {data?.featured_products && data.featured_products.length > 0 && (
          <section>
            <div className="flex justify-between items-end mb-8">
              <h2 className="text-2xl font-bold">Destacados</h2>
              <Link href="/productos" className="text-muted-foreground hover:text-foreground text-sm font-medium">
                Ver todos →
              </Link>
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {data.featured_products.map((product: any) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>
          </section>
        )}
      </main>
    </div>
  );
}
