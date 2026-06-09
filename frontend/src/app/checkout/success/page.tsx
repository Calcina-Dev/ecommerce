"use client"
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { Button } from "@/components/ui/button";

export default function CheckoutSuccessPage() {
  const searchParams = useSearchParams();
  const orderNumber = searchParams.get("external_reference") || searchParams.get("order");
  const status = searchParams.get("status") || searchParams.get("collection_status");
  const isApproved = status === "approved";

  return (
    <div className="min-h-screen bg-muted/20 flex items-center justify-center p-6">
      <div className="bg-background max-w-md w-full rounded-3xl p-10 border shadow-lg text-center">
        <div className={`w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 ${isApproved ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600'}`}>
          <svg className="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d={isApproved ? "M5 13l4 4L19 7" : "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"}></path>
          </svg>
        </div>
        
        <h1 className="text-3xl font-bold mb-2">{isApproved ? "¡Pago Exitoso!" : "¡Pedido Registrado!"}</h1>
        <p className="text-muted-foreground mb-8">
          {isApproved ? "Hemos recibido tu pago y estamos preparando tu orden." : "Tu orden está pendiente de pago o en proceso de confirmación."}
        </p>

        {orderNumber && (
          <div className="bg-muted/50 rounded-2xl p-4 mb-8">
            <p className="text-sm text-muted-foreground font-medium mb-1">Número de Orden</p>
            <p className="text-2xl font-bold tracking-wider">{orderNumber}</p>
          </div>
        )}

        <Link href="/productos">
          <Button size="lg" className="w-full rounded-2xl h-14 text-lg">
            Seguir Comprando
          </Button>
        </Link>
      </div>
    </div>
  );
}
