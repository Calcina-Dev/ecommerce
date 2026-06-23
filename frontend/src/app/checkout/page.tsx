"use client"
import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import Image from "next/image";
import { useCartStore } from "@/store/useCartStore";
import { useAuthStore } from "@/store/useAuthStore";
import { Button } from "@/components/ui/button";
import KRGlue from "@lyracom/embedded-form-glue";
import { toast } from "sonner";

export default function CheckoutPage() {
  const router = useRouter();
  const { items, totalPrice, totalItems, clearCart } = useCartStore();
  const { user } = useAuthStore();

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [formData, setFormData] = useState({
    shipping_name: "",
    shipping_email: "",
    shipping_phone: "",
    shipping_address: "",
    shipping_city: "",
  });

  const [couponCode, setCouponCode] = useState("");
  const [appliedCoupon, setAppliedCoupon] = useState("");
  const [discountAmount, setDiscountAmount] = useState(0);
  const [couponError, setCouponError] = useState("");
  const [validatingCoupon, setValidatingCoupon] = useState(false);

  const [paymentMethod, setPaymentMethod] = useState("izipay");
  const [izipayFormToken, setIzipayFormToken] = useState("");
  const [showIzipayForm, setShowIzipayForm] = useState(false);
  const [izipayLoading, setIzipayLoading] = useState(true);
  const [orderCreated, setOrderCreated] = useState<string | null>(null);

  // Prellenar si el usuario está logueado
  useEffect(() => {
    if (user) {
      setFormData((prev) => ({
        ...prev,
        shipping_name: user.name || "",
        shipping_email: user.email || "",
      }));
    }
  }, [user]);

  // Si el carrito está vacío, redirigir al catálogo
  useEffect(() => {
    if (items.length === 0) {
      router.push("/productos");
    }
  }, [items, router]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleApplyCoupon = async () => {
    if (!couponCode.trim()) return;
    
    setValidatingCoupon(true);
    setCouponError("");
    
    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/checkout/validate-coupon`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          ...(useAuthStore.getState().token ? { "Authorization": `Bearer ${useAuthStore.getState().token}` } : {})
        },
        body: JSON.stringify({
          coupon_code: couponCode,
          total_amount: totalPrice(),
        }),
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data.message || "Cupón inválido");
      }

      if (data.valid) {
        setDiscountAmount(data.discount);
        setAppliedCoupon(couponCode);
      }
    } catch (err: any) {
      setCouponError(err.message);
      setDiscountAmount(0);
      setAppliedCoupon("");
    } finally {
      setValidatingCoupon(false);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError("");

    try {
      const payload = {
        ...formData,
        items: items.map(item => ({ id: item.id, quantity: item.quantity })),
        coupon_code: appliedCoupon || null,
        payment_method: paymentMethod,
      };

      const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/checkout`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          ...(useAuthStore.getState().token ? { "Authorization": `Bearer ${useAuthStore.getState().token}` } : {})
        },
        body: JSON.stringify(payload),
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data.message || "Error al procesar el pedido");
      }

      // Si Mercado Pago nos devolvió el link de pago, redirigimos
      if (data.payment_method === 'mercadopago' && data.init_point) {
        clearCart();
        window.location.href = data.init_point;
      } 
      // Si Izipay devuelve token, inicializamos el formulario embebido
      else if (data.payment_method === 'izipay' && data.form_token) {
        setIzipayFormToken(data.form_token);
        setOrderCreated(data.order_number);
        setShowIzipayForm(true);
        initIzipay(data.form_token, data.order_number);
      } else {
        clearCart();
        router.push(`/checkout/success?order=${data.order_number}`);
      }

    } catch (err: any) {
      setError(err.message);
      setLoading(false);
    }
  };

  const initIzipay = async (formToken: string, orderNumber: string) => {
    try {
      const { KR } = await KRGlue.loadLibrary(
        "https://api.micuentaweb.pe",
        "18265624:testpublickey_hBeKMJ3VoHvalBJBnNvpMHgWkzrMkjt4m7Oxzo3m8eWK2"
      );

      await KR.setFormConfig({
        formToken: formToken,
        'kr-language': 'es-ES',
      });

      // Handle successful payment
      KR.onSubmit(async (paymentData: any) => {
        if (paymentData.clientAnswer.orderStatus === "PAID") {
          try {
            // Avisar al backend localmente para que marque como pagado
            const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/checkout/verify-izipay`, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                "kr-answer": JSON.stringify(paymentData.clientAnswer),
                "kr-hash": paymentData.hash
              })
            });
            
            if (res.ok) {
              router.push(`/checkout/success?order_id=${orderNumber}&status=approved`);
            } else {
              const errData = await res.json().catch(() => ({}));
              console.error('Backend rejected payment verification:', errData);
              toast.error("No se pudo validar el pago en el servidor.");
            }
          } catch (e) {
            console.error('Error verifying locally', e);
            toast.error("Error de conexión al validar el pago.");
          }
        } else {
           toast.error("El pago no fue procesado o fue denegado.");
        }
        return false; 
      });

      // Show form in our custom modal
      await KR.attachForm("#izipay-form-container");
      await KR.showForm("#izipay-form-container");
      setIzipayLoading(false);
      
      
    } catch (error) {
      console.error("Izipay loading error", error);
      setError("No se pudo cargar la pasarela de pagos.");
    } finally {
      setLoading(false);
    }
  };

  if (items.length === 0) return null;

  return (
    <div className="min-h-screen bg-muted/20 py-10 relative">
      {/* Izipay Theme & Customizations */}
      <link rel="stylesheet" href="https://api.micuentaweb.pe/static/js/krypton-client/V4.0/ext/classic-reset.css" />
      <script src="https://api.micuentaweb.pe/static/js/krypton-client/V4.0/ext/classic.js" async></script>
      <style>{`
        /* Personalización de colores Izipay para que coincida con el verde de la tienda */
        .kr-embedded .kr-payment-button {
          background-color: #10b981 !important; /* Verde primario */
          color: white !important;
          border-radius: 8px !important;
          font-weight: bold !important;
          text-transform: uppercase;
        }
        .kr-embedded .kr-payment-button:hover {
          background-color: #059669 !important;
        }
      `}</style>

      {/* Modal Overlay Personalizado para Izipay */}
      {showIzipayForm && (
        <div className="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div className="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl relative animate-in fade-in zoom-in duration-300">
            <button 
              onClick={() => {
                setShowIzipayForm(false);
              }}
              className="absolute top-4 right-4 text-gray-400 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-full w-8 h-8 flex items-center justify-center transition-colors z-50"
            >
              ✕
            </button>
            <h2 className="text-xl font-bold mb-6 text-center text-gray-800">Completa tu pago seguro</h2>
            <div className="flex justify-center w-full min-h-[300px] relative">
              {izipayLoading && (
                <div className="absolute inset-0 flex flex-col items-center justify-center text-gray-400">
                  <div className="w-10 h-10 border-4 border-green-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                  <p className="text-sm font-medium">Cargando pasarela segura...</p>
                </div>
              )}
              <div id="izipay-form-container" className={`w-full flex justify-center relative z-10 transition-opacity duration-500 ${izipayLoading ? 'opacity-0' : 'opacity-100'}`}>
                <div className="kr-embedded w-full bg-white"></div>
              </div>
            </div>
            <div className="mt-6 flex justify-center items-center gap-2 text-xs text-gray-500">
              <svg className="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
              Pago procesado de forma segura por Izipay
            </div>
          </div>
        </div>
      )}
      <div className="max-w-6xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-12 gap-10">
        
        {/* Formulario */}
        <div className="lg:col-span-7 space-y-8">
          <div className="bg-background rounded-3xl p-8 border shadow-sm">
            <h2 className="text-2xl font-bold mb-6">Datos de Envío</h2>
            
            {error && (
              <div className="mb-6 p-4 bg-destructive/10 text-destructive rounded-xl text-sm font-medium">
                {error}
              </div>
            )}

            <form id="checkout-form" onSubmit={handleSubmit} className="space-y-5">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                  <label className="block text-sm font-medium mb-2">Nombre completo</label>
                  <input required name="shipping_name" value={formData.shipping_name} onChange={handleChange} className="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-primary outline-none" />
                </div>
                <div>
                  <label className="block text-sm font-medium mb-2">Correo electrónico</label>
                  <input required type="email" name="shipping_email" value={formData.shipping_email} onChange={handleChange} className="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-primary outline-none" />
                </div>
              </div>
              <div>
                <label className="block text-sm font-medium mb-2">Teléfono</label>
                <input required type="tel" name="shipping_phone" value={formData.shipping_phone} onChange={handleChange} className="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-primary outline-none" />
              </div>
              <div>
                <label className="block text-sm font-medium mb-2">Dirección de entrega</label>
                <input required name="shipping_address" value={formData.shipping_address} onChange={handleChange} placeholder="Calle, Número, Depto" className="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-primary outline-none" />
              </div>
              <div>
                <label className="block text-sm font-medium mb-2">Ciudad / Distrito</label>
                <input required name="shipping_city" value={formData.shipping_city} onChange={handleChange} className="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-primary outline-none" />
              </div>
            </form>
          </div>
        </div>

        {/* Resumen */}
        <div className="lg:col-span-5">
          <div className="bg-background rounded-3xl p-8 border shadow-sm sticky top-24">
            <h2 className="text-xl font-bold mb-6">Resumen del Pedido</h2>
            
            <div className="space-y-4 mb-6">
              {items.map(item => (
                <div key={item.id} className="flex gap-4 items-center">
                  <div className="relative w-16 h-16 rounded-lg overflow-hidden bg-muted border flex-shrink-0">
                    <Image 
                      src={item.image_url 
                        ? (item.image_url.startsWith('http') ? item.image_url : `${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/storage/${item.image_url}`)
                        : "https://images.unsplash.com/photo-1584308666744-24d5e47ac9db?q=80&w=600&auto=format&fit=crop"} 
                      alt={item.name} 
                      fill 
                      className="object-cover"
                    />
                    <span className="absolute top-0 right-0 bg-primary text-white text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-bl-lg">
                      {item.quantity}
                    </span>
                  </div>
                  <div className="flex-1">
                    <h4 className="text-sm font-medium line-clamp-2">{item.name}</h4>
                  </div>
                  <div className="font-semibold text-sm">
                    S/ {(parseFloat(item.price) * item.quantity).toFixed(2)}
                  </div>
                </div>
              ))}
            </div>

            {/* Coupon Input */}
            <div className="mb-6">
              <label className="block text-sm font-medium mb-2">¿Tienes un código de descuento?</label>
              <div className="flex gap-2">
                <input 
                  value={couponCode} 
                  onChange={(e) => setCouponCode(e.target.value)} 
                  placeholder="Ingresa tu cupón" 
                  className="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-primary outline-none uppercase" 
                />
                <Button 
                  type="button" 
                  onClick={handleApplyCoupon} 
                  disabled={validatingCoupon || !couponCode}
                  variant="secondary"
                  className="rounded-xl"
                >
                  {validatingCoupon ? "..." : "Aplicar"}
                </Button>
              </div>
              {couponError && <p className="text-destructive text-xs mt-2">{couponError}</p>}
              {appliedCoupon && !couponError && <p className="text-success text-xs mt-2 text-green-600 font-medium">Cupón {appliedCoupon} aplicado (-S/ {discountAmount.toFixed(2)})</p>}
            </div>

            <div className="border-t pt-4 space-y-3 mb-6 text-sm">
              <div className="flex justify-between text-muted-foreground">
                <span>Subtotal ({totalItems()} ítems)</span>
                <span>S/ {totalPrice().toFixed(2)}</span>
              </div>
              <div className="flex justify-between text-muted-foreground">
                <span>Envío</span>
                <span>Gratis</span>
              </div>
              {discountAmount > 0 && (
                <div className="flex justify-between text-green-600 font-medium">
                  <span>Descuento ({appliedCoupon})</span>
                  <span>- S/ {discountAmount.toFixed(2)}</span>
                </div>
              )}
              <div className="flex justify-between font-bold text-lg pt-3 border-t">
                <span>Total</span>
                <span>S/ {Math.max(0, totalPrice() - discountAmount).toFixed(2)}</span>
              </div>
            </div>

            {/* Selector de Método de Pago */}
            <div className="mb-8">
              <label className="block text-sm font-bold mb-3">Método de Pago</label>
              <div className="space-y-3">
                <label className={`flex items-center gap-3 p-4 border rounded-xl cursor-pointer transition-all ${paymentMethod === 'izipay' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'hover:bg-gray-50'}`}>
                  <input 
                    type="radio" 
                    name="payment_method" 
                    value="izipay" 
                    checked={paymentMethod === 'izipay'}
                    onChange={(e) => setPaymentMethod(e.target.value)}
                    className="w-4 h-4 text-primary focus:ring-primary"
                  />
                  <div className="flex-1">
                    <span className="font-semibold block">Pago Seguro con Tarjeta</span>
                    <span className="text-xs text-muted-foreground">Visa, Mastercard, Yape, Plin (Vía Izipay)</span>
                  </div>
                </label>
                
                <label className={`flex items-center gap-3 p-4 border rounded-xl cursor-pointer transition-all ${paymentMethod === 'mercadopago' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'hover:bg-gray-50'}`}>
                  <input 
                    type="radio" 
                    name="payment_method" 
                    value="mercadopago" 
                    checked={paymentMethod === 'mercadopago'}
                    onChange={(e) => setPaymentMethod(e.target.value)}
                    className="w-4 h-4 text-primary focus:ring-primary"
                  />
                  <div className="flex-1">
                    <span className="font-semibold block">Mercado Pago</span>
                    <span className="text-xs text-muted-foreground">Paga con tu cuenta de Mercado Pago o tarjeta</span>
                  </div>
                </label>
              </div>
            </div>

            <div className="pt-6">
                <Button 
                  type="submit" 
                  form="checkout-form"
                  className="w-full h-14 text-lg rounded-xl shadow-lg hover:shadow-xl transition-all" 
                  disabled={
                    loading || 
                    !formData.shipping_name.trim() || 
                    !formData.shipping_email.trim() || 
                    !formData.shipping_phone.trim() || 
                    !formData.shipping_address.trim() || 
                    !formData.shipping_city.trim()
                  }
                >
                  {loading ? "Procesando de forma segura..." : "Completar Pedido"}
                </Button>
              </div>
          </div>
        </div>

      </div>
    </div>
  );
}
