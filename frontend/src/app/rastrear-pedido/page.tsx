"use client";

import { useState, useEffect, Suspense } from "react";
import { useSearchParams, useRouter } from "next/navigation";
import { CheckCircle2, Package, Truck, Home, Search, AlertCircle, Clock } from "lucide-react";
import { motion, AnimatePresence, Variants } from "framer-motion";

// Framer Motion variants
const containerVariants: Variants = {
  hidden: { opacity: 0, y: 20 },
  visible: { 
    opacity: 1, 
    y: 0,
    transition: { 
      duration: 0.5, 
      ease: [0.22, 1, 0.36, 1],
      staggerChildren: 0.1 
    }
  }
};

const itemVariants: Variants = {
  hidden: { opacity: 0, y: 15 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.4, ease: "easeOut" } }
};

const timelineVariants: Variants = {
  hidden: { opacity: 0, scale: 0.9 },
  visible: (custom: number) => ({
    opacity: 1,
    scale: 1,
    transition: { delay: custom * 0.15, duration: 0.4, type: "spring", stiffness: 300, damping: 20 }
  })
};

function OrderTrackingContent() {
  const searchParams = useSearchParams();
  const router = useRouter();
  const [orderIdInput, setOrderIdInput] = useState("");
  const [order, setOrder] = useState<any>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [lastSearched, setLastSearched] = useState("");

  const orderId = searchParams.get("order_id");

  useEffect(() => {
    if (orderId) {
      const sanitized = orderId.trim().toUpperCase();
      setOrderIdInput(sanitized);
      // Solo buscar si no es el mismo que ya buscamos
      if (sanitized !== lastSearched) {
        fetchOrder(sanitized);
      }
    } else {
      setOrder(null);
      setError("");
      setLastSearched("");
    }
  }, [orderId]);

  const fetchOrder = async (id: string) => {
    const sanitizedId = id.trim().toUpperCase();
    if (!sanitizedId) return;
    
    setLoading(true);
    setError("");
    setOrder(null);
    setLastSearched(sanitizedId);
    try {
      const backendUrl = process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000";
      const response = await fetch(`${backendUrl}/api/orders/tracking/${encodeURIComponent(sanitizedId)}`);
      if (!response.ok) {
        if (response.status === 404) {
          setError(`No hemos podido encontrar ningún pedido con el código ${sanitizedId}. Por favor verifica que esté escrito correctamente.`);
        } else {
          setError("Ocurrió un error al intentar buscar el pedido. Intenta nuevamente.");
        }
      } else {
        const data = await response.json();
        setOrder(data);
      }
    } catch (err) {
      setError("No se pudo conectar con el servidor para buscar el pedido.");
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    const sanitized = orderIdInput.trim().toUpperCase();
    if (sanitized) {
      // Llamar fetchOrder directamente para evitar depender del useEffect
      fetchOrder(sanitized);
      // Actualizar la URL para que sea compartible/recargable
      router.push(`/rastrear-pedido?order_id=${encodeURIComponent(sanitized)}`, { scroll: false });
    }
  };

  const getStatusData = (status: string) => {
    const map: Record<string, any> = {
      pending: { label: "Pendiente", color: "bg-yellow-100 text-yellow-800", step: 1 },
      pending_payment: { label: "Pago Pendiente", color: "bg-yellow-100 text-yellow-800", step: 1 },
      processing: { label: "Procesando", color: "bg-blue-100 text-blue-800", step: 2 },
      shipped: { label: "Enviado", color: "bg-purple-100 text-purple-800", step: 3 },
      delivered: { label: "Entregado", color: "bg-green-100 text-green-800", step: 4 },
      cancelled: { label: "Cancelado", color: "bg-red-100 text-red-800", step: -1 },
    };
    return map[status] || { label: status, color: "bg-gray-100 text-gray-800", step: 0 };
  };

  const formatDate = (dateString: string) => {
    const d = new Date(dateString);
    return new Intl.DateTimeFormat('es-PE', { 
      day: 'numeric', month: 'short', year: 'numeric', 
      hour: '2-digit', minute: '2-digit' 
    }).format(d);
  };

  return (
    <div className="bg-gray-50 text-gray-800 antialiased min-h-screen pt-24 pb-12 overflow-hidden">
      <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <motion.div 
          initial="hidden" 
          animate="visible" 
          variants={containerVariants}
          className="text-center mb-10"
        >
          <motion.h1 variants={itemVariants} className="text-3xl font-extrabold text-green-600 tracking-tight">Compra Saludable</motion.h1>
          <motion.p variants={itemVariants} className="mt-2 text-sm text-gray-500 uppercase tracking-widest font-medium">Tu salud en buenas manos</motion.p>
        </motion.div>

        <motion.div 
          initial={{ opacity: 0, scale: 0.95, y: 20 }}
          animate={{ opacity: 1, scale: 1, y: 0 }}
          transition={{ duration: 0.5, ease: [0.22, 1, 0.36, 1] }}
          className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
        >
          <div className="px-6 py-8 sm:px-10 border-b border-gray-100 bg-gray-50/50">
            <form onSubmit={handleSearch} className="flex flex-col sm:flex-row gap-3">
              <input 
                type="text" 
                value={orderIdInput}
                onChange={(e) => setOrderIdInput(e.target.value)}
                placeholder="Ingresa tu número de pedido (Ej. ORD-XYZ123)" 
                className="flex-1 rounded-lg border-gray-300 shadow-sm px-4 py-3 text-gray-700 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none border transition-all duration-300 hover:shadow-md"
                required
              />
              <motion.button 
                whileHover={{ scale: 1.02 }}
                whileTap={{ scale: 0.98 }}
                type="submit" 
                disabled={loading}
                className="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors shadow-sm whitespace-nowrap flex items-center justify-center gap-2 disabled:opacity-70"
              >
                {loading ? (
                  <span className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                ) : (
                  <Search className="w-5 h-5" />
                )}
                <span>Rastrear Pedido</span>
              </motion.button>
            </form>
          </div>

          <AnimatePresence mode="wait">
            {loading && !order && !error && (
              <motion.div 
                key="loading"
                initial={{ opacity: 0, height: 0 }}
                animate={{ opacity: 1, height: "auto" }}
                exit={{ opacity: 0, height: 0 }}
                className="px-6 py-20 text-center"
              >
                <div className="w-10 h-10 border-4 border-green-100 border-t-green-600 rounded-full animate-spin mx-auto mb-4"></div>
                <p className="text-gray-500">Buscando tu pedido...</p>
              </motion.div>
            )}

            {error && (
              <motion.div 
                key="error"
                initial={{ opacity: 0, scale: 0.9 }}
                animate={{ opacity: 1, scale: 1 }}
                exit={{ opacity: 0, scale: 0.9 }}
                className="px-6 py-12 text-center"
              >
                <motion.div 
                  initial={{ rotate: -20, scale: 0 }}
                  animate={{ rotate: 0, scale: 1 }}
                  transition={{ type: "spring", stiffness: 200, damping: 15 }}
                  className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 mb-4"
                >
                  <AlertCircle className="w-8 h-8 text-red-600" />
                </motion.div>
                <h3 className="text-lg font-medium text-gray-900">Pedido no encontrado</h3>
                <p className="mt-2 text-gray-500 max-w-md mx-auto">{error}</p>
              </motion.div>
            )}

            {order && (
              <motion.div 
                key="order-data"
                variants={containerVariants}
                initial="hidden"
                animate="visible"
                className="px-6 py-8 sm:px-10"
              >
                {(() => {
                  const currentStatus = getStatusData(order.status);
                  return (
                    <>
                      <motion.div variants={itemVariants} className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                        <div>
                          <h2 className="text-2xl font-bold text-gray-900">Pedido #{order.order_number}</h2>
                          <p className="text-sm text-gray-500 mt-1">Realizado el {formatDate(order.created_at).split(',')[0]}</p>
                        </div>
                        <div>
                          <motion.span 
                            initial={{ opacity: 0, x: 20 }}
                            animate={{ opacity: 1, x: 0 }}
                            className={`inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold ${currentStatus.color}`}
                          >
                            {currentStatus.label}
                          </motion.span>
                        </div>
                      </motion.div>

                      {order.status !== "cancelled" && (
                        <>
                        <motion.div variants={itemVariants} className="relative mb-12 mt-4 hidden sm:block">
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
                                animate={{ width: `${Math.max(0, ((currentStatus.step - 1) / 3) * 100)}%` }} 
                                transition={{ duration: 1.5, ease: "easeInOut", delay: 0.5 }}
                                className="absolute left-0 top-0 border-t-2 border-green-600"
                              ></motion.div>
                            </div>
                          </div>
                          <div className="relative flex justify-between">
                            <motion.div custom={0} variants={timelineVariants} className="flex items-center flex-col relative">
                              <div className={`h-10 w-10 rounded-full flex items-center justify-center transition-colors duration-500 relative z-10 ${currentStatus.step >= 1 ? "bg-green-600 text-white ring-4 ring-white shadow-md shadow-green-600/30" : "bg-gray-200 text-gray-400 ring-4 ring-white"}`}>
                                <CheckCircle2 className="w-5 h-5" />
                              </div>
                              <div className={`absolute top-12 text-xs font-semibold ${currentStatus.step >= 1 ? "text-gray-900" : "text-gray-400"}`}>Recibido</div>
                              {currentStatus.step >= 1 ? (
                                <p className="absolute top-16 mt-1 text-[10px] text-muted-foreground whitespace-nowrap bg-muted/30 py-0.5 px-1.5 rounded-md">
                                  {formatDate(order.created_at).split(',')[0]}
                                </p>
                              ) : (
                                <p className="absolute top-16 mt-1 text-[10px] text-transparent whitespace-nowrap select-none">--/--</p>
                              )}
                            </motion.div>
                            <motion.div custom={1} variants={timelineVariants} className="flex items-center flex-col relative">
                              <div className={`h-10 w-10 rounded-full flex items-center justify-center transition-colors duration-500 relative z-10 ${currentStatus.step >= 2 ? "bg-green-600 text-white ring-4 ring-white shadow-md shadow-green-600/30" : "bg-gray-200 text-gray-400 ring-4 ring-white"}`}>
                                <Package className="w-5 h-5" />
                              </div>
                              <div className={`absolute top-12 text-xs font-semibold ${currentStatus.step >= 2 ? "text-gray-900" : "text-gray-400"}`}>Procesando</div>
                              {currentStatus.step >= 2 ? (
                                <p className="absolute top-16 mt-1 text-[10px] text-muted-foreground whitespace-nowrap bg-muted/30 py-0.5 px-1.5 rounded-md">
                                  {formatDate(order.processing_at || order.updated_at).split(',')[0]}
                                </p>
                              ) : (
                                <p className="absolute top-16 mt-1 text-[10px] text-transparent whitespace-nowrap select-none">--/--</p>
                              )}
                            </motion.div>
                            <motion.div custom={2} variants={timelineVariants} className="flex items-center flex-col relative">
                              <div className={`h-10 w-10 rounded-full flex items-center justify-center transition-colors duration-500 relative z-10 ${currentStatus.step >= 3 ? "bg-green-600 text-white ring-4 ring-white shadow-md shadow-green-600/30" : "bg-gray-200 text-gray-400 ring-4 ring-white"}`}>
                                <Truck className="w-5 h-5" />
                              </div>
                              <div className={`absolute top-12 text-xs font-semibold ${currentStatus.step >= 3 ? "text-gray-900" : "text-gray-400"}`}>Enviado</div>
                              {currentStatus.step >= 3 ? (
                                <p className="absolute top-16 mt-1 text-[10px] text-muted-foreground whitespace-nowrap bg-muted/30 py-0.5 px-1.5 rounded-md">
                                  {formatDate(order.shipped_at || order.updated_at).split(',')[0]}
                                </p>
                              ) : (
                                <p className="absolute top-16 mt-1 text-[10px] text-transparent whitespace-nowrap select-none">--/--</p>
                              )}
                            </motion.div>
                            <motion.div custom={3} variants={timelineVariants} className="flex items-center flex-col relative">
                              <div className={`h-10 w-10 rounded-full flex items-center justify-center transition-colors duration-500 relative z-10 ${currentStatus.step >= 4 ? "bg-green-600 text-white ring-4 ring-white shadow-md shadow-green-600/30" : "bg-gray-200 text-gray-400 ring-4 ring-white"}`}>
                                <Home className="w-5 h-5" />
                              </div>
                              <div className={`absolute top-12 text-xs font-semibold ${currentStatus.step >= 4 ? "text-gray-900" : "text-gray-400"}`}>Entregado</div>
                              {currentStatus.step >= 4 ? (
                                <p className="absolute top-16 mt-1 text-[10px] text-muted-foreground whitespace-nowrap bg-muted/30 py-0.5 px-1.5 rounded-md">
                                  {formatDate(order.delivered_at || order.updated_at).split(',')[0]}
                                </p>
                              ) : (
                                <p className="absolute top-16 mt-1 text-[10px] text-transparent whitespace-nowrap select-none">--/--</p>
                              )}
                            </motion.div>
                          </div>
                        </motion.div>

                        {/* Vertical Timeline - Mobile */}
                        <div className="sm:hidden my-6 pl-2 space-y-5 relative before:absolute before:inset-0 before:left-[19px] before:top-2 before:bottom-2 before:w-[2px] before:bg-gray-200 dark:before:bg-zinc-800">
                          {[
                            { label: 'Recibido', date: order.created_at, icon: CheckCircle2, stepNum: 1 },
                            { label: 'Procesando', date: order.processing_at || order.updated_at, icon: Package, stepNum: 2 },
                            { label: 'Enviado', date: order.shipped_at || order.updated_at, icon: Truck, stepNum: 3 },
                            { label: 'Entregado', date: order.delivered_at || order.updated_at, icon: Home, stepNum: 4 },
                          ].map((step) => {
                            const isPastOrCurrent = currentStatus.step >= step.stepNum;
                            const IconComponent = step.icon;

                            return (
                              <div key={step.label} className="flex items-start gap-3.5 relative z-10">
                                <div className={`h-9 w-9 rounded-full flex items-center justify-center transition-colors duration-300 flex-shrink-0 ${isPastOrCurrent ? 'bg-green-600 text-white shadow-md shadow-green-600/30 ring-4 ring-white dark:ring-zinc-900' : 'bg-gray-200 dark:bg-zinc-800 text-gray-400 dark:text-zinc-500 ring-4 ring-white dark:ring-zinc-900'}`}>
                                  <IconComponent className="w-4 h-4" />
                                </div>
                                <div className="flex-1 pt-1">
                                  <div className="flex items-center justify-between">
                                    <h4 className={`text-sm font-bold ${isPastOrCurrent ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-zinc-500'}`}>
                                      {step.label}
                                    </h4>
                                    {isPastOrCurrent && step.date && (
                                      <span className="text-[11px] text-muted-foreground bg-muted/40 px-2 py-0.5 rounded-md font-medium">
                                        {formatDate(step.date).split(',')[0]}
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

                      {order.tracking_code && (
                        <motion.div 
                          variants={itemVariants} 
                          whileHover={{ scale: 1.01, boxShadow: "0px 4px 15px rgba(59, 130, 246, 0.1)" }}
                          className="bg-blue-50 border border-blue-100 rounded-xl p-5 mb-8 flex items-start gap-4 transition-all"
                        >
                          <div className="bg-blue-100 text-blue-600 p-2 rounded-lg">
                            <Truck className="w-6 h-6 animate-pulse" />
                          </div>
                          <div>
                            <h4 className="text-sm font-semibold text-blue-900">Información de Envío</h4>
                            <p className="text-sm text-blue-800 mt-1">Empresa: <strong>{order.shippingMethod?.name || "Courier"}</strong></p>
                            <p className="text-sm text-blue-800">Código de Rastreo: <strong className="font-mono bg-blue-100 px-2 py-0.5 rounded ml-1">{order.tracking_code}</strong></p>
                          </div>
                        </motion.div>
                      )}

                      {order.notes && order.notes.length > 0 && (
                        <motion.div variants={itemVariants} className="mb-8">
                          <h3 className="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <Clock className="w-5 h-5 text-green-600" />
                            Actualizaciones
                          </h3>
                          <div className="space-y-4">
                            {order.notes.map((note: any, index: number) => (
                              <motion.div 
                                key={note.id} 
                                initial={{ opacity: 0, x: -20 }}
                                animate={{ opacity: 1, x: 0 }}
                                transition={{ delay: 0.3 + (index * 0.1) }}
                                className="bg-gray-50 border border-gray-100 hover:border-green-200 hover:bg-green-50/30 rounded-xl p-4 flex gap-4 transition-colors"
                              >
                                <div className="flex-shrink-0 mt-1 relative">
                                  {index !== order.notes.length - 1 && (
                                    <div className="absolute top-4 left-1/2 -ml-px w-0.5 h-full bg-gray-200"></div>
                                  )}
                                  <div className="w-2 h-2 rounded-full bg-green-500 ring-4 ring-green-100 relative z-10"></div>
                                </div>
                                <div>
                                  <p className="text-xs text-gray-500 font-medium mb-1">{formatDate(note.created_at)}</p>
                                  <p className="text-sm text-gray-800 italic">"{note.content}"</p>
                                </div>
                              </motion.div>
                            ))}
                          </div>
                        </motion.div>
                      )}

                      <motion.div variants={itemVariants} className="border-t border-gray-100 pt-8 mt-8">
                        <h3 className="text-lg font-bold text-gray-900 mb-4">Resumen del Pedido</h3>
                        <div className="space-y-3 mb-6">
                          {order.items.map((item: any, i: number) => (
                            <motion.div 
                              key={item.id} 
                              initial={{ opacity: 0, y: 10 }}
                              animate={{ opacity: 1, y: 0 }}
                              transition={{ delay: 0.4 + (i * 0.05) }}
                              className="flex justify-between items-center group hover:bg-gray-50 p-2 -mx-2 rounded-lg transition-colors"
                            >
                              <div>
                                <p className="text-sm font-medium text-gray-900 group-hover:text-green-700 transition-colors">{item.product_name}</p>
                                <p className="text-xs text-gray-500">Cant: {item.quantity} (S/ {parseFloat(item.price).toFixed(2)} c/u)</p>
                              </div>
                              <span className="text-sm font-semibold text-gray-900">
                                S/ {(parseFloat(item.price) * item.quantity).toFixed(2)}
                              </span>
                            </motion.div>
                          ))}
                        </div>
                        
                        <motion.div 
                          variants={itemVariants}
                          className="bg-gray-50 p-5 rounded-xl space-y-2 border border-gray-100"
                        >
                          <div className="flex justify-between text-sm text-gray-600">
                            <span>Subtotal</span>
                            <span>S/ {order.items.reduce((acc: number, item: any) => acc + (item.price * item.quantity), 0).toFixed(2)}</span>
                          </div>
                          {parseFloat(order.discount_amount) > 0 && (
                            <div className="flex justify-between text-sm text-red-600">
                              <span>Descuento</span>
                              <span>- S/ {parseFloat(order.discount_amount).toFixed(2)}</span>
                            </div>
                          )}
                          <div className="flex justify-between text-sm text-gray-600">
                            <span>Envío</span>
                            <span>S/ {parseFloat(order.shipping_cost || 0).toFixed(2)}</span>
                          </div>
                          <div className="flex justify-between text-base font-bold text-gray-900 pt-3 border-t border-gray-200 mt-3">
                            <span>Total</span>
                            <motion.span 
                              initial={{ scale: 0.9 }}
                              animate={{ scale: 1 }}
                              transition={{ type: "spring", stiffness: 300, delay: 0.6 }}
                              className="text-green-600 text-lg"
                            >
                              S/ {parseFloat(order.total_amount || 0).toFixed(2)}
                            </motion.span>
                          </div>
                        </motion.div>
                      </motion.div>
                    </>
                  );
                })()}
              </motion.div>
            )}
          </AnimatePresence>
        </motion.div>
      </div>
    </div>
  );
}

export default function OrderTrackingPage() {
  return (
    <Suspense fallback={
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="w-10 h-10 border-4 border-green-100 border-t-green-600 rounded-full animate-spin"></div>
      </div>
    }>
      <OrderTrackingContent />
    </Suspense>
  );
}
