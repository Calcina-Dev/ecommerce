"use client"
import { useEffect, useState } from "react";
import Image from "next/image";
import Link from "next/link";
import { useParams } from "next/navigation";
import { Button } from "@/components/ui/button";
import { useCartStore } from "@/store/useCartStore";
import { motion } from "framer-motion";

export default function ProductDetailPage() {
  const params = useParams();
  const slug = params.slug;
  const [product, setProduct] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const addItem = useCartStore((state) => state.addItem);

  useEffect(() => {
    fetch(`http://localhost:8000/api/catalog/products/${slug}`)
      .then(res => res.json())
      .then(data => {
        setProduct(data);
        setLoading(false);
      })
      .catch(err => {
        console.error(err);
        setLoading(false);
      });
  }, [slug]);

  if (loading) {
    return (
      <div className="bg-background min-h-screen">
        <div className="max-w-6xl mx-auto px-6 sm:px-12 py-12">
          <div className="h-4 bg-muted rounded-full w-48 mb-8 animate-pulse"></div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-20">
            {/* Gallery Skeleton */}
            <div className="space-y-4">
              <div className="aspect-square bg-muted rounded-3xl animate-pulse"></div>
              <div className="flex gap-4">
                {[...Array(4)].map((_, i) => (
                  <div key={i} className="w-20 h-20 bg-muted rounded-xl animate-pulse"></div>
                ))}
              </div>
            </div>
            {/* Details Skeleton */}
            <div className="flex flex-col justify-center space-y-6">
              <div className="h-4 bg-muted rounded-full w-24 animate-pulse"></div>
              <div className="h-12 bg-muted rounded-2xl w-3/4 animate-pulse"></div>
              <div className="h-8 bg-muted rounded-xl w-32 animate-pulse"></div>
              <div className="space-y-2">
                <div className="h-4 bg-muted rounded-full w-full animate-pulse"></div>
                <div className="h-4 bg-muted rounded-full w-5/6 animate-pulse"></div>
                <div className="h-4 bg-muted rounded-full w-4/6 animate-pulse"></div>
              </div>
              <div className="h-14 bg-muted rounded-2xl w-full animate-pulse mt-8"></div>
            </div>
          </div>
        </div>
      </div>
    );
  }

  if (!product) {
    return <div className="max-w-6xl mx-auto px-6 py-12 text-center">Producto no encontrado.</div>;
  }

  const images = product.images?.length > 0 
    ? product.images.map((img: any) => img.image_url.startsWith('http') ? img.image_url : `http://localhost:8000/storage/${img.image_url}`)
    : ["https://images.unsplash.com/photo-1584308666744-24d5e47ac9db?q=80&w=600&auto=format&fit=crop"];

  return (
    <div className="bg-background min-h-screen">
      <div className="max-w-6xl mx-auto px-6 sm:px-12 py-12">
        <nav className="text-sm text-muted-foreground mb-8">
          <Link href="/" className="hover:text-foreground">Inicio</Link>
          <span className="mx-2">/</span>
          <Link href="/productos" className="hover:text-foreground">Catálogo</Link>
          <span className="mx-2">/</span>
          <span className="text-foreground">{product.name}</span>
        </nav>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-20">
          {/* Gallery */}
          <div className="space-y-4">
            <div className="relative aspect-square rounded-3xl overflow-hidden bg-muted border">
              <Image 
                src={images[0]} 
                alt={product.name}
                fill
                className="object-cover"
              />
            </div>
            {images.length > 1 && (
              <div className="flex gap-4 overflow-x-auto pb-2">
                {images.map((img: string, idx: number) => (
                  <div key={idx} className="relative w-20 h-20 rounded-xl overflow-hidden bg-muted border flex-shrink-0 cursor-pointer">
                    <Image src={img} alt="" fill className="object-cover" />
                  </div>
                ))}
              </div>
            )}
          </div>

          {/* Details */}
          <div className="flex flex-col justify-center">
            {product.brand && (
              <span className="text-sm font-bold text-muted-foreground uppercase tracking-wider mb-2 block">
                {product.brand.name}
              </span>
            )}
            <h1 className="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight mb-4">{product.name}</h1>
            
            {/* Rating Stars (Trust Signal) */}
            <div className="flex items-center gap-2 mb-6">
              <div className="flex items-center">
                {[...Array(5)].map((_, i) => (
                  <svg key={i} className={`w-5 h-5 ${i < 4 ? 'text-amber-400 fill-amber-400' : 'text-gray-300 fill-gray-300'}`} viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                ))}
              </div>
              <span className="text-sm font-medium text-muted-foreground underline decoration-dashed underline-offset-4 cursor-pointer">4.8 (124 reseñas)</span>
            </div>
            
            <div className="flex items-center gap-4 mb-6">
              <span className="text-3xl font-bold tracking-tight">S/ {parseFloat(product.price).toFixed(2)}</span>
              {product.compare_at_price && (
                <span className="text-xl text-muted-foreground line-through">S/ {parseFloat(product.compare_at_price).toFixed(2)}</span>
              )}
            </div>

            <p className="text-lg text-foreground/80 mb-8 leading-relaxed">
              {product.short_description || "Descripción breve no disponible."}
            </p>

            <div className="space-y-6 mb-8">
              <div className="flex items-center gap-4">
                <motion.button 
                  whileTap={{ scale: 0.95 }}
                  transition={{ type: "spring", stiffness: 500, damping: 30 }}
                  className="w-full bg-primary text-primary-foreground hover:bg-primary/90 inline-flex items-center justify-center whitespace-nowrap text-lg font-bold ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 rounded-2xl h-14 shadow-lg shadow-primary/20"
                  onClick={() => {
                    addItem({
                      id: product.id,
                      name: product.name,
                      price: product.price,
                      image_url: product.primary_image?.image_url || product.images?.[0]?.image_url || null,
                      quantity: 1,
                      slug: product.slug
                    });
                    import("sonner").then(({ toast }) => {
                      toast.success("Producto agregado", {
                        description: `${product.name} está en tu carrito.`,
                        icon: <svg className="w-4 h-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                      });
                    });
                  }}
                >
                  Agregar al Carrito
                </motion.button>
              </div>
              
              {/* Trust Signals */}
              <div className="grid grid-cols-2 md:grid-cols-3 gap-4 pt-4 border-t">
                <div className="flex flex-col items-center justify-center text-center gap-2 text-muted-foreground">
                  <div className="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  </div>
                  <span className="text-xs font-medium">Calidad Garantizada</span>
                </div>
                <div className="flex flex-col items-center justify-center text-center gap-2 text-muted-foreground">
                  <div className="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                  </div>
                  <span className="text-xs font-medium">Pago Seguro</span>
                </div>
                <div className="flex flex-col items-center justify-center text-center gap-2 text-muted-foreground col-span-2 md:col-span-1">
                  <div className="w-10 h-10 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                  </div>
                  <span className="text-xs font-medium">Envío a todo el país</span>
                </div>
              </div>
            </div>

            <div className="prose prose-sm sm:prose-base dark:prose-invert">
              <h3 className="text-xl font-semibold mb-2">Detalles del Producto</h3>
              <div dangerouslySetInnerHTML={{ __html: product.description || "Sin descripción detallada." }} />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
