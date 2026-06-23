"use client"
import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import Image from "next/image";
import { useAuthStore } from "@/store/useAuthStore";
import { Button } from "@/components/ui/button";

export default function MiCuentaPage() {
  const router = useRouter();
  const { user, token, setAuth } = useAuthStore();
  const [orders, setOrders] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [expandedOrder, setExpandedOrder] = useState<number | null>(null);

  const [isEditing, setIsEditing] = useState(false);
  const [editForm, setEditForm] = useState({ name: '', dni: '', phone: '' });
  const [savingProfile, setSavingProfile] = useState(false);

  useEffect(() => {
    if (user) {
      setEditForm({ name: user.name || '', dni: user.dni || '', phone: user.phone || '' });
    }
  }, [user]);

  const [isHydrated, setIsHydrated] = useState(false);

  useEffect(() => {
    setIsHydrated(true);
  }, []);

  useEffect(() => {
    if (!isHydrated) return;
    
    if (!user || !token) {
      router.push("/login");
      return;
    }

    const fetchOrders = async () => {
      try {
        const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/orders`, {
          headers: {
            "Authorization": `Bearer ${token}`
          }
        });
        if (res.status === 401) {
          useAuthStore.getState().logout();
          router.push("/login");
          return;
        }
        if (res.ok) {
          const data = await res.json();
          // Filtrar órdenes abandonadas (pending_payment) para no llenar el historial
          const validOrders = data.filter((o: any) => o.status !== 'pending_payment');
          setOrders(validOrders);
        }
      } catch (err) {
        console.error("Error cargando pedidos:", err);
      } finally {
        setLoading(false);
      }
    };

    fetchOrders();
  }, [user, token, router, isHydrated]);

  const handleSaveProfile = async (e: React.FormEvent) => {
    e.preventDefault();
    setSavingProfile(true);
    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/user/profile`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${token}`
        },
        body: JSON.stringify(editForm),
      });
      if (res.ok) {
        const data = await res.json();
        setAuth(data.user, token!);
        setIsEditing(false);
      }
    } catch (err) {
      console.error("Error guardando perfil:", err);
    } finally {
      setSavingProfile(false);
    }
  };

  if (!user) return null;

  return (
    <div className="min-h-screen bg-muted/20 py-10">
      <div className="max-w-4xl mx-auto px-6">
        
        <div className="bg-background rounded-3xl p-8 border shadow-sm mb-8">
          {!isEditing ? (
            <div className="flex items-center justify-between flex-wrap gap-6">
              <div className="flex items-center gap-6">
                <div className="w-20 h-20 bg-primary/10 text-primary rounded-full flex items-center justify-center text-2xl font-bold">
                  {user.name.charAt(0).toUpperCase()}
                </div>
                <div>
                  <h1 className="text-2xl font-bold">{user.name}</h1>
                  <p className="text-muted-foreground">{user.email}</p>
                  {(user.phone || user.dni) && (
                    <div className="mt-2 text-sm text-muted-foreground flex gap-4">
                      {user.phone && <span>📞 {user.phone}</span>}
                      {user.dni && <span>🪪 DNI: {user.dni}</span>}
                    </div>
                  )}
                </div>
              </div>
              <Button onClick={() => setIsEditing(true)} variant="outline" className="rounded-xl">Editar Perfil</Button>
            </div>
          ) : (
            <form onSubmit={handleSaveProfile} className="space-y-4">
              <h2 className="text-xl font-bold mb-4">Editar Perfil</h2>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium mb-1">Nombre Completo</label>
                  <input required value={editForm.name} onChange={e => setEditForm({...editForm, name: e.target.value})} className="w-full px-4 py-2 border rounded-xl outline-none focus:ring-2 focus:ring-primary" />
                </div>
                <div>
                  <label className="block text-sm font-medium mb-1">DNI / RUC</label>
                  <input value={editForm.dni} onChange={e => setEditForm({...editForm, dni: e.target.value})} className="w-full px-4 py-2 border rounded-xl outline-none focus:ring-2 focus:ring-primary" />
                </div>
                <div>
                  <label className="block text-sm font-medium mb-1">Teléfono</label>
                  <input value={editForm.phone} onChange={e => setEditForm({...editForm, phone: e.target.value})} className="w-full px-4 py-2 border rounded-xl outline-none focus:ring-2 focus:ring-primary" />
                </div>
              </div>
              <div className="flex gap-4 mt-6">
                <Button type="submit" disabled={savingProfile} className="rounded-xl">
                  {savingProfile ? 'Guardando...' : 'Guardar Cambios'}
                </Button>
                <Button type="button" variant="outline" onClick={() => setIsEditing(false)} className="rounded-xl">Cancelar</Button>
              </div>
            </form>
          )}
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
                    
                    {/* INICIO TIMELINE */}
                    <div className="mb-8 p-6 bg-background rounded-3xl border shadow-sm">
                      <h4 className="text-xs font-bold mb-8 text-muted-foreground uppercase tracking-wider text-center">Estado del Pedido</h4>
                      
                      {order.status === 'cancelled' ? (
                        <div className="flex justify-center items-center gap-4 text-red-600">
                          <div className="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                            </svg>
                          </div>
                          <div>
                            <p className="font-bold text-lg">Pedido Cancelado</p>
                            {order.cancelled_at && <p className="text-sm text-red-500">{new Date(order.cancelled_at).toLocaleDateString('es-PE', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })}</p>}
                          </div>
                        </div>
                      ) : (
                        <div className="relative flex justify-between items-start w-full max-w-4xl mx-auto mt-4">
                          {/* Línea conectora de fondo */}
                          <div className="absolute top-6 left-[12.5%] right-[12.5%] h-1 bg-muted rounded-full"></div>
                          
                          {/* Línea conectora de progreso */}
                          <div 
                            className="absolute top-6 left-[12.5%] h-1 bg-primary rounded-full transition-all duration-700 ease-in-out"
                            style={{ width: 
                              order.status === 'delivered' ? '75%' : 
                              order.status === 'shipped' ? '50%' : 
                              order.status === 'processing' ? '25%' : '0%' 
                            }}
                          ></div>

                          {[
                            { status: 'pending', label: 'Pendiente', date: order.created_at, icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
                            { status: 'processing', label: 'Procesando', date: order.processing_at, icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' },
                            { status: 'shipped', label: 'Enviado', date: order.shipped_at, icon: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4' },
                            { status: 'delivered', label: 'Entregado', date: order.delivered_at, icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
                          ].map((step) => {
                            const isPastOrCurrent = 
                              step.status === 'pending' || 
                              (step.status === 'processing' && ['processing', 'shipped', 'delivered'].includes(order.status)) ||
                              (step.status === 'shipped' && ['shipped', 'delivered'].includes(order.status)) ||
                              (step.status === 'delivered' && order.status === 'delivered');

                            const displayDate = step.date || (isPastOrCurrent ? order.updated_at : null);

                            return (
                              <div key={step.status} className="relative z-10 flex flex-col items-center w-1/4">
                                <div className={`w-12 h-12 rounded-full flex items-center justify-center border-4 border-background transition-colors duration-500 shadow-sm ${isPastOrCurrent ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'}`}>
                                  <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={step.icon} />
                                  </svg>
                                </div>
                                <p className={`mt-3 text-[11px] sm:text-sm font-bold tracking-tight ${isPastOrCurrent ? 'text-foreground' : 'text-muted-foreground'}`}>{step.label}</p>
                                {isPastOrCurrent ? (
                                  <p className="text-[10px] sm:text-[11px] text-muted-foreground mt-1 text-center px-1 font-medium bg-muted/30 py-1 px-2 rounded-lg leading-tight">
                                    {new Date(displayDate).toLocaleDateString('es-PE', { month: 'short', day: 'numeric', hour: '2-digit', minute:'2-digit' })}
                                  </p>
                                ) : (
                                  <p className="text-[10px] sm:text-[11px] text-transparent mt-1 text-center px-1 font-medium bg-transparent py-1 px-2 rounded-lg leading-tight select-none">
                                    --/--
                                  </p>
                                )}
                              </div>
                            );
                          })}
                        </div>
                      )}
                    </div>
                    {/* FIN TIMELINE */}

                    <h4 className="text-sm font-bold mb-4 text-muted-foreground uppercase tracking-wider">Productos comprados</h4>
                    <div className="space-y-4">
                      {order.items.map((item: any) => (
                        <div key={item.id} className="flex gap-4 items-center">
                          <div className="w-12 h-12 bg-muted rounded-lg border flex items-center justify-center text-xs text-muted-foreground font-bold">
                            {item.quantity}x
                          </div>
                          <div className="flex-1">
                            {item.product && item.product.slug ? (
                              <Link href={`/productos/${item.product.slug}`} className="font-medium text-sm hover:text-accent hover:underline transition-all">
                                {item.product_name}
                              </Link>
                            ) : (
                              <p className="font-medium text-sm">{item.product_name}</p>
                            )}
                            <p className="text-xs text-muted-foreground">S/ {parseFloat(item.price).toFixed(2)} c/u</p>
                          </div>
                          <div className="font-bold text-sm">
                            S/ {parseFloat(item.subtotal).toFixed(2)}
                          </div>
                        </div>
                      ))}
                    </div>
                    
                    {order.discount_amount > 0 && order.coupon && (
                      <div className="mt-4 pt-4 border-t flex justify-between text-sm font-medium text-green-600">
                        <span>Descuento aplicado ({order.coupon.code})</span>
                        <span>- S/ {parseFloat(order.discount_amount).toFixed(2)}</span>
                      </div>
                    )}
                    
                    <div className="mt-6 pt-6 border-t grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                      <div>
                        <span className="block font-semibold mb-1 text-muted-foreground">Enviado a:</span>
                        <p>{order.shipping_name}</p>
                        <p>{order.shipping_address}, {order.shipping_city}</p>
                        <p>{order.shipping_phone}</p>
                        {order.shipping_method && (
                          <p className="mt-2 text-primary font-medium text-xs border border-primary/20 bg-primary/5 inline-block px-2 py-1 rounded">
                            {order.shipping_method.name}
                          </p>
                        )}
                        {order.tracking_code && (
                          <p className="mt-2 font-medium">Tracking: <span className="bg-muted px-2 py-1 rounded font-mono text-xs">{order.tracking_code}</span></p>
                        )}
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
