"use client";

import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { toast } from "sonner";
import { Drawer } from "vaul";
import { Button } from "@/components/ui/button";
import { CheckCircle2, Star, Sparkles, BellRing } from "lucide-react";

export function KowalskiShowcase() {
  const [activeTab, setActiveTab] = useState("sonner");
  const [isLiked, setIsLiked] = useState(false);

  return (
    <section className="py-12 px-4 max-w-5xl mx-auto">
      <div className="text-center mb-10">
        <div className="inline-flex items-center justify-center p-3 bg-primary/10 rounded-full mb-4">
          <Sparkles className="w-6 h-6 text-primary" />
        </div>
        <h2 className="text-3xl font-bold tracking-tight mb-2">Micro-interacciones Premium</h2>
        <p className="text-muted-foreground text-lg max-w-2xl mx-auto">
          Experimenta las físicas de resorte (springs), modales con escalado de fondo (Vaul) y notificaciones de alto rendimiento (Sonner).
        </p>
      </div>

      <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        {/* Card 1: Sonner Toasts */}
        <motion.div 
          whileHover={{ y: -5 }}
          className="p-6 bg-card rounded-2xl border shadow-sm flex flex-col items-center text-center gap-4"
        >
          <div className="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mb-2">
            <BellRing className="w-6 h-6" />
          </div>
          <h3 className="font-semibold text-xl">Notificaciones Sonner</h3>
          <p className="text-sm text-muted-foreground">Toasts rápidos y apilables con física de aceleración.</p>
          <Button 
            onClick={() => toast.success("¡Producto agregado exitosamente!", {
              description: "Tu carrito ha sido actualizado.",
              icon: <CheckCircle2 className="w-4 h-4 text-emerald-500" />
            })}
            className="mt-auto w-full"
          >
            Lanzar Toast
          </Button>
        </motion.div>

        {/* Card 2: Vaul Drawer */}
        <motion.div 
          whileHover={{ y: -5 }}
          className="p-6 bg-card rounded-2xl border shadow-sm flex flex-col items-center text-center gap-4"
        >
          <div className="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 mb-2">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 15H20V21C20 21.5523 19.5523 22 19 22H5C4.44772 22 4 21.5523 4 21V15Z" fill="currentColor"/><path d="M4 13V9C4 8.44772 4.44772 8 5 8H19C19.5523 8 20 8.44772 20 9V13H4Z" fill="currentColor"/><path d="M10 4C10 3.44772 10.4477 3 11 3H13C13.5523 3 14 3.44772 14 4V6H10V4Z" fill="currentColor"/></svg>
          </div>
          <h3 className="font-semibold text-xl">Vaul Drawer</h3>
          <p className="text-sm text-muted-foreground">Panel inferior que empuja todo el fondo de la página web hacia atrás 3D.</p>
          
          <Drawer.Root shouldScaleBackground>
            <Drawer.Trigger asChild>
              <Button variant="secondary" className="mt-auto w-full">Abrir Cajón (Drawer)</Button>
            </Drawer.Trigger>
            <Drawer.Portal>
              <Drawer.Overlay className="fixed inset-0 bg-black/40 z-50" />
              <Drawer.Content className="bg-background flex flex-col rounded-t-[10px] h-[50vh] mt-24 fixed bottom-0 left-0 right-0 z-50">
                <div className="p-4 bg-background rounded-t-[10px] flex-1">
                  <div className="mx-auto w-12 h-1.5 flex-shrink-0 rounded-full bg-muted mb-8" />
                  <div className="max-w-md mx-auto">
                    <Drawer.Title className="font-medium mb-4 text-2xl">Diseño inmersivo</Drawer.Title>
                    <p className="text-muted-foreground mb-6">
                      Este cajón empuja sutilmente toda la aplicación web hacia atrás, reduciendo su escala y oscureciéndola, creando foco total en esta acción.
                    </p>
                    <Drawer.Close asChild>
                      <Button className="w-full">Entendido</Button>
                    </Drawer.Close>
                  </div>
                </div>
              </Drawer.Content>
            </Drawer.Portal>
          </Drawer.Root>
        </motion.div>

        {/* Card 3: Framer Motion Layout */}
        <motion.div 
          whileHover={{ y: -5 }}
          className="p-6 bg-card rounded-2xl border shadow-sm flex flex-col items-center text-center gap-4"
        >
          <div className="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 mb-2">
            <Star className="w-6 h-6" />
          </div>
          <h3 className="font-semibold text-xl">Físicas de Resorte</h3>
          <p className="text-sm text-muted-foreground">Animaciones basadas en "Springs" en vez de curvas genéricas.</p>
          
          <motion.button
            onClick={() => setIsLiked(!isLiked)}
            whileTap={{ scale: 0.8 }}
            transition={{ type: "spring", stiffness: 400, damping: 17 }}
            className={`mt-auto w-16 h-16 rounded-full flex items-center justify-center border-2 ${isLiked ? 'bg-amber-100 border-amber-500 text-amber-500' : 'bg-transparent border-border text-muted-foreground'}`}
          >
            <Star className={`w-8 h-8 ${isLiked ? 'fill-amber-500' : ''}`} />
          </motion.button>
        </motion.div>

      </div>
    </section>
  );
}
