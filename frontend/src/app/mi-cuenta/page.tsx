"use client"
import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import Image from "next/image";
import { useAuthStore } from "@/store/useAuthStore";
import { CheckCircle2, Package, Truck, Home } from "lucide-react";
import { Button } from "@/components/ui/button";
import { motion, AnimatePresence } from "framer-motion";
import { toast } from "sonner";

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
          toast.error("Tu sesión ha expirado por seguridad. Por favor, ingresa nuevamente.");
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
      if (res.status === 401) {
        toast.error("Tu sesión ha expirado por seguridad. Por favor, ingresa nuevamente.");
        useAuthStore.getState().logout();
        router.push("/login");
        return;
      }
      if (res.ok) {
        const data = await res.json();
        setAuth(data.user, token!);
        setIsEditing(false);
        toast.success("Perfil actualizado correctamente.");
      } else {
        toast.error("No se pudo actualizar el perfil.");
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
                  <input 
                    type="tel" 
                    pattern="[0-9]{9}" 
                    maxLength={9} 
                    title="El número debe tener exactamente 9 dígitos"
                    value={editForm.phone} 
                    onChange={e => setEditForm({...editForm, phone: e.target.value.replace(/\D/g, '')})} 
                    className="w-full px-4 py-2 border rounded-xl outline-none focus:ring-2 focus:ring-primary" 
                  />
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
                      {{
                        pending: 'Pendiente',
                        processing: 'Procesando',
                        shipped: 'Enviado',
                        delivered: 'Entregado',
                        cancelled: 'Cancelado'
                      }[order.status as string] || order.status}
                    </div>

                    <div className="text-muted-foreground">
                      <svg className={`w-5 h-5 transform transition-transform ${expandedOrder === order.id ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                      </svg>
                    </div>
                  </div>
                </div>

                {/* Acordeón de detalles */}
                <AnimatePresence>
                {expandedOrder === order.id && (
                  <motion.div 
                    initial={{ height: 0, opacity: 0 }}
                    animate={{ height: "auto", opacity: 1 }}
                    exit={{ height: 0, opacity: 0 }}
                    transition={{ duration: 0.3, ease: "easeInOut" }}
                    className="border-t bg-muted/10 p-0 sm:p-6 overflow-hidden"
                  >
                    <div className="p-6 sm:p-0">
                    
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
                        <>
                        <div className="relative mb-12 mt-4 hidden sm:block">
                          <div className="absolute inset-0 flex items-center" aria-hidden="true">
                            <div className="w-full relative h-[2px]">
                              <motion.div 
                                initial={{ width: 0 }} 
                                animate={{ width: "100%" }} 
                                transition={{ duration: 1, ease: "easeOut", delay: 0.2 }}
                                className="absolute left-0 top-0 w-full border-t-2 border-gray-200"
                              ></motion.div>
                              <motion.div 
                                initial={{ width: 0 }} 
                                animate={{ width: `${Math.max(0, (((order.status === 'delivered' ? 4 : order.status === 'shipped' ? 3 : order.status === 'processing' ? 2 : 1) - 1) / 3) * 100)}%` }} 
                                transition={{ duration: 1.5, ease: "easeInOut", delay: 0.5 }}
                                className="absolute left-0 top-0 border-t-2 border-green-600"
                              ></motion.div>
                            </div>
                          </div>
                          <div className="relative flex justify-between">
                          {[
                            { status: 'pending', label: 'Recibido', date: order.created_at, icon: CheckCircle2, stepNum: 1 },
                            { status: 'processing', label: 'Procesando', date: order.processing_at, icon: Package, stepNum: 2 },
                            { status: 'shipped', label: 'Enviado', date: order.shipped_at, icon: Truck, stepNum: 3 },
                            { status: 'delivered', label: 'Entregado', date: order.delivered_at, icon: Home, stepNum: 4 },
                          ].map((step, index) => {
                            const currentStepNum = 
                              order.status === 'delivered' ? 4 : 
                              order.status === 'shipped' ? 3 : 
                              order.status === 'processing' ? 2 : 1;
                              
                            const isPastOrCurrent = step.stepNum <= currentStepNum;
                            const displayDate = step.date || (isPastOrCurrent ? order.updated_at : null);
                            const IconComponent = step.icon;

                            return (
                              <motion.div 
                                key={step.status} 
                                initial={{ opacity: 0, scale: 0.9 }}
                                animate={{ opacity: 1, scale: 1 }}
                                transition={{ type: "spring", stiffness: 300, damping: 20, delay: index * 0.15 }}
                                className="flex items-center flex-col relative"
                              >
                                <div className={`h-10 w-10 rounded-full flex items-center justify-center transition-colors duration-500 relative z-10 ${isPastOrCurrent ? 'bg-green-600 text-white ring-4 ring-white shadow-md shadow-green-600/30' : 'bg-gray-200 text-gray-400 ring-4 ring-white'}`}>
                                  <IconComponent className="w-5 h-5" />
                                </div>
                                <div className={`absolute top-12 text-xs font-semibold ${isPastOrCurrent ? 'text-gray-900' : 'text-gray-400'}`}>
                                  {step.label}
                                </div>
                                {isPastOrCurrent ? (
                                  <p className="absolute top-16 mt-1 text-[10px] text-muted-foreground whitespace-nowrap bg-muted/30 py-0.5 px-1.5 rounded-md">
                                    {new Date(displayDate).toLocaleDateString('es-PE', { month: 'short', day: 'numeric' })}
                                  </p>
                                ) : (
                                  <p className="absolute top-16 mt-1 text-[10px] text-transparent whitespace-nowrap select-none">
                                    --/--
                                  </p>
                                )}
                              </motion.div>
                            );
                          })}
                          </div>
                        </div>

                        {/* Vertical Timeline - Mobile */}
                        <div className="sm:hidden my-6 pl-2 space-y-5 relative before:absolute before:inset-0 before:left-[19px] before:top-2 before:bottom-2 before:w-[2px] before:bg-gray-200 dark:before:bg-zinc-800">
                          {[
                            { status: 'pending', label: 'Recibido', date: order.created_at, icon: CheckCircle2, stepNum: 1 },
                            { status: 'processing', label: 'Procesando', date: order.processing_at, icon: Package, stepNum: 2 },
                            { status: 'shipped', label: 'Enviado', date: order.shipped_at, icon: Truck, stepNum: 3 },
                            { status: 'delivered', label: 'Entregado', date: order.delivered_at, icon: Home, stepNum: 4 },
                          ].map((step, index) => {
                            const currentStepNum = 
                              order.status === 'delivered' ? 4 : 
                              order.status === 'shipped' ? 3 : 
                              order.status === 'processing' ? 2 : 1;
                              
                            const isPastOrCurrent = step.stepNum <= currentStepNum;
                            const displayDate = step.date || (isPastOrCurrent ? order.updated_at : null);
                            const IconComponent = step.icon;

                            return (
                              <div key={step.status} className="flex items-start gap-3.5 relative z-10">
                                <div className={`h-9 w-9 rounded-full flex items-center justify-center transition-colors duration-300 flex-shrink-0 ${isPastOrCurrent ? 'bg-green-600 text-white shadow-md shadow-green-600/30 ring-4 ring-white dark:ring-zinc-900' : 'bg-gray-200 dark:bg-zinc-800 text-gray-400 dark:text-zinc-500 ring-4 ring-white dark:ring-zinc-900'}`}>
                                  <IconComponent className="w-4 h-4" />
                                </div>
                                <div className="flex-1 pt-1">
                                  <div className="flex items-center justify-between">
                                    <h4 className={`text-sm font-bold ${isPastOrCurrent ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-zinc-500'}`}>
                                      {step.label}
                                    </h4>
                                    {isPastOrCurrent && displayDate && (
                                      <span className="text-[11px] text-muted-foreground bg-muted/40 px-2 py-0.5 rounded-md font-medium">
                                        {new Date(displayDate).toLocaleDateString('es-PE', { month: 'short', day: 'numeric' })}
                                      </span>
                                    )}
                                  </div>
                                </div>
                              </div>
                            );
                          })}
                        </div>
                        </>
                      )}
                    </div>
                    {/* FIN TIMELINE */}
                    {/* Tracking Info (Blue Box) */}
                    {order.tracking_code && (
                      <div className="bg-blue-50 border border-blue-100 rounded-xl p-5 mb-8 flex items-start gap-4 transition-all hover:shadow-sm">
                        <div className="bg-blue-100 text-blue-600 p-2 rounded-lg">
                          <Truck className="w-6 h-6 animate-pulse" />
                        </div>
                        <div>
                          <h4 className="text-sm font-semibold text-blue-900">Información de Envío</h4>
                          <p className="text-sm text-blue-800 mt-1">Empresa: <strong>{order.shipping_method?.name || "Courier"}</strong></p>
                          <p className="text-sm text-blue-800">Código de Rastreo: <strong className="font-mono bg-blue-100 px-2 py-0.5 rounded ml-1">{order.tracking_code}</strong></p>
                        </div>
                      </div>
                    )}

                    <div className="border-t border-gray-100 pt-8 mt-8">
                      <h4 className="text-lg font-bold text-gray-900 mb-4">Resumen del Pedido</h4>
                      <div className="space-y-3 mb-6">
                        {order.items.map((item: any, i: number) => (
                          <div key={item.id} className="flex justify-between items-center group hover:bg-gray-50 p-2 -mx-2 rounded-lg transition-colors">
                            <div className="flex-1">
                              {item.product && item.product.slug ? (
                                <Link href={`/productos/${item.product.slug}`} className="text-sm font-medium text-gray-900 group-hover:text-green-700 transition-colors">
                                  {item.product_name}
                                </Link>
                              ) : (
                                <p className="text-sm font-medium text-gray-900">{item.product_name}</p>
                              )}
                              <p className="text-xs text-gray-500">Cant: {item.quantity} (S/ {parseFloat(item.price).toFixed(2)} c/u)</p>
                            </div>
                            <span className="text-sm font-semibold text-gray-900">
                              S/ {parseFloat(item.subtotal).toFixed(2)}
                            </span>
                          </div>
                        ))}
                      </div>

                      <div className="bg-gray-50 p-5 rounded-xl space-y-2 border border-gray-100 mb-6">
                        <div className="flex justify-between text-sm text-gray-600">
                          <span>Subtotal</span>
                          <span>S/ {order.items.reduce((acc: number, item: any) => acc + (item.price * item.quantity), 0).toFixed(2)}</span>
                        </div>
                        {parseFloat(order.discount_amount) > 0 && (
                          <div className="flex justify-between text-sm text-red-600">
                            <span>Descuento {order.coupon ? `(${order.coupon.code})` : ''}</span>
                            <span>- S/ {parseFloat(order.discount_amount).toFixed(2)}</span>
                          </div>
                        )}
                        <div className="flex justify-between text-sm text-gray-600">
                          <span>Envío</span>
                          <span>S/ {parseFloat(order.shipping_cost || 0).toFixed(2)}</span>
                        </div>
                        <div className="flex justify-between text-base font-bold text-gray-900 pt-3 border-t border-gray-200 mt-3">
                          <span>Total</span>
                          <span className="text-green-600 text-lg">
                            S/ {parseFloat(order.total_amount || 0).toFixed(2)}
                          </span>
                        </div>
                      </div>

                      <div className="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm bg-muted/10 p-5 rounded-xl border border-gray-100">
                        <div>
                          <p className="text-muted-foreground font-semibold mb-2 uppercase tracking-wider text-xs">Datos de Envío</p>
                          <p className="font-medium text-gray-900">{order.shipping_name}</p>
                          <p className="text-gray-600">{order.shipping_address}</p>
                          <p className="text-gray-600">{order.shipping_district ? `${order.shipping_district}, ${order.shipping_province}, ${order.shipping_department}` : order.shipping_city}</p>
                          <p className="text-gray-600">CP: {order.shipping_postal_code}</p>
                          <p className="text-gray-600">Telf: {order.shipping_phone}</p>
                        </div>
                        <div>
                          <p className="text-muted-foreground font-semibold mb-2 uppercase tracking-wider text-xs">Datos de Pago</p>
                          <p className="font-medium text-gray-900 capitalize">{order.payment_method || 'N/A'}</p>
                          <p className={`font-bold mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs ${order.payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'}`}>
                            {order.payment_status === 'paid' ? 'Pagado' : order.payment_status === 'pending' ? 'Pendiente' : 'Fallido'}
                          </p>
                        </div>
                      </div>
                    </div>
                    </div>
                  </motion.div>
                )}
                </AnimatePresence>
              </div>
            ))}
          </div>
        )}

      </div>
    </div>
  );
}
