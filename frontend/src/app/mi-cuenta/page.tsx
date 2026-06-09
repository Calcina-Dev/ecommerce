"use client"
import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Image from "next/image";
import { useAuthStore } from "@/store/useAuthStore";
import { Button } from "@/components/ui/button";

export default function MiCuentaPage() {
  const router = useRouter();
  const { user, token } = useAuthStore();
  const [orders, setOrders] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [expandedOrder, setExpandedOrder] = useState<number | null>(null);

  useEffect(() => {
    if (!user || !token) {
      router.push("/login");
      return;
    }

    const fetchOrders = async () => {
      try {
        const res = await fetch("http://localhost:8000/api/orders", {
          headers: {
            "Authorization": `Bearer ${token}`
          }
        });
        if (res.ok) {
          const data = await res.json();
          setOrders(data);
        }
      } catch (err) {
        console.error("Error cargando pedidos:", err);
      } finally {
        setLoading(false);
      }
    };

    fetchOrders();
  }, [user, token, router]);

  if (!user) return null;

  return (
    <div className="min-h-screen bg-muted/20 py-10">
      <div className="max-w-4xl mx-auto px-6">
        
        <div className="bg-background rounded-3xl p-8 border shadow-sm mb-8 flex items-center gap-6">
          <div className="w-20 h-20 bg-primary/10 text-primary rounded-full flex items-center justify-center text-2xl font-bold">
            {user.name.charAt(0).toUpperCase()}
          </div>
          <div>
            <h1 className="text-2xl font-bold">{user.name}</h1>
            <p className="text-muted-foreground">{user.email}</p>
          </div>
        </div>

        <h2 className="text-xl font-bold mb-6">Historial de Pedidos</h2>

        {loading ? (
          <div className="text-center py-10 text-muted-foreground">Cargando pedidos...</div>
        ) : orders.length === 0 ? (
          <div className="bg-background rounded-3xl p-10 border shadow-sm text-center">
            <div className="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4 opacity-50">
              <svg className="w-8 h-8 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
            <p className="text-lg font-medium text-muted-foreground">Aún no has realizado ninguna compra.</p>
            <Button variant="outline" className="mt-4 rounded-xl" onClick={() => router.push("/productos")}>Explorar catálogo</Button>
          </div>
        ) : (
          <div className="space-y-4">
            {orders.map((order) => (
              <div key={order.id} className="bg-background rounded-2xl border shadow-sm overflow-hidden">
                <div 
                  className="p-6 flex flex-wrap sm:flex-nowrap items-center justify-between gap-4 cursor-pointer hover:bg-muted/30 transition-colors"
                  onClick={() => setExpandedOrder(expandedOrder === order.id ? null : order.id)}
                >
                  <div>
                    <h3 className="font-bold text-lg">{order.order_number}</h3>
                    <p className="text-sm text-muted-foreground">
                      {new Date(order.created_at).toLocaleDateString('es-PE', { year: 'numeric', month: 'long', day: 'numeric' })}
                    </p>
                  </div>
                  
                  <div className="flex items-center gap-6">
                    <div className="text-right">
                      <p className="text-sm text-muted-foreground">Total</p>
                      <p className="font-bold">S/ {parseFloat(order.total_amount).toFixed(2)}</p>
                    </div>
                    
                    <div className={`px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider ${
                      order.status === 'delivered' ? 'bg-green-100 text-green-700' :
                      order.status === 'cancelled' ? 'bg-red-100 text-red-700' :
                      'bg-amber-100 text-amber-700'
                    }`}>
                      {order.status}
                    </div>

                    <div className="text-muted-foreground">
                      <svg className={`w-5 h-5 transform transition-transform ${expandedOrder === order.id ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                      </svg>
                    </div>
                  </div>
                </div>

                {/* Acordeón de detalles */}
                {expandedOrder === order.id && (
                  <div className="border-t bg-muted/10 p-6">
                    <h4 className="text-sm font-bold mb-4 text-muted-foreground uppercase tracking-wider">Productos comprados</h4>
                    <div className="space-y-4">
                      {order.items.map((item: any) => (
                        <div key={item.id} className="flex gap-4 items-center">
                          <div className="w-12 h-12 bg-muted rounded-lg border flex items-center justify-center text-xs text-muted-foreground font-bold">
                            {item.quantity}x
                          </div>
                          <div className="flex-1">
                            <p className="font-medium text-sm">{item.product_name}</p>
                            <p className="text-xs text-muted-foreground">S/ {parseFloat(item.price).toFixed(2)} c/u</p>
                          </div>
                          <div className="font-bold text-sm">
                            S/ {parseFloat(item.subtotal).toFixed(2)}
                          </div>
                        </div>
                      ))}
                    </div>
                    
                    <div className="mt-6 pt-6 border-t grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                      <div>
                        <span className="block font-semibold mb-1 text-muted-foreground">Enviado a:</span>
                        <p>{order.shipping_name}</p>
                        <p>{order.shipping_address}, {order.shipping_city}</p>
                        <p>{order.shipping_phone}</p>
                      </div>
                      <div>
                        <span className="block font-semibold mb-1 text-muted-foreground">Método de Pago:</span>
                        <p className="capitalize">{order.payment_method || 'Pendiente'}</p>
                        <p className={`mt-1 font-medium ${order.payment_status === 'paid' ? 'text-green-600' : 'text-amber-600'}`}>
                          {order.payment_status === 'paid' ? 'Pagado' : 'Pendiente de confirmación'}
                        </p>
                      </div>
                    </div>
                  </div>
                )}
              </div>
            ))}
          </div>
        )}

      </div>
    </div>
  );
}
