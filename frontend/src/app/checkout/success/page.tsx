"use client"
import { Suspense, useEffect } from "react";
import { useSearchParams } from "next/navigation";
import Link from "next/link";
import { useCartStore } from "@/store/useCartStore";

function SuccessContent() {
  const searchParams = useSearchParams();
  const orderId = searchParams.get('order_id');
  const clearCart = useCartStore(state => state.clearCart);

  useEffect(() => {
    if (orderId) {
      clearCart();
    }
  }, [orderId, clearCart]);

  return (
    <div className="max-w-2xl mx-auto px-6 py-24 text-center">
      <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-8">
        <svg className="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
        </svg>
      </div>
      <h1 className="text-4xl font-bold mb-4">¡Pedido Confirmado!</h1>
      <p className="text-lg text-muted-foreground mb-8">
        Tu pedido <span className="font-medium text-foreground">#{orderId}</span> ha sido procesado exitosamente. 
        Te enviaremos los detalles de envío por correo electrónico.
      </p>
      <div className="flex gap-4 justify-center">
        <Link 
          href="/productos"
          className="bg-primary text-primary-foreground px-8 py-3 rounded-xl font-medium hover:bg-primary/90 transition-colors"
        >
          Seguir Comprando
        </Link>
      </div>
    </div>
  );
}
