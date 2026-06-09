"use client"
import { useState } from "react";
import { useRouter } from "next/navigation";
import { useAuthStore } from "@/store/useAuthStore";
import { Button } from "@/components/ui/button";

export default function LoginPage() {
  const [email, setEmail] = useState("");
  const [code, setCode] = useState("");
  const [step, setStep] = useState<1 | 2>(1);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const router = useRouter();
  const setAuth = useAuthStore((state) => state.setAuth);

  const requestOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!email) return;
    setLoading(true);
    setError("");

    try {
      const res = await fetch("http://localhost:8000/api/auth/request-otp", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email }),
      });
      
      if (!res.ok) throw new Error("Error solicitando código");
      setStep(2);
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const verifyOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!code || code.length !== 6) return;
    setLoading(true);
    setError("");

    try {
      const res = await fetch("http://localhost:8000/api/auth/verify-otp", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, code }),
      });
      
      const data = await res.json();
      
      if (!res.ok) throw new Error(data.message || "Código inválido");
      
      setAuth(data.user, data.token);
      router.push("/");
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-muted/30 flex items-center justify-center p-6">
      <div className="bg-background max-w-md w-full rounded-3xl p-8 border shadow-lg">
        <div className="text-center mb-8">
          <h1 className="text-2xl font-bold">Iniciar Sesión</h1>
          <p className="text-muted-foreground mt-2">
            {step === 1 ? "Ingresa tu correo para recibir un código de acceso rápido." : "Ingresa el código de 6 dígitos enviado a tu correo."}
          </p>
        </div>

        {error && (
          <div className="bg-destructive/10 text-destructive p-3 rounded-xl text-sm mb-6 text-center">
            {error}
          </div>
        )}

        {step === 1 ? (
          <form onSubmit={requestOtp} className="space-y-4">
            <div>
              <label htmlFor="email" className="block text-sm font-medium mb-2">Correo Electrónico</label>
              <input
                id="email"
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="ejemplo@correo.com"
                className="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all"
                required
              />
            </div>
            <Button type="submit" disabled={loading} className="w-full h-12 rounded-xl text-base">
              {loading ? "Enviando..." : "Continuar"}
            </Button>
          </form>
        ) : (
          <form onSubmit={verifyOtp} className="space-y-4">
            <div>
              <label htmlFor="code" className="block text-sm font-medium mb-2">Código de Acceso</label>
              <input
                id="code"
                type="text"
                maxLength={6}
                value={code}
                onChange={(e) => setCode(e.target.value.replace(/\D/g, ''))}
                placeholder="000000"
                className="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-primary outline-none text-center text-2xl tracking-widest transition-all"
                required
              />
            </div>
            <Button type="submit" disabled={loading || code.length !== 6} className="w-full h-12 rounded-xl text-base">
              {loading ? "Verificando..." : "Entrar"}
            </Button>
            <button
              type="button"
              onClick={() => setStep(1)}
              className="w-full text-sm text-muted-foreground hover:text-foreground mt-4"
            >
              Cambiar correo
            </button>
          </form>
        )}
      </div>
    </div>
  );
}
