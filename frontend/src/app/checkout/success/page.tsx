"use client"
import { Suspense, useEffect, useState } from "react";
import { useSearchParams } from "next/navigation";
import Link from "next/link";
import { useCartStore } from "@/store/useCartStore";
import { toast } from "sonner";
import confetti from "canvas-confetti";
import { DotLottieReact } from "@lottiefiles/dotlottie-react";

function SuccessContent() {
  const searchParams = useSearchParams();
  const orderId = searchParams.get('order_id') || searchParams.get('order') || "100023";
  const email = searchParams.get('email') || "tu correo electrónico";
  const clearCart = useCartStore(state => state.clearCart);
  const [showDetails, setShowDetails] = useState(false);

  useEffect(() => {
    if (orderId) {
      clearCart();
      toast.success('¡Compra realizada con éxito!', {
        id: 'success-toast',
        duration: 2500,
      });

      // Lanza confeti festivo desde los costados superiores
      setTimeout(() => {
        confetti({
          particleCount: 80,
          angle: 60,
          spread: 55,
          origin: { x: 0 }
        });
        confetti({
          particleCount: 80,
          angle: 120,
          spread: 55,
          origin: { x: 1 }
        });
      }, 400);
    }
  }, [orderId, clearCart]);

  return (
    <div className="min-h-[85vh] bg-gray-100 flex flex-col justify-between">
      {/* Sección Superior: Verde Celebración */}
      <div className="bg-[#4ade80] text-gray-950 py-16 px-6 text-center relative overflow-hidden shadow-sm">
        {/* Partículas festivas flotantes de la marca */}
        <div className="absolute inset-0 pointer-events-none">
          <div className="absolute top-8 left-10 w-3 h-3 bg-white rounded-full animate-ping"></div>
          <div className="absolute top-12 right-16 w-3 h-3 bg-[#14532d] rounded-full animate-ping delay-300"></div>
          <div className="absolute bottom-10 left-1/4 w-2.5 h-2.5 bg-[#111827] rounded-full animate-ping delay-700"></div>
          <div className="absolute bottom-8 right-1/4 w-3 h-3 bg-white rounded-full animate-ping delay-500"></div>
        </div>

        {/* Lottie Animation Success Confetti */}
        <div className="w-56 h-56 sm:w-64 sm:h-64 mx-auto mb-2 relative flex items-center justify-center">
          <DotLottieReact
            src="/animations/confetti.json"
            loop
            autoplay
            className="w-full h-full absolute inset-0 pointer-events-none scale-125 sm:scale-150"
          />
          <div className="w-28 h-28 sm:w-32 sm:h-32 rounded-full bg-white text-[#22c55e] flex items-center justify-center shadow-2xl animate-bounce">
            <svg className="w-16 h-16 sm:w-20 sm:h-20 stroke-[3.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
            </svg>
          </div>
        </div>

        <h1 className="text-3xl sm:text-4xl font-extrabold mb-3 tracking-tight">
          Pedido confirmado. ¡Gracias!
        </h1>
        <p className="text-base sm:text-lg font-medium text-gray-900 max-w-xl mx-auto leading-relaxed">
          Te enviaremos una confirmación por correo electrónico a <span className="font-bold underline decoration-black/30">{email}</span>.
        </p>
      </div>

      {/* Sección Central: Información del Pedido Desplegable */}
      <div className="max-w-2xl mx-auto w-full px-6 py-10 flex-1">
        <div className="text-center mb-8">
          <button 
            onClick={() => setShowDetails(!showDetails)}
            className="inline-flex items-center gap-2 text-gray-700 font-bold text-base hover:text-black transition-colors py-2 px-4 rounded-xl hover:bg-gray-200/60"
          >
            <span>Ver información del pedido</span>
            <svg className={`w-5 h-5 transform transition-transform duration-300 ${showDetails ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
        </div>

        {/* Tarjeta de Detalles Desplegable */}
        {showDetails && (
          <div className="bg-white rounded-3xl p-6 sm:p-8 shadow-md border border-gray-200/80 mb-8 animate-in slide-in-from-top-4 fade-in duration-300">
            <h3 className="text-lg font-bold border-b pb-4 mb-4 text-gray-900 flex justify-between items-center">
              <span>Resumen de la Orden</span>
              <span className="text-sm font-mono bg-green-100 text-green-800 px-3 py-1 rounded-full">#{orderId}</span>
            </h3>
            <div className="space-y-3 text-sm text-gray-600">
              <div className="flex justify-between py-1.5 border-b border-gray-100">
                <span className="font-medium">Número de Pedido:</span>
                <span className="font-mono font-bold text-gray-900">#{orderId}</span>
              </div>
              <div className="flex justify-between py-1.5 border-b border-gray-100">
                <span className="font-medium">Estado del Pago:</span>
                <span className="text-green-600 font-bold flex items-center gap-1.5">
                  <span className="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                  Aprobado y Pagado
                </span>
              </div>
              <div className="flex justify-between py-1.5 border-b border-gray-100">
                <span className="font-medium">Correo de Notificación:</span>
                <span className="font-medium text-gray-900">{email}</span>
              </div>
              <div className="flex justify-between py-1.5">
                <span className="font-medium">Fecha de Transacción:</span>
                <span className="text-gray-900">{new Date().toLocaleDateString('es-PE', { day: '2-digit', month: 'long', year: 'numeric' })}</span>
              </div>
            </div>
          </div>
        )}

        {/* Botones de Acción */}
        <div className="flex flex-col sm:flex-row gap-4 justify-center items-center mt-6">
          <Link 
            href="/productos"
            className="w-full sm:w-auto bg-gray-900 text-white px-8 py-4 rounded-2xl font-bold hover:bg-black transition-all shadow-lg hover:shadow-xl text-center flex items-center justify-center gap-2"
          >
            <span>Seguir Comprando</span>
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
          </Link>
          <Link 
            href="/"
            className="w-full sm:w-auto bg-white text-gray-800 border border-gray-300 px-8 py-4 rounded-2xl font-bold hover:bg-gray-50 transition-all text-center"
          >
            Volver al Inicio
          </Link>
        </div>
      </div>
    </div>
  );
}

export default function Page() {
  return (
    <Suspense fallback={<div className="min-h-screen flex items-center justify-center">Cargando...</div>}>
      <SuccessContent />
    </Suspense>
  );
}
