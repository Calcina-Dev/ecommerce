"use client"
import { useEffect, useState } from "react";
import Image from "next/image";
import Link from "next/link";
import { useParams } from "next/navigation";
import { Button } from "@/components/ui/button";
import { useCartStore } from "@/store/useCartStore";
import { motion } from "framer-motion";

function cleanDescriptionText(text: string | null) {
  if (!text) return "";
  
  // Strip literal escaped newlines and backslashes
  let clean = text.replace(/\\r\\n/g, '\n').replace(/\\n/g, '\n').replace(/\\/g, '');
  
  // Strip ALL Unicode emojis, pictographs, checkmarks, and AI clipart symbols
  clean = clean.replace(/\p{Extended_Pictographic}/gu, '');
  clean = clean.replace(/[\u2700-\u27BF]|[\uE000-\uF8FF]|\uD83C[\uDC00-\uDFFF]|\uD83D[\uDC00-\uDFFF]|[\u2011-\u26FF]|\uD83E[\uDD10-\uDDFF]/g, '');

  // Strip data-start, data-end and other data attributes from copy-pasting
  clean = clean.replace(/\s+data-[a-z-]+="[^"]*"/gi, '');
  clean = clean.replace(/\s+data-[a-z-]+='[^']*'/gi, '');

  // If it already contains HTML tags like <p>, <ul>, <ol>, <li>, <strong>, <b>, <h3>, <h4>, <div>, <br>
  if (/<(p|ul|ol|li|strong|b|em|i|h[1-6]|div|br|span|a|table|blockquote)[\s>]/i.test(clean)) {
    return clean;
  }

  const lines = clean.split('\n').map(l => l.trim()).filter(Boolean);
  
  return lines.map(line => {
    // Clean bullet leftovers or odd spacing before colons
    let item = line.replace(/^[-•*+>]\s*/, '').trim();
    item = item.replace(/\s+:/g, ':').trim();

    if (line.startsWith('-') || line.startsWith('•') || line.startsWith('*')) {
      return `<li class="ml-5 list-disc marker:text-emerald-600 font-medium mb-1.5 text-foreground/90">${item}</li>`;
    }
    if (item.includes(':') && item.length < 120) {
      const [title, ...desc] = item.split(':');
      const descText = desc.join(':').trim();
      if (descText) {
        return `<div class="mb-3 leading-relaxed"><span class="font-bold text-foreground block sm:inline">${title}:</span> <span class="text-muted-foreground">${descText}</span></div>`;
      }
      return `<h4 class="font-bold text-foreground mt-6 mb-2 tracking-tight text-base border-b pb-1">${title}:</h4>`;
    }
    return `<p class="mb-3 leading-relaxed text-muted-foreground">${item}</p>`;
  }).join('');
}

export default function ProductDetailPage() {
  const params = useParams();
  const slug = params.slug;
  const [product, setProduct] = useState<any>(null);
  const [activeIdx, setActiveIdx] = useState(0);
  const [loading, setLoading] = useState(true);
  const addItem = useCartStore((state) => state.addItem);

  useEffect(() => {
    fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/catalog/products/${slug}`)
      .then(res => res.json())
      .then(data => {
        setProduct(data);
        setActiveIdx(0);
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
    ? product.images.map((img: any) => img.image_url.startsWith('http') ? img.image_url : `${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/storage/${img.image_url}`)
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
            <div className="relative aspect-square rounded-3xl overflow-hidden bg-white dark:bg-white border border-gray-100 dark:border-zinc-800/80 group shadow-sm p-4 sm:p-8">
              <Image 
                src={images[activeIdx] || images[0]} 
                alt={product.name}
                fill
                className="object-contain p-6 sm:p-10 transition-all duration-300"
              />
              {images.length > 1 && (
                <>
                  <button 
                    onClick={() => setActiveIdx((prev) => (prev > 0 ? prev - 1 : images.length - 1))}
                    className="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-background/80 backdrop-blur-md border shadow-md flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all hover:scale-105 hover:bg-background z-10 font-bold"
                  >
                    ←
                  </button>
                  <button 
                    onClick={() => setActiveIdx((prev) => (prev < images.length - 1 ? prev + 1 : 0))}
                    className="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-background/80 backdrop-blur-md border shadow-md flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all hover:scale-105 hover:bg-background z-10 font-bold"
                  >
                    →
                  </button>
                  <div className="absolute top-4 right-4 bg-background/80 backdrop-blur-md px-3 py-1 rounded-full text-xs font-semibold border shadow-sm z-10">
                    {activeIdx + 1} / {images.length}
                  </div>
                </>
              )}
            </div>
            {images.length > 1 && (
              <div className="flex gap-4 overflow-x-auto pb-2 scrollbar-none">
                {images.map((img: string, idx: number) => (
                  <div 
                    key={idx} 
                    onClick={() => setActiveIdx(idx)}
                    className={`relative w-20 h-20 rounded-xl overflow-hidden bg-white dark:bg-white border border-gray-100 dark:border-zinc-800 flex-shrink-0 cursor-pointer transition-all duration-200 p-1.5 ${activeIdx === idx ? 'ring-2 ring-emerald-600 border-emerald-600 scale-95 opacity-100 shadow-md' : 'opacity-60 hover:opacity-100'}`}
                  >
                    <Image src={img} alt="" fill className="object-contain p-1.5" />
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

            <div 
              className="text-lg text-foreground/80 mb-8 leading-relaxed prose prose-sm sm:prose-base dark:prose-invert max-w-none"
              dangerouslySetInnerHTML={{ __html: cleanDescriptionText(product.short_description) || "Descripción breve no disponible." }}
            />

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
              
              {/* Premium Clinical Trust Signals */}
              <div className="grid grid-cols-3 gap-3 pt-6 border-t border-border/60 mt-6 mb-8">
                <div className="flex flex-col items-center justify-center text-center p-3 rounded-2xl bg-muted/30 border border-border/60">
                  <span className="text-[11px] font-extrabold tracking-wider uppercase text-foreground mb-0.5">Laboratorio</span>
                  <span className="text-[11px] text-muted-foreground font-medium">Grado Clínico GMP</span>
                </div>
                <div className="flex flex-col items-center justify-center text-center p-3 rounded-2xl bg-muted/30 border border-border/60">
                  <span className="text-[11px] font-extrabold tracking-wider uppercase text-foreground mb-0.5">Trazabilidad</span>
                  <span className="text-[11px] text-muted-foreground font-medium">Lote Auditado FEFO</span>
                </div>
                <div className="flex flex-col items-center justify-center text-center p-3 rounded-2xl bg-muted/30 border border-border/60">
                  <span className="text-[11px] font-extrabold tracking-wider uppercase text-foreground mb-0.5">Despacho</span>
                  <span className="text-[11px] text-muted-foreground font-medium">Envío Seguro Nacional</span>
                </div>
              </div>
            </div>

            <div className="prose prose-sm sm:prose-base dark:prose-invert max-w-none">
              <h3 className="text-xl font-bold tracking-tight mb-4 text-foreground">Información Clínica y Beneficios</h3>
              <div 
                className="text-sm sm:text-base leading-relaxed"
                dangerouslySetInnerHTML={{ __html: cleanDescriptionText(product.description) || "Sin descripción clínica detallada." }} 
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
