"use client"
import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import Image from "next/image";
import { useCartStore } from "@/store/useCartStore";
import { useAuthStore } from "@/store/useAuthStore";
import { useAddressStore } from "@/store/useAddressStore";
import { Button } from "@/components/ui/button";
import KRGlue from "@lyracom/embedded-form-glue";
import { toast } from "sonner";
import ubigeosData from "@/data/ubigeos_peru.json";

export default function CheckoutPage() {
  const router = useRouter();
  const { items, totalPrice, totalItems, clearCart } = useCartStore();
  const { user } = useAuthStore();

  const [loading, setLoading] = useState(false);
  const [processingPayment, setProcessingPayment] = useState(false);
  const [error, setError] = useState("");
  const [formData, setFormData] = useState({
    shipping_name: "",
    shipping_email: "",
    shipping_phone: "",
    shipping_address: "",
    shipping_department: "",
    shipping_province: "",
    shipping_district: "",
    shipping_postal_code: "",
  });

  const [savedAddresses, setSavedAddresses] = useState<any[]>([]);
  const [selectedAddressId, setSelectedAddressId] = useState<number | null>(null);
  const [saveNewAddress, setSaveNewAddress] = useState(false);
  const [newAddressAlias, setNewAddressAlias] = useState("Casa");

  // Autocomplete lists
  const departments = Array.from(new Set(ubigeosData.map(u => u.department))).sort();
  const provinces = formData.shipping_department 
    ? Array.from(new Set(ubigeosData.filter(u => u.department === formData.shipping_department).map(u => u.province))).sort()
    : [];
  const districts = formData.shipping_province
    ? Array.from(new Set(ubigeosData.filter(u => u.department === formData.shipping_department && u.province === formData.shipping_province).map(u => u.district))).sort()
    : [];

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

  const handleSelectAddress = (addr: any) => {
    setSelectedAddressId(addr.id);
    setSaveNewAddress(false);
    setFormData(prev => ({
      ...prev,
      shipping_name: addr.recipient_name || prev.shipping_name,
      shipping_phone: addr.phone || prev.shipping_phone,
      shipping_department: addr.department || "",
      shipping_province: addr.province || "",
      shipping_district: addr.district || "",
      shipping_address: addr.address || "",
      shipping_postal_code: addr.postal_code || "",
    }));
  };

  // Prellenar si el usuario está logueado y cargar sus direcciones guardadas
  useEffect(() => {
    if (user) {
      setFormData((prev) => ({
        ...prev,
        shipping_name: user.name || "",
        shipping_email: user.email || "",
      }));

      const token = useAuthStore.getState().token;
      if (token) {
        fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/user/addresses`, {
          headers: {
            "Authorization": `Bearer ${token}`,
            "Accept": "application/json"
          }
        })
        .then(res => res.ok ? res.json() : [])
        .then(data => {
          if (Array.isArray(data) && data.length > 0) {
            setSavedAddresses(data);
            useAddressStore.getState().fetchAddresses();
            const sel = useAddressStore.getState().selectedAddress || data.find(a => a.is_default) || data[0];
            if (sel) {
              handleSelectAddress(sel);
              toast.success("📍 Dirección seleccionada automáticamente", {
                description: `Enviando a ${sel.alias || 'tu dirección'}: ${sel.address}, ${sel.district}.`,
              });
            }
          } else {
            setSaveNewAddress(true);
          }
        })
        .catch(err => console.error("Error cargando direcciones en checkout:", err));
      }
    }
  }, [user]);

  const [cartHydrated, setCartHydrated] = useState(false);

  useEffect(() => {
    if (useCartStore.persist.hasHydrated()) {
      setCartHydrated(true);
    } else {
      const unsub = useCartStore.persist.onFinishHydration(() => {
        setCartHydrated(true);
      });
      return () => unsub();
    }
  }, []);

  // Si el carrito está vacío DESPUÉS de que Zustand se haya hidratado desde localStorage, redirigir al catálogo
  useEffect(() => {
    if (cartHydrated && items.length === 0) {
      router.push("/productos");
    }
  }, [cartHydrated, items, router]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handlePhoneChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const val = e.target.value.replace(/\D/g, '');
    setFormData({ ...formData, shipping_phone: val });
  };

  const handlePostalCodeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const code = e.target.value.replace(/\D/g, '').substring(0, 6);
    setFormData(prev => ({ ...prev, shipping_postal_code: code }));
    
    if (code.length === 6) {
      const match = ubigeosData.find(u => u.postal_code === code);
      if (match) {
        setFormData(prev => ({
          ...prev,
          shipping_department: match.department,
          shipping_province: match.province,
          shipping_district: match.district,
        }));
      }
    }
  };

  const handleDistrictChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const district = e.target.value;
    const match = ubigeosData.find(u => 
      u.department === formData.shipping_department &&
      u.province === formData.shipping_province &&
      u.district === district
    );
    setFormData(prev => ({
      ...prev,
      shipping_district: district,
      shipping_postal_code: match ? match.postal_code : prev.shipping_postal_code
    }));
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
    setProcessingPayment(true);
    setError("");

    try {
      if ((saveNewAddress || selectedAddressId === null) && useAuthStore.getState().token) {
        try {
          await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/user/addresses`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "Authorization": `Bearer ${useAuthStore.getState().token}`,
              "Accept": "application/json"
            },
            body: JSON.stringify({
              alias: newAddressAlias || "Casa",
              recipient_name: formData.shipping_name,
              phone: formData.shipping_phone,
              department: formData.shipping_department,
              province: formData.shipping_province,
              district: formData.shipping_district,
              address: formData.shipping_address,
              postal_code: formData.shipping_postal_code,
              is_default: savedAddresses.length === 0
            })
          });
        } catch (e) {
          console.error("Error guardando nueva dirección", e);
        }
      }

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
        setProcessingPayment(false);
        setShowIzipayForm(true);
        initIzipay(data.form_token, data.order_number);
      } else {
        clearCart();
        router.push(`/checkout/success?order=${data.order_number}&email=${encodeURIComponent(formData.shipping_email)}`);
      }

    } catch (err: any) {
      setError(err.message);
      toast.error(err.message, { duration: 2500 });
      setLoading(false);
      setProcessingPayment(false);
    }
  };

  const initIzipay = async (formToken: string, orderNumber: string) => {
    try {
      const { KR } = await KRGlue.loadLibrary(
        "https://api.micuentaweb.pe",
        process.env.NEXT_PUBLIC_IZIPAY_PUBLIC_KEY || "18265624:testpublickey_hBeKMJ3VoHvalBJBnNvpMHgWkzrMkjt4m7Oxzo3m8eWK2"
      );

      await KR.setFormConfig({
        formToken: formToken,
        'kr-language': 'es-ES',
      });

      // Handle successful payment
      KR.onSubmit(async (paymentData: any) => {
        if (paymentData.clientAnswer.orderStatus === "PAID") {
          try {
            setShowIzipayForm(false);
            setProcessingPayment(true);
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
              router.push(`/checkout/success?order_id=${orderNumber}&status=approved&email=${encodeURIComponent(formData.shipping_email)}`);
            } else {
              setProcessingPayment(false);
              const errData = await res.json().catch(() => ({}));
              console.error('Backend rejected payment verification:', errData);
              toast.error("No se pudo validar el pago en el servidor.");
            }
          } catch (e) {
            setProcessingPayment(false);
            console.error('Error verifying locally', e);
            toast.error("Error de conexión al validar el pago.");
          }
        } else {
           const tx = paymentData?.clientAnswer?.transactions?.[0];
           const errorMsg = tx?.detailedErrorMessage || paymentData?.clientAnswer?.errorMessage || "El pago no fue procesado o fue denegado.";
           toast.error(`Pago rechazado: ${errorMsg}`, { duration: 2500 });
        }
        return false; 
      });

      KR.onError(async (errorData: any) => {
        const errorMsg = errorData?.detailedErrorMessage || errorData?.errorMessage || "Revise los datos de su tarjeta e intente nuevamente.";
        toast.error(`Error: ${errorMsg}`, { duration: 2500 });
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

      {/* Modal / Overlay de Procesando Pago (Estilo input_file_0) */}
      {(loading || processingPayment) && !showIzipayForm && (
        <div className="fixed inset-0 z-[10000] flex items-center justify-center bg-black/60 backdrop-blur-md p-4 animate-in fade-in duration-300">
          <div className="bg-white rounded-3xl p-10 max-w-md w-full shadow-2xl flex flex-col items-center text-center relative overflow-hidden animate-in zoom-in-95 duration-300">
            {/* Ícono superior */}
            <div className="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mb-6 shadow-inner">
              <svg className="w-10 h-10 text-gray-700 animate-pulse" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8 5v14l11-7z" />
              </svg>
            </div>

            <h3 className="text-2xl font-extrabold text-gray-900 mb-2 tracking-tight">¡Ya falta poco!</h3>
            <p className="text-gray-600 text-base font-medium mb-8">Estamos procesando tu pago</p>

            {/* Barra animada con colores de la marca (Verde, Carbón y Menta) */}
            <div className="w-full max-w-[280px] flex gap-1.5 overflow-hidden">
              <div className="h-2 flex-1 rounded-full bg-[#111827] animate-[pulse_1.2s_ease-in-out_infinite]"></div>
              <div className="h-2 flex-1 rounded-full bg-[#047857] animate-[pulse_1.2s_ease-in-out_0.2s_infinite]"></div>
              <div className="h-2 flex-1 rounded-full bg-[#10b981] animate-[pulse_1.2s_ease-in-out_0.4s_infinite]"></div>
              <div className="h-2 flex-1 rounded-full bg-[#34d399] animate-[pulse_1.2s_ease-in-out_0.6s_infinite]"></div>
              <div className="h-2 flex-1 rounded-full bg-[#111827] animate-[pulse_1.2s_ease-in-out_0.8s_infinite]"></div>
            </div>
            
            <p className="text-xs text-gray-400 mt-6 font-medium animate-pulse">Por favor, no cierres ni recargues esta ventana...</p>
          </div>
        </div>
      )}

      <div className="max-w-6xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-12 gap-10">
        
        {/* Formulario */}
        <div className="lg:col-span-7 space-y-8">
          <div className="bg-background rounded-3xl p-8 border shadow-sm">
            <h2 className="text-2xl font-bold mb-6">Datos de Envío</h2>

            {/* Mercado Libre Style Address Selector */}
            {savedAddresses.length > 0 ? (
              <div className="mb-8 pb-6 border-b border-border/80">
                <div className="flex items-center justify-between mb-3">
                  <p className="text-xs font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
                    <span className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Mis direcciones guardadas (Estilo Mercado Libre)
                  </p>
                  <span className="text-[11px] font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-1 rounded-full border border-emerald-200 dark:border-emerald-800">
                    ⚡ Selección automática activa
                  </span>
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                  {savedAddresses.map((addr) => (
                    <div
                      key={addr.id}
                      onClick={() => handleSelectAddress(addr)}
                      className={`p-4 rounded-2xl border-2 cursor-pointer transition-all flex flex-col justify-between ${
                        selectedAddressId === addr.id
                          ? "border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 ring-2 ring-emerald-500/20 shadow-sm"
                          : "border-border hover:border-gray-400 bg-background"
                      }`}
                    >
                      <div>
                        <div className="flex items-center justify-between mb-1.5">
                          <div className="flex items-center gap-1.5">
                            <span className="font-black text-xs uppercase px-2.5 py-0.5 rounded-md bg-gray-900 text-white dark:bg-white dark:text-gray-900">
                              {addr.alias || "Casa"}
                            </span>
                            {addr.is_default && (
                              <span className="text-amber-500 text-[11px] font-extrabold flex items-center gap-0.5">
                                ★ Predeterminada
                              </span>
                            )}
                          </div>
                          {selectedAddressId === addr.id && (
                            <span className="text-emerald-600 dark:text-emerald-400 font-black text-xs flex items-center gap-1 bg-emerald-100 dark:bg-emerald-900/50 px-2 py-0.5 rounded-full">
                              ✓ Envío aquí
                            </span>
                          )}
                        </div>
                        <p className="font-bold text-sm text-foreground mt-1.5">{addr.recipient_name} <span className="font-normal text-muted-foreground">({addr.phone})</span></p>
                        <p className="text-xs text-muted-foreground line-clamp-1 mt-0.5 font-medium">{addr.address}</p>
                        <p className="text-[11px] text-muted-foreground/80 mt-0.5">{addr.district}, {addr.province}</p>
                      </div>
                    </div>
                  ))}
                  <div
                    onClick={() => {
                      setSelectedAddressId(null);
                      setSaveNewAddress(true);
                      setFormData(prev => ({
                        ...prev,
                        shipping_address: "",
                        shipping_department: "",
                        shipping_province: "",
                        shipping_district: "",
                        shipping_postal_code: "",
                      }));
                    }}
                    className={`p-4 rounded-2xl border-2 border-dashed cursor-pointer transition-all flex flex-col items-center justify-center text-center min-h-[110px] ${
                      selectedAddressId === null
                        ? "border-emerald-500 bg-emerald-50/30 dark:bg-emerald-950/10 text-emerald-600 dark:text-emerald-400 font-bold shadow-sm"
                        : "border-border hover:border-gray-400 text-muted-foreground"
                    }`}
                  >
                    <span className="text-2xl font-black mb-1">+</span>
                    <span className="text-xs font-bold">Enviar a una nueva dirección</span>
                  </div>
                </div>
              </div>
            ) : (
              <div className="mb-8 p-5 rounded-2xl bg-amber-50/80 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800 flex items-start gap-4">
                <div className="w-10 h-10 rounded-full bg-amber-500/10 text-amber-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                  <h4 className="font-bold text-sm text-foreground">Aún no tienes direcciones guardadas</h4>
                  <p className="text-xs text-muted-foreground mt-0.5 leading-relaxed">
                    Al completar tu pedido abajo, podrás marcar la casilla <span className="font-bold text-foreground">&quot;Guardar esta dirección&quot;</span> para que en tus próximas compras se seleccione y complete automáticamente como en Mercado Libre.
                  </p>
                </div>
              </div>
            )}
            
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
                <label className="block text-sm font-medium mb-2">Teléfono / Celular</label>
                <input 
                  required 
                  type="tel" 
                  pattern="[0-9]{9}" 
                  maxLength={9}
                  title="El número debe tener exactamente 9 dígitos"
                  name="shipping_phone" 
                  value={formData.shipping_phone} 
                  onChange={handlePhoneChange} 
                  className="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-primary outline-none" 
                  placeholder="Ej: 987654321"
                />
              </div>
              <div>
                <label className="block text-sm font-medium mb-2">Dirección de entrega</label>
                <input required name="shipping_address" value={formData.shipping_address} onChange={handleChange} placeholder="Calle, Número, Depto" className="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-primary outline-none" />
              </div>

              <div className="space-y-3 pt-2 border-t border-gray-100">
                <div className="flex items-center justify-between">
                  <span className="text-sm font-bold text-gray-900">Ubicación de entrega (Perú)</span>
                  <span className="text-xs text-muted-foreground">Selecciona tu zona o ingresa Ubigeo</span>
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  <div>
                    <label className="block text-xs font-semibold text-gray-700 mb-1.5">Departamento</label>
                    <select required name="shipping_department" value={formData.shipping_department} onChange={(e) => { handleChange(e); setFormData(p => ({...p, shipping_province: "", shipping_district: "", shipping_postal_code: ""})) }} className="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none bg-white text-sm font-medium transition-all">
                      <option value="">Seleccionar...</option>
                      {departments.map(d => <option key={d} value={d}>{d}</option>)}
                    </select>
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-gray-700 mb-1.5">Provincia</label>
                    <select required name="shipping_province" value={formData.shipping_province} onChange={(e) => { handleChange(e); setFormData(p => ({...p, shipping_district: "", shipping_postal_code: ""})) }} disabled={!formData.shipping_department} className="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none bg-white text-sm font-medium disabled:bg-gray-50 disabled:text-gray-400 transition-all">
                      <option value="">Seleccionar...</option>
                      {provinces.map(p => <option key={p} value={p}>{p}</option>)}
                    </select>
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-gray-700 mb-1.5">Distrito</label>
                    <select required name="shipping_district" value={formData.shipping_district} onChange={handleDistrictChange} disabled={!formData.shipping_province} className="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none bg-white text-sm font-medium disabled:bg-gray-50 disabled:text-gray-400 transition-all">
                      <option value="">Seleccionar...</option>
                      {districts.map(d => <option key={d} value={d}>{d}</option>)}
                    </select>
                  </div>
                </div>

                <div className="flex items-center gap-3 pt-1">
                  <div className="w-40 flex-shrink-0">
                    <label className="block text-xs font-semibold text-gray-700 mb-1">Ubigeo / C. Postal</label>
                    <input required name="shipping_postal_code" maxLength={6} value={formData.shipping_postal_code} onChange={handlePostalCodeChange} placeholder="Ej: 150101" className="w-full px-3.5 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm font-mono bg-gray-50/50 focus:bg-white transition-all" />
                  </div>
                  <p className="text-xs text-muted-foreground mt-4 leading-snug">
                    💡 <span className="font-medium">Se autocompleta al elegir tu distrito</span>, o puedes ingresarlo directamente para seleccionar tu zona.
                  </p>
                </div>

                {user && (
                  <div className="pt-4 border-t border-border mt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-muted/20 p-4 rounded-2xl">
                    <div className="flex items-center gap-2.5">
                      <input
                        type="checkbox"
                        id="save_addr_chk"
                        checked={saveNewAddress || selectedAddressId === null}
                        onChange={(e) => setSaveNewAddress(e.target.checked)}
                        className="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500"
                      />
                      <label htmlFor="save_addr_chk" className="text-sm font-semibold cursor-pointer text-foreground">
                        Guardar esta dirección en mi cuenta para futuras compras
                      </label>
                    </div>
                    {(saveNewAddress || selectedAddressId === null) && (
                      <div className="flex items-center gap-2">
                        <span className="text-xs font-bold text-muted-foreground">Alias:</span>
                        <input
                          type="text"
                          value={newAddressAlias}
                          onChange={(e) => setNewAddressAlias(e.target.value)}
                          placeholder="Ej: Casa, Oficina"
                          className="px-3 py-1 border rounded-xl text-xs w-28 bg-background font-medium outline-none focus:ring-2 focus:ring-primary"
                        />
                      </div>
                    )}
                  </div>
                )}
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
                    !formData.shipping_department.trim() ||
                    !formData.shipping_province.trim() ||
                    !formData.shipping_district.trim() ||
                    !formData.shipping_postal_code.trim()
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
