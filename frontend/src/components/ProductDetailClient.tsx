"use client"
import { useEffect, useState, useRef } from "react";
import Image from "next/image";
import Link from "next/link";
import { useCartStore } from "@/store/useCartStore";
import { motion } from "framer-motion";

function cleanDescriptionText(text: string | null) {
  if (!text) return "";
  
  let clean = text.replace(/\\r\\n/g, '\n').replace(/\\n/g, '\n').replace(/\\/g, '');
  clean = clean.replace(/\p{Extended_Pictographic}/gu, '');
  clean = clean.replace(/[\u2700-\u27BF]|[\uE000-\uF8FF]|\uD83C[\uDC00-\uDFFF]|\uD83D[\uDC00-\uDFFF]|[\u2011-\u26FF]|\uD83E[\uDD10-\uDDFF]/g, '');
  clean = clean.replace(/\s+data-[a-z-]+="[^"]*"/gi, '');
  clean = clean.replace(/\s+data-[a-z-]+='[^']*'/gi, '');

  if (/<(p|ul|ol|li|strong|b|em|i|h[1-6]|div|br|span|a|table|blockquote)[\s>]/i.test(clean)) {
    return clean;
  }

  const lines = clean.split('\n').map(l => l.trim()).filter(Boolean);
  
  return lines.map(line => {
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

function formatAIText(text: string): string {
  if (!text) return "";
  
  let cleaned = text
    .replace(/^Actuando como un copywriter.*?\n/ig, '')
    .replace(/^Eres un copywriter.*?\n/ig, '')
    .replace(/^\*\*Producto a analizar:\*\*.*?\n/ig, '')
    .replace(/^\*\*Características \/ Descripción:\*\*.*?\n/ig, '')
    .replace(/\uFFFD/g, '')
    .trim();

  cleaned = cleaned.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-emerald-950 dark:text-emerald-200">$1</strong>');
  cleaned = cleaned.replace(/\*(.*?)\*/g, '<strong class="font-bold text-foreground/90">$1</strong>');

  const lines = cleaned.split('\n').map(l => l.trim()).filter(Boolean);
  
  return lines.map((line, idx) => {
    if (line.startsWith('•') || line.startsWith('-') || line.startsWith('*')) {
      let item = line.replace(/^[•*\-]\s*/, '').trim();
      item = item.replace(/^[\uFFFD\?]\s*/, '').trim();
      return `<div class="flex items-start gap-2.5 mb-2 pl-1.5"><span class="text-emerald-500 font-black mt-0.5 select-none">•</span><div class="flex-1 text-foreground/90 leading-relaxed">${item}</div></div>`;
    }

    if (/^(🎯|🧪|🛡️|⚡|✨|PERFIL|EXPLICACIÓN|SEGURIDAD|INGREDIENTES|ADVERTENCIAS|CONSERVACIÓN)/i.test(line) && !line.includes(':')) {
      let cleanTitle = line.replace(/^(🎯|🧪|🛡️|⚡|✨|[\uFFFD\?]|•|-|\*)\s*/g, '').trim();
      
      return `<div class="${idx > 0 ? 'mt-5' : 'mt-1'} mb-2.5 font-extrabold text-xs sm:text-sm uppercase tracking-wider text-emerald-900 dark:text-emerald-300 bg-emerald-500/10 dark:bg-emerald-500/20 px-3 py-1.5 rounded-xl border border-emerald-500/30 shadow-sm flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> <span>${cleanTitle}</span></div>`;
    }

    return `<p class="mb-2.5 text-foreground/80 leading-relaxed pl-1">${line}</p>`;
  }).join('');
}

function AIOverviewBox({ overviewText, productName }: { overviewText?: string; productName: string }) {
  const [displayedText, setDisplayedText] = useState("");
  const [isGenerating, setIsGenerating] = useState(false);
  const [hasStarted, setHasStarted] = useState(false);

  const textToType = overviewText || `PERFIL Y MODO DE EMPLEO
• ¿Para qué sirve?: Respaldo integral para el equilibrio metabólico, energía celular y bienestar general.
• ¿Para quién es ideal?: Adultos con ritmo de vida exigente que buscan apoyo diario y optimización biológica.
• Activos clave por porción: Fórmula de alta biodisponibilidad estandarizada de grado clínico.
• ¿Cómo tomarlo?: Tomar 1 porción diaria con abundante agua, preferentemente junto a una comida principal para facilitar la digestión y absorción.
• Certificaciones y dietas: 100% Vegano • Sin Gluten • Libre de OGM • Calidad GMP Auditada.

EXPLICACIÓN DE INGREDIENTES Y SINERGIA
• Mecanismo de acción: Los ingredientes activos actúan a nivel celular como mensajeros secundarios, favoreciendo la señalización metabólica y la absorción de nutrientes.
• Sinergia de la fórmula: Combinación equilibrada en proporciones fisiológicas exactas para potenciar la eficacia mutua y evitar la saturación de los receptores.
• Precaución: Monitorear la tolerancia individual durante los primeros 3 días de uso.

SEGURIDAD, ADVERTENCIAS Y CONSERVACIÓN
• Advertencia médica: Consultar con un profesional de la salud antes de usar si está embarazada, en período de lactancia, toma medicamentos o tiene alguna condición médica.
• Conservación: Almacenar en un lugar fresco, seco y alejado de la luz solar directa para preservar la potencia del lote.`;

  const startStreaming = () => {
    setIsGenerating(true);
    setHasStarted(true);
    setDisplayedText("");
    
    let currentIdx = 0;
    const interval = setInterval(() => {
      if (currentIdx < textToType.length) {
        setDisplayedText(prev => prev + textToType.charAt(currentIdx));
        currentIdx++;
      } else {
        clearInterval(interval);
        setIsGenerating(false);
      }
    }, 15);
  };

  useEffect(() => {
    const timer = setTimeout(() => {
      startStreaming();
    }, 600);
    return () => clearTimeout(timer);
  }, [textToType]);

  return (
    <div className="mb-8 p-4 sm:p-5 rounded-2xl bg-gradient-to-br from-emerald-500/10 via-teal-500/5 to-cyan-500/10 border border-emerald-500/30 shadow-sm relative overflow-hidden group">
      <div className="absolute -right-10 -top-10 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none" />
      
      <div className="flex items-center justify-between mb-3 border-b border-emerald-500/20 pb-2.5">
        <div className="flex items-center gap-2">
          <span className="flex h-2.5 w-2.5 relative">
            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span className="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
          </span>
          <span className="text-xs sm:text-sm font-black tracking-wider uppercase text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5">
            Resumen de Especificaciones
          </span>
        </div>
        <button 
          onClick={startStreaming}
          disabled={isGenerating}
          className="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 underline decoration-dotted flex items-center gap-1 transition-opacity disabled:opacity-50"
          title="Regenerar resumen"
        >
          <svg className={`w-3.5 h-3.5 ${isGenerating ? 'animate-spin' : ''}`} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v-5"/></svg>
          {isGenerating ? "Sintetizando..." : "Repetir"}
        </button>
      </div>

      <div className="min-h-[70px] text-xs sm:text-sm text-foreground/90 font-medium leading-relaxed font-sans">
        {!hasStarted ? (
          <div className="flex items-center gap-2 text-muted-foreground italic py-2">
            <svg className="w-4 h-4 animate-spin text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Analizando ficha clínica e ingredientes en vivo...
          </div>
        ) : (
          <div 
            className="space-y-1.5"
            dangerouslySetInnerHTML={{ 
              __html: formatAIText(displayedText) + (isGenerating ? '<span class="inline-block w-1.5 h-3.5 bg-emerald-500 ml-1 animate-pulse align-middle"></span>' : '') 
            }}
          />
        )}
      </div>
      <div className="mt-3 pt-2.5 border-t border-emerald-500/10 flex items-center justify-between text-[10px] text-muted-foreground font-semibold">
        <span className="flex items-center gap-1 text-emerald-700 dark:text-emerald-400">⚡ Generado por CompraSaludable-IA</span>
      </div>
    </div>
  );
}

function ZoomableImage({ src, alt }: { src: string; alt: string }) {
  const [zoomStyle, setZoomStyle] = useState<React.CSSProperties>({});
  const [isZoomed, setIsZoomed] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);

  const handleMouseMove = (e: React.MouseEvent<HTMLDivElement>) => {
    if (!containerRef.current) return;
    const { left, top, width, height } = containerRef.current.getBoundingClientRect();
    const x = ((e.clientX - left) / width) * 100;
    const y = ((e.clientY - top) / height) * 100;

    setZoomStyle({
      transformOrigin: `${x}% ${y}%`,
      transform: "scale(2.4)",
    });
    setIsZoomed(true);
  };

  const handleMouseLeave = () => {
    setIsZoomed(false);
    setZoomStyle({
      transformOrigin: "center center",
      transform: "scale(1)",
    });
  };

  return (
    <div 
      ref={containerRef}
      onMouseMove={handleMouseMove}
      onMouseLeave={handleMouseLeave}
      className="relative w-full h-full cursor-zoom-in overflow-hidden flex items-center justify-center select-none"
    >
      <div 
        className="relative w-full h-full transition-transform duration-150 ease-out pointer-events-none"
        style={zoomStyle}
      >
        <Image 
          src={src} 
          alt={alt}
          fill
          priority
          className="object-contain p-6 sm:p-10"
        />
      </div>
      
      {!isZoomed && (
        <div className="absolute bottom-4 left-1/2 -translate-x-1/2 bg-background/85 dark:bg-zinc-900/85 backdrop-blur-md px-3.5 py-1.5 rounded-full text-[11px] font-semibold border shadow-sm z-10 text-muted-foreground flex items-center gap-1.5 pointer-events-none transition-opacity duration-300">
          <svg className="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="M11 8v6"/><path d="M8 11h6"/></svg>
          <span>Pasa el puntero para ampliar</span>
        </div>
      )}
    </div>
  );
}

export default function ProductDetailClient({ product }: { product: any }) {
  const [activeIdx, setActiveIdx] = useState(0);
  const [quantity, setQuantity] = useState(1);
  const addItem = useCartStore((state) => state.addItem);

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
            <div className="relative aspect-square rounded-3xl overflow-hidden bg-white dark:bg-white border border-gray-100 dark:border-zinc-800/80 group shadow-sm">
              <ZoomableImage 
                src={images[activeIdx] || images[0]} 
                alt={product.name}
              />
              {images.length > 1 && (
                <>
                  <button 
                    onClick={() => setActiveIdx((prev) => (prev > 0 ? prev - 1 : images.length - 1))}
                    className="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-background/80 backdrop-blur-md border shadow-md flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all hover:scale-105 hover:bg-background z-20 font-bold"
                  >
                    ←
                  </button>
                  <button 
                    onClick={() => setActiveIdx((prev) => (prev < images.length - 1 ? prev + 1 : 0))}
                    className="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-background/80 backdrop-blur-md border shadow-md flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all hover:scale-105 hover:bg-background z-20 font-bold"
                  >
                    →
                  </button>
                  <div className="absolute top-4 right-4 bg-background/80 backdrop-blur-md px-3 py-1 rounded-full text-xs font-semibold border shadow-sm z-20">
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
            
            <div className="flex items-center gap-4 mb-6">
              <span className="text-3xl font-bold tracking-tight">S/ {parseFloat(product.price).toFixed(2)}</span>
              {product.compare_at_price && (
                <span className="text-xl text-muted-foreground line-through">S/ {parseFloat(product.compare_at_price).toFixed(2)}</span>
              )}
            </div>

            <div 
              className="text-lg text-foreground/80 mb-6 leading-relaxed prose prose-sm sm:prose-base dark:prose-invert max-w-none"
              dangerouslySetInnerHTML={{ __html: cleanDescriptionText(product.short_description) || "Descripción breve no disponible." }}
            />

            {/* AI Overview Box with Streaming Typewriter Effect */}
            <AIOverviewBox overviewText={product.ai_overview} productName={product.name} />

            <div className="space-y-6 mb-8">
              <div className="flex items-center gap-4">
                {/* Quantity Selector */}
                <div className="flex items-center border border-border/80 rounded-2xl h-14 px-3 bg-muted/20">
                  <button
                    type="button"
                    onClick={() => setQuantity(prev => Math.max(1, prev - 1))}
                    className="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-muted font-bold text-lg text-foreground transition-colors"
                  >
                    -
                  </button>
                  <span className="w-12 text-center font-bold text-base text-foreground">
                    {quantity}
                  </span>
                  <button
                    type="button"
                    onClick={() => setQuantity(prev => prev + 1)}
                    className="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-muted font-bold text-lg text-foreground transition-colors"
                  >
                    +
                  </button>
                </div>

                <motion.button 
                  whileTap={{ scale: 0.95 }}
                  transition={{ type: "spring", stiffness: 500, damping: 30 }}
                  className="flex-1 bg-primary text-primary-foreground hover:bg-primary/90 inline-flex items-center justify-center whitespace-nowrap text-lg font-bold ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 rounded-2xl h-14 shadow-lg shadow-primary/20"
                  onClick={() => {
                    addItem({
                      id: product.id,
                      name: product.name,
                      price: product.price,
                      image_url: product.primary_image?.image_url || product.images?.[0]?.image_url || null,
                      quantity: quantity,
                      slug: product.slug
                    });
                    import("sonner").then(({ toast }) => {
                      toast.success("Producto agregado", {
                        description: `${quantity}x ${product.name} en tu carrito.`,
                        icon: <svg className="w-4 h-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                      });
                    });
                  }}
                >
                  Agregar al Carrito
                </motion.button>
              </div>
              
              {/* Premium Clinical Trust Signals (Configurable & Editable per product) */}
              {(() => {
                const activeBadges = [
                  product.show_gmp_badge !== false && (
                    <div key="gmp" className="flex flex-col items-center justify-center text-center p-3 rounded-2xl bg-muted/30 border border-border/60">
                      <span className="text-[11px] font-extrabold tracking-wider uppercase text-foreground mb-0.5">
                        {product.badge_1_title || "Laboratorio"}
                      </span>
                      <span className="text-[11px] text-muted-foreground font-medium">
                        {product.badge_1_subtitle || "Grado Clínico GMP"}
                      </span>
                    </div>
                  ),
                  product.show_fefo_badge !== false && (
                    <div key="fefo" className="flex flex-col items-center justify-center text-center p-3 rounded-2xl bg-muted/30 border border-border/60">
                      <span className="text-[11px] font-extrabold tracking-wider uppercase text-foreground mb-0.5">
                        {product.badge_2_title || "Trazabilidad"}
                      </span>
                      <span className="text-[11px] text-muted-foreground font-medium">
                        {product.badge_2_subtitle || "Lote Auditado FEFO"}
                      </span>
                    </div>
                  ),
                  product.show_shipping_badge !== false && (
                    <div key="shipping" className="flex flex-col items-center justify-center text-center p-3 rounded-2xl bg-muted/30 border border-border/60">
                      <span className="text-[11px] font-extrabold tracking-wider uppercase text-foreground mb-0.5">
                        {product.badge_3_title || "Despacho"}
                      </span>
                      <span className="text-[11px] text-muted-foreground font-medium">
                        {product.badge_3_subtitle || "Envío Seguro Nacional"}
                      </span>
                    </div>
                  ),
                ].filter(Boolean);

                if (activeBadges.length === 0) return null;

                const gridCols = activeBadges.length === 1 ? 'grid-cols-1' : activeBadges.length === 2 ? 'grid-cols-2' : 'grid-cols-3';

                return (
                  <div className={`grid ${gridCols} gap-3 pt-6 border-t border-border/60 mt-6 mb-8`}>
                    {activeBadges}
                  </div>
                );
              })()}
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
