"use client"
import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import Image from "next/image";
import { useCartStore } from "@/store/useCartStore";
import { useAuthStore } from "@/store/useAuthStore";
import { Button } from "@/components/ui/button";

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

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError("");

    try {
      const payload = {
        ...formData,
        items: items.map(item => ({ id: item.id, quantity: item.quantity }))
      };

      const res = await fetch("http://localhost:8000/api/checkout", {
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

      // Vaciar carrito
      clearCart();

      // Si Mercado Pago nos devolvió el link de pago, redirigimos
      if (data.init_point) {
        window.location.href = data.init_point;
      } else {
        router.push(`/checkout/success?order=${data.order_number}`);
      }

    } catch (err: any) {
      setError(err.message);
      setLoading(false);
    }
  };

  if (items.length === 0) return null;

  return (
    <div className="min-h-screen bg-muted/20 py-10">
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
                      src={item.image_url ? `http://localhost:8000/storage/${item.image_url}` : "https://images.unsplash.com/photo-1584308666744-24d5e47ac9db?q=80&w=600&auto=format&fit=crop"} 
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

            <div className="border-t pt-4 space-y-3 mb-6 text-sm">
              <div className="flex justify-between text-muted-foreground">
                <span>Subtotal ({totalItems()} ítems)</span>
                <span>S/ {totalPrice().toFixed(2)}</span>
              </div>
              <div className="flex justify-between text-muted-foreground">
                <span>Envío</span>
                <span>Calculado en el próximo paso</span>
              </div>
              <div className="flex justify-between font-bold text-lg pt-3 border-t">
                <span>Total</span>
                <span>S/ {totalPrice().toFixed(2)}</span>
              </div>
            </div>

            <Button 
              type="submit" 
              form="checkout-form"
              disabled={loading} 
              size="lg" 
              className="w-full rounded-2xl h-14 text-lg shadow-md"
            >
              {loading ? "Procesando..." : "Confirmar Pedido"}
            </Button>
          </div>
        </div>

      </div>
    </div>
  );
}
