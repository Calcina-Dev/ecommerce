"use client"
import { useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { useAuthStore } from "@/store/useAuthStore";
import { Button } from "@/components/ui/button";
import { GoogleOAuthProvider, GoogleLogin } from '@react-oauth/google';

const GOOGLE_CLIENT_ID = process.env.NEXT_PUBLIC_GOOGLE_CLIENT_ID || 
  (process.env.NODE_ENV === 'development' 
    ? "744860459189-gr15hataltrla8o1r23tmbpu6oj7pui1.apps.googleusercontent.com" 
    : "744860459189-ej6h6nan488nk95enrgmr0fjhprcp32l.apps.googleusercontent.com");

export default function LoginPage() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const router = useRouter();
  const setAuth = useAuthStore((state) => state.setAuth);

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!email || !password) return;
    setLoading(true);
    setError("");

    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/auth/login`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password }),
      });
      
      const data = await res.json();
      
      if (!res.ok) throw new Error(data.message || "Credenciales incorrectas");
      
      setAuth(data.user, data.token);
      router.push("/");
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleGoogleSuccess = async (credentialResponse: any) => {
    try {
      setLoading(true);
      const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/auth/google`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ credential: credentialResponse.credential }),
      });

      const data = await res.json();

      if (!res.ok) throw new Error(data.message || "Error al autenticar con Google");

      setAuth(data.user, data.token);
      router.push("/");
    } catch (err: any) {
      setError(err.message);
      setLoading(false);
    }
  };

  return (
    <GoogleOAuthProvider clientId={GOOGLE_CLIENT_ID}>
      <div className="min-h-screen bg-muted/30 flex items-center justify-center p-6">
        <div className="bg-background max-w-md w-full rounded-3xl p-8 border shadow-lg">
          <div className="text-center mb-8">
            <h1 className="text-2xl font-bold">Iniciar Sesión</h1>
            <p className="text-muted-foreground mt-2">
              Ingresa tus credenciales para continuar
            </p>
          </div>

          {error && (
            <div className="bg-destructive/10 text-destructive p-3 rounded-xl text-sm mb-6 text-center">
              {error}
            </div>
          )}

          <form onSubmit={handleLogin} className="space-y-4">
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
            <div>
              <label htmlFor="password" className="block text-sm font-medium mb-2">Contraseña</label>
              <input
                id="password"
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="********"
                className="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all"
                required
              />
            </div>
            <Button type="submit" disabled={loading} className="w-full h-12 rounded-xl text-base">
              {loading ? "Entrando..." : "Iniciar Sesión"}
            </Button>
          </form>

          <div className="mt-6 flex items-center justify-center relative">
            <div className="absolute border-t w-full"></div>
            <span className="bg-background px-3 text-sm text-muted-foreground relative z-10">O ingresa con</span>
          </div>

          <div className="mt-6 flex justify-center">
            <GoogleLogin
              onSuccess={handleGoogleSuccess}
              onError={() => setError('Google Login Falló')}
              text="signin_with"
              shape="pill"
            />
          </div>

          <div className="mt-8 text-center text-sm">
            ¿No tienes cuenta? <Link href="/register" className="text-primary hover:underline font-semibold">Regístrate</Link>
          </div>
        </div>
      </div>
    </GoogleOAuthProvider>
  );
}
