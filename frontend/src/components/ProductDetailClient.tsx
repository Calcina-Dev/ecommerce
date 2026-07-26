"use client"
import { useEffect, useState, useRef } from "react";
import { createPortal } from "react-dom";
import Image from "next/image";
import Link from "next/link";
import { useCartStore } from "@/store/useCartStore";
import { useFavoriteStore } from "@/store/useFavoriteStore";
import { motion, AnimatePresence } from "framer-motion";
import { Heart, Phone } from "lucide-react";
import { useAuthStore } from "@/store/useAuthStore";

function cleanDescriptionText(text: string | null) {
  if (!text) return "";
  
  let clean = text.replace(/\\r\\n/g, '\n').replace(/\\n/g, '\n').replace(/\\/g, '');
  clean = clean.replace(/\s+data-[a-z-]+="[^"]*"/gi, '');
  clean = clean.replace(/\s+data-[a-z-]+='[^']*'/gi, '');

  if (/<(p|ul|ol|li|strong|b|em|i|h[1-6]|div|br|span|a|table|blockquote|u|del|s)[\s>]/i.test(clean) || clean.includes('</p>') || clean.includes('</div>') || clean.includes('</li>') || clean.includes('</h1>') || clean.includes('</ul>') || clean.includes('</ol>')) {
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
        const chunk = textToType.slice(currentIdx, currentIdx + 4);
        setDisplayedText(prev => prev + chunk);
        currentIdx += 4;
      } else {
        clearInterval(interval);
        setIsGenerating(false);
      }
    }, 10);
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

const isVideoUrl = (url?: string | null) => {
  if (!url) return false;
  const clean = url.split('?')[0].toLowerCase();
  return clean.endsWith('.mp4') || clean.endsWith('.webm') || clean.endsWith('.ogg') || clean.endsWith('.mov') || clean.includes('video/');
};

function ZoomableImage({ src, alt }: { src: string; alt: string }) {
  const [zoomStyle, setZoomStyle] = useState<React.CSSProperties>({});
  const [isZoomed, setIsZoomed] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);

  if (isVideoUrl(src)) {
    return (
      <div className="relative w-full h-full flex items-center justify-center bg-black/5 dark:bg-black/40 overflow-hidden">
        <video 
          src={src} 
          controls 
          autoPlay 
          muted 
          loop 
          playsInline 
          className="w-full h-full object-contain max-h-[550px]"
        />
      </div>
    );
  }

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
  const { isFavorite, toggleItem } = useFavoriteStore();
  const isFav = isFavorite(product.id);

  const user = useAuthStore((state) => state.user);
  const [mounted, setMounted] = useState(false);
  
  useEffect(() => {
    setMounted(true);
  }, []);

  const [isCallModalOpen, setIsCallModalOpen] = useState(false);
  const [callForm, setCallForm] = useState({
    name: user?.name || "",
    phone: user?.phone || "",
    termsAccepted: false
  });
  const [isSubmittingCall, setIsSubmittingCall] = useState(false);

  const handleCallSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!callForm.name || !callForm.phone) {
      import("sonner").then(({ toast }) => toast.error("Por favor completa los datos obligatorios"));
      return;
    }
    if (!callForm.termsAccepted) {
      import("sonner").then(({ toast }) => toast.error("Debes aceptar los términos y condiciones"));
      return;
    }
    setIsSubmittingCall(true);
    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/leads`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          name: callForm.name,
          phone: callForm.phone,
          product_id: product.id?.toString(),
          product_name: product.name,
        }),
      });
      
      if (res.ok) {
        import("sonner").then(({ toast }) => toast.success("¡Solicitud enviada!", { description: "Un asesor te contactará pronto." }));
        setIsCallModalOpen(false);
      } else {
        import("sonner").then(({ toast }) => toast.error("Hubo un error al enviar tu solicitud"));
      }
    } catch (err) {
      import("sonner").then(({ toast }) => toast.error("Error de conexión"));
    } finally {
      setIsSubmittingCall(false);
    }
  };

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
                {images.map((img: string, idx: number) => {
                  const isVideo = isVideoUrl(img);
                  return (
                    <div 
                      key={idx} 
                      onClick={() => setActiveIdx(idx)}
                      className={`relative w-20 h-20 rounded-xl overflow-hidden bg-white dark:bg-white border border-gray-100 dark:border-zinc-800 flex-shrink-0 cursor-pointer transition-all duration-200 p-1.5 ${activeIdx === idx ? 'ring-2 ring-emerald-600 border-emerald-600 scale-95 opacity-100 shadow-md' : 'opacity-60 hover:opacity-100'}`}
                    >
                      {isVideo ? (
                        <div className="w-full h-full bg-slate-900 rounded-lg flex flex-col items-center justify-center text-white relative overflow-hidden">
                          <video src={img} className="absolute inset-0 w-full h-full object-cover opacity-60 pointer-events-none" />
                          <div className="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center shadow z-10">
                            <svg className="w-3.5 h-3.5 ml-0.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                          </div>
                        </div>
                      ) : (
                        <Image src={img} alt="" fill className="object-contain p-1.5" />
                      )}
                    </div>
                  );
                })}
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

            {/* Action Bar (Quantity Selector + Add to Cart / Agotado + Heart button) - Placed before short description */}
            <div className="space-y-6 mb-8">
              <div className="flex items-center gap-3 sm:gap-4">
                {/* Quantity Selector */}
                <div className="flex items-center border border-gray-200 dark:border-zinc-700 rounded-2xl h-14 px-3 bg-white dark:bg-zinc-900 shadow-sm">
                  <button
                    type="button"
                    onClick={() => setQuantity(prev => Math.max(1, prev - 1))}
                    className="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-gray-100 dark:hover:bg-zinc-800 font-bold text-lg text-foreground transition-colors"
                  >
                    -
                  </button>
                  <span className="w-12 text-center font-bold text-base text-foreground">
                    {quantity}
                  </span>
                  <button
                    type="button"
                    onClick={() => setQuantity(prev => prev + 1)}
                    className="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-gray-100 dark:hover:bg-zinc-800 font-bold text-lg text-foreground transition-colors"
                  >
                    +
                  </button>
                </div>

                <motion.button 
                  whileTap={{ scale: 0.95 }}
                  transition={{ type: "spring", stiffness: 500, damping: 30 }}
                  disabled={product.stock !== undefined && product.stock <= 0}
                  className={`flex-1 inline-flex items-center justify-center whitespace-nowrap text-base sm:text-lg font-bold rounded-2xl h-14 transition-all px-4 ${
                    (product.stock !== undefined && product.stock <= 0)
                      ? "bg-gray-300 dark:bg-zinc-700 text-white dark:text-gray-300 cursor-not-allowed pointer-events-none shadow-none"
                      : "bg-slate-900 dark:bg-white text-white dark:text-slate-900 hover:bg-slate-800 dark:hover:bg-gray-100 shadow-lg shadow-slate-900/10"
                  }`}
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
                  {(product.stock !== undefined && product.stock <= 0) ? "Agotado - Sin Stock" : "Agregar al Carrito"}
                </motion.button>

                <motion.button
                  type="button"
                  whileTap={{ scale: 0.8 }}
                  whileHover={{ scale: 1.05 }}
                  onClick={async () => {
                    const added = await toggleItem({
                      id: product.id,
                      name: product.name,
                      price: product.price,
                      image_url: product.primary_image?.image_url || product.images?.[0]?.image_url || null,
                      slug: product.slug,
                    });
                    import("sonner").then(({ toast }) => {
                      if (added) {
                        toast.success("Añadido a tus favoritos ❤️", {
                          description: `${product.name} fue guardado en tu lista de deseos.`,
                        });
                      } else {
                        toast("Eliminado de favoritos", {
                          description: `${product.name} fue quitado de tu lista.`,
                        });
                      }
                    });
                  }}
                  className={`h-14 w-14 rounded-2xl border flex items-center justify-center transition-colors shadow-sm flex-shrink-0 ${
                    isFav 
                      ? "bg-rose-500 border-rose-500 text-white" 
                      : "border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 hover:bg-rose-50 dark:hover:bg-rose-950/30 text-gray-600 dark:text-gray-400 hover:text-rose-500 dark:hover:text-rose-400"
                  }`}
                  title={isFav ? "Quitar de favoritos" : "Añadir a favoritos"}
                >
                  <Heart className={`w-6 h-6 ${isFav ? "fill-white" : ""}`} />
                </motion.button>
              </div>
            </div>
            
            <div className="mt-4">
              <button
                type="button"
                onClick={() => setIsCallModalOpen(true)}
                className="w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-4 rounded-xl transition-colors shadow-sm"
              >
                <Phone className="w-5 h-5" />
                Continuar compra por llamada
              </button>
            </div>



            <div 
              className="text-lg text-foreground/80 mb-6 leading-relaxed prose prose-sm sm:prose-base dark:prose-invert max-w-none"
              dangerouslySetInnerHTML={{ __html: cleanDescriptionText(product.short_description) || "Descripción breve no disponible." }}
            />

            {/* AI Overview Box with Streaming Typewriter Effect */}
            <AIOverviewBox overviewText={product.ai_overview} productName={product.name} />

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

      {mounted && createPortal(
        <AnimatePresence>
          {isCallModalOpen && (
            <div className="fixed inset-0 z-[9999] pointer-events-none flex items-start justify-center pt-4 sm:pt-8 px-4">
              <motion.div 
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                exit={{ opacity: 0 }}
                className="absolute inset-0 bg-black/20 backdrop-blur-sm pointer-events-auto"
                onClick={() => setIsCallModalOpen(false)}
              />
              <motion.div 
                initial={{ y: -100, opacity: 0, scale: 0.95 }}
                animate={{ y: 0, opacity: 1, scale: 1 }}
                exit={{ y: -100, opacity: 0, scale: 0.95 }}
                transition={{ type: "spring", stiffness: 400, damping: 30 }}
                className="bg-white dark:bg-zinc-900 rounded-[24px] p-6 w-full max-w-sm shadow-2xl relative border border-gray-100 dark:border-zinc-800 pointer-events-auto flex flex-col"
              >
                <div className="w-12 h-1.5 bg-gray-200 dark:bg-zinc-700 rounded-full mx-auto mb-4" />
                <button 
                  onClick={() => setIsCallModalOpen(false)}
                  className="absolute top-5 right-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 bg-gray-50 dark:bg-zinc-800 rounded-full p-1"
                >
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
                
                <h3 className="text-xl font-bold text-foreground mb-1 text-center">Te llamamos</h3>
                <p className="text-sm text-muted-foreground mb-6 text-center leading-relaxed">Déjanos tus datos y un asesor te contactará para completar tu pedido.</p>
                
                <form onSubmit={handleCallSubmit} className="space-y-4">
                  <div>
                    <label className="block text-[13px] font-semibold text-foreground mb-1.5 ml-1">Nombre completo</label>
                    <input 
                      type="text" 
                      required
                      value={callForm.name}
                      onChange={e => setCallForm({...callForm, name: e.target.value})}
                      className="w-full rounded-2xl border-0 bg-gray-100 dark:bg-zinc-800/50 px-4 py-3.5 text-sm outline-none focus:ring-2 focus:ring-emerald-500 transition-all text-foreground placeholder:text-gray-400"
                      placeholder="Ej. Juan Pérez"
                    />
                  </div>
                  <div>
                    <label className="block text-[13px] font-semibold text-foreground mb-1.5 ml-1">Teléfono</label>
                    <input 
                      type="tel" 
                      required
                      value={callForm.phone}
                      onChange={e => setCallForm({...callForm, phone: e.target.value})}
                      className="w-full rounded-2xl border-0 bg-gray-100 dark:bg-zinc-800/50 px-4 py-3.5 text-sm outline-none focus:ring-2 focus:ring-emerald-500 transition-all text-foreground placeholder:text-gray-400"
                      placeholder="Ej. 987654321"
                    />
                  </div>
                  <div className="flex items-start gap-2.5 pt-2 px-1">
                    <input 
                      type="checkbox" 
                      id="terms" 
                      required
                      checked={callForm.termsAccepted}
                      onChange={e => setCallForm({...callForm, termsAccepted: e.target.checked})}
                      className="mt-0.5 w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-600 transition-colors cursor-pointer"
                    />
                    <label htmlFor="terms" className="text-xs text-muted-foreground leading-relaxed cursor-pointer">
                      Acepto los términos y el tratamiento de mis datos personales.
                    </label>
                  </div>
                  <button
                    type="submit"
                    disabled={isSubmittingCall}
                    className="w-full mt-4 flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-4 px-4 rounded-2xl transition-all shadow-lg shadow-emerald-600/20 disabled:opacity-70 disabled:shadow-none active:scale-[0.98]"
                  >
                    {isSubmittingCall ? (
                      <svg className="w-5 h-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    ) : "Solicitar llamada"}
                  </button>
                </form>
              </motion.div>
            </div>
          )}
        </AnimatePresence>,
        document.body
      )}
    </div>
  );
}
