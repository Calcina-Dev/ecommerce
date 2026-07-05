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
  const [loginMethod, setLoginMethod] = useState<"otp" | "password">("otp");
  const [otpStep, setOtpStep] = useState<"request" | "verify">("request");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [otpCode, setOtpCode] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [successMsg, setSuccessMsg] = useState("");
  const router = useRouter();
  const setAuth = useAuthStore((state) => state.setAuth);

  const handleRequestOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!email) return;
    setLoading(true);
    setError("");
    setSuccessMsg("");

    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/auth/request-otp`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email }),
      });
      
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || "Error al enviar código de verificación");
      
      setOtpStep("verify");
      setSuccessMsg("¡Te enviamos un código de 6 dígitos a tu correo!");
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleVerifyOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!email || !otpCode) return;
    setLoading(true);
    setError("");

    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/auth/verify-otp`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, code: otpCode.trim() }),
      });
      
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || "Código incorrecto o expirado");
      
      setAuth(data.user, data.token);
      router.push("/");
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handlePasswordLogin = async (e: React.FormEvent) => {
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
          <div className="text-center mb-6">
            <h1 className="text-2xl font-bold">Iniciar Sesión</h1>
            <p className="text-muted-foreground mt-1.5 text-sm">
              Accede para ver tus pedidos y rastrear envíos
            </p>
          </div>

          {/* Login Method Toggle */}
          <div className="grid grid-cols-2 gap-2 p-1.5 bg-muted rounded-2xl mb-6">
            <button
              type="button"
              onClick={() => { setLoginMethod("otp"); setError(""); setSuccessMsg(""); }}
              className={`py-2 px-3 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center justify-center gap-1.5 ${
                loginMethod === "otp" 
                  ? "bg-background text-foreground shadow-sm" 
                  : "text-muted-foreground hover:text-foreground"
              }`}
            >
              <span>✉️</span>
              <span>Código por Correo</span>
            </button>
            <button
              type="button"
              onClick={() => { setLoginMethod("password"); setError(""); setSuccessMsg(""); }}
              className={`py-2 px-3 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center justify-center gap-1.5 ${
                loginMethod === "password" 
                  ? "bg-background text-foreground shadow-sm" 
                  : "text-muted-foreground hover:text-foreground"
              }`}
            >
              <span>🔐</span>
              <span>Contraseña</span>
            </button>
          </div>

          {error && (
            <div className="bg-destructive/10 text-destructive p-3 rounded-xl text-sm mb-6 text-center font-medium animate-shake">
              {error}
            </div>
          )}

          {successMsg && (
            <div className="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 p-3 rounded-xl text-sm mb-6 text-center font-medium">
              {successMsg}
            </div>
          )}

          {/* METHOD 1: OTP CODE LOGIN (MERCADO LIBRE STYLE) */}
          {loginMethod === "otp" && (
            <div>
              {otpStep === "request" ? (
                <form onSubmit={handleRequestOtp} className="space-y-4">
                  <div>
                    <label htmlFor="email-otp" className="block text-sm font-medium mb-2">Correo Electrónico</label>
                    <input
                      id="email-otp"
                      type="email"
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      placeholder="ejemplo@outlook.com o gmail.com"
                      className="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all"
                      required
                    />
                    <p className="text-[11px] text-muted-foreground mt-2">
                      ⚡ Te enviaremos un código de 6 dígitos para ingresar sin contraseña, igual que en Mercado Libre.
                    </p>
                  </div>
                  <Button type="submit" disabled={loading} className="w-full h-12 rounded-xl text-base font-bold shadow-md">
                    {loading ? "Enviando código..." : "Recibir Código por Correo"}
                  </Button>
                </form>
              ) : (
                <form onSubmit={handleVerifyOtp} className="space-y-4">
                  <div className="text-center mb-2">
                    <p className="text-xs text-muted-foreground">
                      Código enviado a: <span className="font-semibold text-foreground">{email}</span>
                    </p>
                    <button
                      type="button"
                      onClick={() => { setOtpStep("request"); setError(""); setSuccessMsg(""); }}
                      className="text-xs text-primary hover:underline font-semibold mt-1"
                    >
                      (Cambiar correo)
                    </button>
                  </div>
                  <div>
                    <label htmlFor="otp-code" className="block text-sm font-medium mb-2 text-center">
                      Ingresa el código de 6 dígitos
                    </label>
                    <input
                      id="otp-code"
                      type="text"
                      maxLength={6}
                      value={otpCode}
                      onChange={(e) => setOtpCode(e.target.value.replace(/\D/g, ''))}
                      placeholder="889635"
                      className="w-full px-4 py-3.5 border rounded-2xl focus:ring-2 focus:ring-primary outline-none transition-all text-center text-2xl font-mono tracking-[0.4em] font-bold"
                      required
                      autoFocus
                    />
                  </div>
                  <Button type="submit" disabled={loading || otpCode.length < 6} className="w-full h-12 rounded-xl text-base font-bold shadow-md">
                    {loading ? "Verificando..." : "Verificar e Iniciar Sesión"}
                  </Button>
                </form>
              )}
            </div>
          )}

          {/* METHOD 2: PASSWORD LOGIN */}
          {loginMethod === "password" && (
            <form onSubmit={handlePasswordLogin} className="space-y-4">
              <div>
                <label htmlFor="email-pwd" className="block text-sm font-medium mb-2">Correo Electrónico</label>
                <input
                  id="email-pwd"
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
              <Button type="submit" disabled={loading} className="w-full h-12 rounded-xl text-base font-bold shadow-md">
                {loading ? "Entrando..." : "Iniciar Sesión"}
              </Button>
            </form>
          )}

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
