"use client"
import React, { useState, useEffect } from "react";
import { useAuthStore } from "@/store/useAuthStore";
import { useAddressStore } from "@/store/useAddressStore";
import { toast } from "sonner";
import { Plus, MapPin, Check, Trash2, Edit2, Star } from "lucide-react";
import { Button } from "@/components/ui/button";
import dynamic from "next/dynamic";

const AddressMapSelector = dynamic(() => import("@/components/AddressMapSelector").then(mod => mod.AddressMapSelector), { ssr: false });

export interface UserAddress {
  id: number;
  alias: string;
  recipient_name: string;
  phone: string;
  department: string;
  province: string;
  district: string;
  address: string;
  reference?: string;
  postal_code?: string;
  is_default: boolean;
}

export function UserAddressesManager() {
  const { token, user } = useAuthStore();
  const [addresses, setAddresses] = useState<UserAddress[]>([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [saving, setSaving] = useState(false);

  const initialForm = {
    alias: "Casa",
    recipient_name: user?.name || "",
    phone: user?.phone || "",
    department: "La Libertad",
    province: "Chepén",
    district: "Chepén",
    address: "",
    reference: "",
    postal_code: "",
    is_default: false,
  };

  const [form, setForm] = useState(initialForm);

  const fetchAddresses = async () => {
    if (!token) return;
    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/user/addresses`, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Accept": "application/json",
        },
      });
      if (res.ok) {
        const data = await res.json();
        setAddresses(data);
        useAddressStore.getState().fetchAddresses();
      }
    } catch (err) {
      console.error("Error cargando direcciones:", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchAddresses();
  }, [token]);

  const openNewModal = () => {
    setEditingId(null);
    setForm({
      ...initialForm,
      recipient_name: user?.name || "",
      phone: user?.phone || "",
      is_default: addresses.length === 0,
    });
    setShowModal(true);
  };

  const openEditModal = (addr: UserAddress) => {
    setEditingId(addr.id);
    setForm({
      alias: addr.alias || "Casa",
      recipient_name: addr.recipient_name,
      phone: addr.phone,
      department: addr.department,
      province: addr.province,
      district: addr.district,
      address: addr.address,
      reference: addr.reference || "",
      postal_code: addr.postal_code || "",
      is_default: addr.is_default,
    });
    setShowModal(true);
  };

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!token) return;
    setSaving(true);

    try {
      const url = editingId
        ? `${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/user/addresses/${editingId}`
        : `${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/user/addresses`;
      
      const method = editingId ? "PUT" : "POST";

      const res = await fetch(url, {
        method,
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
          "Accept": "application/json",
        },
        body: JSON.stringify(form),
      });

      if (res.ok) {
        toast.success(editingId ? "Dirección actualizada" : "Dirección guardada correctamente");
        setShowModal(false);
        fetchAddresses();
      } else {
        const errData = await res.json();
        toast.error(errData.message || "Error al guardar la dirección");
      }
    } catch (err) {
      console.error("Error guardando dirección:", err);
      toast.error("Error de conexión");
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm("¿Estás seguro de eliminar esta dirección?")) return;
    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/user/addresses/${id}`, {
        method: "DELETE",
        headers: {
          "Authorization": `Bearer ${token}`,
          "Accept": "application/json",
        },
      });
      if (res.ok) {
        toast.success("Dirección eliminada");
        fetchAddresses();
      }
    } catch (err) {
      console.error("Error eliminando dirección:", err);
    }
  };

  const handleSetDefault = async (id: number) => {
    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/user/addresses/${id}/default`, {
        method: "PATCH",
        headers: {
          "Authorization": `Bearer ${token}`,
          "Accept": "application/json",
        },
      });
      if (res.ok) {
        toast.success("Dirección predeterminada actualizada");
        fetchAddresses();
      }
    } catch (err) {
      console.error("Error cambiando predeterminada:", err);
    }
  };

  if (loading) {
    return <div className="py-8 text-center text-muted-foreground">Cargando direcciones...</div>;
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h3 className="text-xl font-bold text-foreground">Mis Direcciones Guardadas</h3>
          <p className="text-sm text-muted-foreground">Administra tus direcciones de envío para un pago más rápido (estilo Mercado Libre).</p>
        </div>
        <Button onClick={openNewModal} className="rounded-xl flex items-center gap-2">
          <Plus className="w-4 h-4" />
          Nueva Dirección
        </Button>
      </div>

      {addresses.length === 0 ? (
        <div className="bg-background rounded-3xl p-8 border border-dashed text-center">
          <MapPin className="w-12 h-12 text-muted-foreground mx-auto mb-3 opacity-50" />
          <h4 className="font-bold text-lg mb-1">No tienes direcciones guardadas</h4>
          <p className="text-sm text-muted-foreground mb-4">Añade tu casa, oficina o trabajo para comprar con un solo clic.</p>
          <Button onClick={openNewModal} variant="outline" className="rounded-xl">Agregar Dirección</Button>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {addresses.map((addr) => (
            <div
              key={addr.id}
              className={`relative bg-background p-5 rounded-2xl border transition-all ${
                addr.is_default 
                  ? "border-emerald-500 ring-2 ring-emerald-500/20 shadow-sm" 
                  : "border-border hover:border-gray-400 dark:hover:border-zinc-700"
              }`}
            >
              <div className="flex items-start justify-between mb-2">
                <div className="flex items-center gap-2">
                  <span className="font-extrabold text-sm uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-muted text-foreground">
                    {addr.alias || "Casa"}
                  </span>
                  {addr.is_default && (
                    <span className="bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                      <Star className="w-3 h-3 fill-emerald-500 text-emerald-500" />
                      Predeterminada
                    </span>
                  )}
                </div>
                <div className="flex items-center gap-1">
                  <button
                    onClick={() => openEditModal(addr)}
                    className="p-1.5 text-muted-foreground hover:text-foreground rounded-lg hover:bg-muted transition-colors"
                    title="Editar"
                  >
                    <Edit2 className="w-4 h-4" />
                  </button>
                  <button
                    onClick={() => handleDelete(addr.id)}
                    className="p-1.5 text-muted-foreground hover:text-rose-500 rounded-lg hover:bg-muted transition-colors"
                    title="Eliminar"
                  >
                    <Trash2 className="w-4 h-4" />
                  </button>
                </div>
              </div>

              <div className="space-y-1 text-sm text-foreground/90">
                <p className="font-bold">{addr.recipient_name} <span className="text-muted-foreground font-normal">({addr.phone})</span></p>
                <p className="leading-snug">{addr.address}</p>
                {addr.reference && <p className="text-xs text-muted-foreground italic">Ref: {addr.reference}</p>}
                <p className="text-xs text-muted-foreground pt-1 border-t border-border/50 mt-2">
                  {addr.district}, {addr.province} - {addr.department}
                </p>
              </div>

              {!addr.is_default && (
                <div className="mt-4 pt-3 border-t flex justify-end">
                  <button
                    onClick={() => handleSetDefault(addr.id)}
                    className="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1"
                  >
                    <Check className="w-3.5 h-3.5" />
                    Establecer como predeterminada
                  </button>
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {/* Modal */}
      {showModal && (
        <div 
          onClick={() => setShowModal(false)}
          className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 cursor-pointer"
        >
          <div 
            onClick={(e) => e.stopPropagation()}
            className="bg-background rounded-3xl max-w-lg w-full p-6 shadow-2xl border max-h-[90vh] overflow-y-auto cursor-default"
          >
            <h3 className="text-xl font-bold mb-4">
              {editingId ? "Editar Dirección" : "Nueva Dirección"}
            </h3>
            <form onSubmit={handleSave} className="space-y-4">
              <AddressMapSelector
                selectedDepartment={form.department}
                selectedProvince={form.province}
                selectedDistrict={form.district}
                onSelectLocation={(loc) => {
                  setForm((prev) => ({
                    ...prev,
                    address: loc.address || prev.address,
                    district: loc.district || prev.district,
                    province: loc.province || prev.province,
                    department: loc.department || prev.department,
                  }));
                }}
              />
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-bold mb-1">Alias / Nombre de lugar</label>
                  <input
                    type="text"
                    required
                    placeholder="Ej: Casa, Trabajo, Oficina"
                    value={form.alias}
                    onChange={(e) => setForm({ ...form, alias: e.target.value })}
                    className="w-full px-3 py-2 border rounded-xl text-sm bg-background focus:ring-2 focus:ring-primary outline-none"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold mb-1">Teléfono de contacto</label>
                  <input
                    type="tel"
                    required
                    pattern="[0-9]{9}"
                    maxLength={9}
                    placeholder="999888777"
                    value={form.phone}
                    onChange={(e) => setForm({ ...form, phone: e.target.value.replace(/\D/g, "") })}
                    className="w-full px-3 py-2 border rounded-xl text-sm bg-background focus:ring-2 focus:ring-primary outline-none"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold mb-1">Nombre de quien recibe</label>
                <input
                  type="text"
                  required
                  placeholder="Nombre y Apellidos"
                  value={form.recipient_name}
                  onChange={(e) => setForm({ ...form, recipient_name: e.target.value })}
                  className="w-full px-3 py-2 border rounded-xl text-sm bg-background focus:ring-2 focus:ring-primary outline-none"
                />
              </div>

              <div className="grid grid-cols-3 gap-2">
                <div>
                  <label className="block text-xs font-bold mb-1">Departamento</label>
                  <input
                    type="text"
                    required
                    value={form.department}
                    onChange={(e) => setForm({ ...form, department: e.target.value })}
                    className="w-full px-3 py-2 border rounded-xl text-sm bg-background focus:ring-2 focus:ring-primary outline-none"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold mb-1">Provincia</label>
                  <input
                    type="text"
                    required
                    value={form.province}
                    onChange={(e) => setForm({ ...form, province: e.target.value })}
                    className="w-full px-3 py-2 border rounded-xl text-sm bg-background focus:ring-2 focus:ring-primary outline-none"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold mb-1">Distrito</label>
                  <input
                    type="text"
                    required
                    value={form.district}
                    onChange={(e) => setForm({ ...form, district: e.target.value })}
                    className="w-full px-3 py-2 border rounded-xl text-sm bg-background focus:ring-2 focus:ring-primary outline-none"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold mb-1">Dirección exacta (Calle, número, Mz, Lt)</label>
                <input
                  type="text"
                  required
                  placeholder="Ej: Av. Las Flores 123 - Dpto 402"
                  value={form.address}
                  onChange={(e) => setForm({ ...form, address: e.target.value })}
                  className="w-full px-3 py-2 border rounded-xl text-sm bg-background focus:ring-2 focus:ring-primary outline-none"
                />
              </div>

              <div>
                <label className="block text-xs font-bold mb-1">Referencia (Opcional)</label>
                <input
                  type="text"
                  placeholder="Ej: Frente al parque, al lado de la farmacia"
                  value={form.reference}
                  onChange={(e) => setForm({ ...form, reference: e.target.value })}
                  className="w-full px-3 py-2 border rounded-xl text-sm bg-background focus:ring-2 focus:ring-primary outline-none"
                />
              </div>

              <div className="flex items-center gap-2 pt-2">
                <input
                  type="checkbox"
                  id="default_chk"
                  checked={form.is_default}
                  onChange={(e) => setForm({ ...form, is_default: e.target.checked })}
                  className="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500"
                />
                <label htmlFor="default_chk" className="text-sm font-medium cursor-pointer">
                  Establecer como dirección de envío predeterminada
                </label>
              </div>

              <div className="flex gap-3 pt-4 border-t">
                <Button type="submit" disabled={saving} className="flex-1 rounded-xl">
                  {saving ? "Guardando..." : "Guardar Dirección"}
                </Button>
                <Button type="button" variant="outline" onClick={() => setShowModal(false)} className="rounded-xl">
                  Cancelar
                </Button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
