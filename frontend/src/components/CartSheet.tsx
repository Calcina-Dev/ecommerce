"use client";
import Image from "next/image";
import Link from "next/link";
import { useCartStore } from "@/store/useCartStore";
import { Sheet, SheetContent, SheetHeader, SheetTitle } from "@/components/ui/sheet";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Button } from "@/components/ui/button";

export function CartSheet() {
  const { items, isOpen, setIsOpen, removeItem, updateQuantity, totalPrice, totalItems } = useCartStore();

  return (
    <Sheet open={isOpen} onOpenChange={setIsOpen}>
      <SheetContent showCloseButton={false} className="w-full sm:max-w-md flex flex-col p-0 border-l rounded-l-3xl overflow-hidden">
        <SheetHeader className="p-6 border-b bg-muted/20">
          <SheetTitle className="text-xl font-bold flex items-center justify-between">
            <div className="flex items-center gap-3">
              <button 
                onClick={() => setIsOpen(false)}
                className="p-1.5 hover:bg-black/5 rounded-full transition-colors"
              >
                <svg className="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
              </button>
              <span>Tu Bolsa</span>
            </div>
            <span className="bg-primary text-primary-foreground text-sm px-3 py-1 rounded-full">
              {totalItems()}
            </span>
          </SheetTitle>
        </SheetHeader>

        <ScrollArea className="flex-1 p-6">
          {items.length === 0 ? (
            <div className="h-full flex flex-col items-center justify-center space-y-6 text-center py-24 px-4">
              <div className="relative w-24 h-24 bg-emerald-50 rounded-full flex items-center justify-center mb-2">
                <div className="absolute inset-0 border-2 border-emerald-100 rounded-full animate-ping opacity-20"></div>
                <svg className="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
              </div>
              <div className="space-y-2">
                <p className="text-xl font-bold tracking-tight text-gray-900">Tu bolsa está vacía</p>
                <p className="text-sm text-gray-500 max-w-[200px] mx-auto">Parece que aún no has añadido ninguna vitamina o suplemento.</p>
              </div>
              <Button 
                className="rounded-2xl h-12 px-8 shadow-md active:scale-95 transition-transform mt-4 font-semibold" 
                onClick={() => setIsOpen(false)}
              >
                Explorar Catálogo
              </Button>
            </div>
          ) : (
            <div className="space-y-6">
              {items.map((item) => (
                <div key={item.id} className="flex gap-4">
                  <div className="relative w-20 h-20 rounded-xl overflow-hidden bg-muted border flex-shrink-0">
                    <Image 
                      src={item.image_url 
                        ? (item.image_url.startsWith('http') ? item.image_url : `http://localhost:8000/storage/${item.image_url}`)
                        : "https://images.unsplash.com/photo-1584308666744-24d5e47ac9db?q=80&w=600&auto=format&fit=crop"} 
                      alt={item.name} 
                      fill 
                      className="object-cover"
                    />
                  </div>
                  <div className="flex-1 flex flex-col justify-between">
                    <div>
                      <h4 className="font-medium text-sm leading-tight line-clamp-2">{item.name}</h4>
                      <p className="font-bold mt-1">S/ {parseFloat(item.price).toFixed(2)}</p>
                    </div>
                    <div className="flex items-center justify-between mt-2">
                      <div className="flex items-center border rounded-lg overflow-hidden h-8">
                        <button 
                          className="px-3 bg-muted/50 hover:bg-muted text-sm transition-colors"
                          onClick={() => updateQuantity(item.id, item.quantity - 1)}
                        >
                          -
                        </button>
                        <span className="px-3 text-sm font-medium w-8 text-center">{item.quantity}</span>
                        <button 
                          className="px-3 bg-muted/50 hover:bg-muted text-sm transition-colors"
                          onClick={() => updateQuantity(item.id, item.quantity + 1)}
                        >
                          +
                        </button>
                      </div>
                      <button 
                        className="text-xs text-destructive font-medium hover:underline px-2"
                        onClick={() => removeItem(item.id)}
                      >
                        Eliminar
                      </button>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </ScrollArea>

        {items.length > 0 && (
          <div className="p-6 border-t bg-background shadow-[0_-10px_20px_-10px_rgba(0,0,0,0.05)]">
            <div className="flex items-center justify-between mb-4">
              <span className="font-medium text-muted-foreground">Subtotal</span>
              <span className="text-xl font-bold">S/ {totalPrice().toFixed(2)}</span>
            </div>
            <Link href="/checkout" onClick={() => setIsOpen(false)}>
              <Button size="lg" className="w-full rounded-2xl h-14 text-lg shadow-sm hover:shadow-md transition-all active:scale-95 duration-200 ease-[var(--spring-easing)] font-semibold">
                Proceder al Pago
              </Button>
            </Link>
          </div>
        )}
      </SheetContent>
    </Sheet>
  );
}
