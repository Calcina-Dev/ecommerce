"use client"
import { useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { useAuthStore } from "@/store/useAuthStore";
import { Button } from "@/components/ui/button";
import { GoogleOAuthProvider, GoogleLogin } from '@react-oauth/google';
import { motion, AnimatePresence } from "framer-motion";
import { Mail, Lock, ArrowLeft, AlertCircle, CheckCircle2 } from "lucide-react";

const GOOGLE_CLIENT_ID = process.env.NEXT_PUBLIC_GOOGLE_CLIENT_ID || "744860459189-gr15hataltrla8o1r23tmbpu6oj7pui1.apps.googleusercontent.com";

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
      setSuccessMsg("Te hemos enviado un código de 6 dígitos a tu correo.");
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
      router.push("/mi-cuenta");
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
      router.push("/mi-cuenta");
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleGoogleSuccess = async (credentialResponse: any) => {
    try {
      setLoading(true);
      setError("");
      const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/auth/google`, {
        method: "POST",
        headers: { "Content-Type": "application/json", "Accept": "application/json" },
        body: JSON.stringify({ credential: credentialResponse.credential }),
      });

      let data;
      try {
        data = await res.json();
      } catch (e) {
        throw new Error(`Error de conexión o respuesta no válida del servidor (HTTP ${res.status}). Verifica tu conexión.`);
      }

      if (!res.ok) throw new Error(data.message || "Error al autenticar con Google");

      setAuth(data.user, data.token);
      router.push("/mi-cuenta");
    } catch (err: any) {
      setError(err.message || "Error al iniciar sesión con Google");
      setLoading(false);
    }
  };

  return (
    <GoogleOAuthProvider clientId={GOOGLE_CLIENT_ID}>
      <div className="min-h-[calc(100vh-80px)] bg-muted/30 flex items-center justify-center p-6">
        <div className="bg-background max-w-md w-full rounded-3xl p-8 border shadow-lg">
          
          <div className="text-center mb-8">
            <h1 className="text-2xl font-bold">Iniciar Sesión</h1>
            <p className="text-muted-foreground mt-2 text-sm">
              Accede para ver tus pedidos y rastrear envíos
            </p>
          </div>

          {/* Login Method Toggle */}
          <div className="grid grid-cols-2 gap-1.5 p-1.5 bg-muted rounded-2xl mb-6 relative">
            <button
              type="button"
              onClick={() => { setLoginMethod("otp"); setError(""); setSuccessMsg(""); }}
              className={`py-2 px-3 rounded-xl text-xs sm:text-sm font-bold transition-colors flex items-center justify-center gap-2 relative z-10 ${
                loginMethod === "otp" 
                  ? "text-foreground" 
                  : "text-muted-foreground hover:text-foreground"
              }`}
            >
              <Mail className="w-4 h-4" />
              <span>Código por Correo</span>
              {loginMethod === "otp" && (
                <motion.div 
                  layoutId="activeLoginTab" 
                  transition={{ type: "spring", bounce: 0.2, duration: 0.5 }}
                  className="absolute inset-0 bg-background rounded-xl shadow-sm border -z-10" 
                />
              )}
            </button>
            <button
              type="button"
              onClick={() => { setLoginMethod("password"); setError(""); setSuccessMsg(""); }}
              className={`py-2 px-3 rounded-xl text-xs sm:text-sm font-bold transition-colors flex items-center justify-center gap-2 relative z-10 ${
                loginMethod === "password" 
                  ? "text-foreground" 
                  : "text-muted-foreground hover:text-foreground"
              }`}
            >
              <Lock className="w-4 h-4" />
              <span>Contraseña</span>
              {loginMethod === "password" && (
                <motion.div 
                  layoutId="activeLoginTab" 
                  transition={{ type: "spring", bounce: 0.2, duration: 0.5 }}
                  className="absolute inset-0 bg-background rounded-xl shadow-sm border -z-10" 
                />
              )}
            </button>
          </div>

          {error && (
            <div className="bg-destructive/10 text-destructive p-3 rounded-xl text-sm mb-6 text-center font-medium flex items-center justify-center gap-2">
              <AlertCircle className="w-4 h-4 shrink-0" />
              <span>{error}</span>
            </div>
          )}

          {successMsg && (
            <div className="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 p-3 rounded-xl text-sm mb-6 text-center font-medium flex items-center justify-center gap-2">
              <CheckCircle2 className="w-4 h-4 shrink-0" />
              <span>{successMsg}</span>
            </div>
          )}

          <AnimatePresence mode="wait">
            {/* METHOD 1: OTP CODE LOGIN */}
            {loginMethod === "otp" && (
              <motion.div 
                key="otp-method"
                initial={{ opacity: 0, x: -10 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: 10 }}
                transition={{ duration: 0.2 }}
              >
                {otpStep === "request" ? (
                  <form onSubmit={handleRequestOtp} className="space-y-4">
                    <div>
                      <label htmlFor="email-otp" className="block text-sm font-medium mb-2">
                        Correo Electrónico
                      </label>
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
                        Recibirás un código de 6 dígitos. Sin necesidad de recordar contraseñas.
                      </p>
                    </div>
                    
                    <Button type="submit" disabled={loading} className="w-full h-12 rounded-xl text-base font-bold">
                      {loading ? "Enviando código..." : "Recibir Código por Correo"}
                    </Button>
                  </form>
                ) : (
                  <form onSubmit={handleVerifyOtp} className="space-y-4">
                    <div className="bg-muted/50 rounded-xl p-3 border flex items-center justify-between">
                      <div className="text-left">
                        <p className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Enviado a</p>
                        <p className="text-xs font-bold text-foreground truncate max-w-[200px] sm:max-w-[240px]">{email}</p>
                      </div>
                      <button
                        type="button"
                        onClick={() => { setOtpStep("request"); setError(""); setSuccessMsg(""); }}
                        className="text-xs font-bold text-primary hover:underline flex items-center gap-1 bg-background px-2.5 py-1.5 rounded-lg border transition-colors"
                      >
                        <ArrowLeft className="w-3 h-3" />
                        <span>Cambiar</span>
                      </button>
                    </div>

                    <div>
                      <label htmlFor="otp-code" className="block text-xs font-bold uppercase tracking-wider text-center text-muted-foreground mb-2">
                        Ingresa los 6 dígitos
                      </label>
                      <input
                        id="otp-code"
                        type="text"
                        maxLength={6}
                        value={otpCode}
                        onChange={(e) => setOtpCode(e.target.value.replace(/\D/g, ''))}
                        placeholder="• • • • • •"
                        className="w-full px-4 py-3.5 border-2 rounded-2xl focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all text-center text-2xl sm:text-3xl font-mono tracking-[0.4em] font-bold text-foreground"
                        required
                        autoFocus
                      />
                    </div>

                    <Button type="submit" disabled={loading || otpCode.length < 6} className="w-full h-12 rounded-xl text-base font-bold">
                      {loading ? "Verificando..." : "Verificar e Iniciar Sesión"}
                    </Button>
                  </form>
                )}
              </motion.div>
            )}

            {/* METHOD 2: PASSWORD LOGIN */}
            {loginMethod === "password" && (
              <motion.form 
                key="password-method"
                initial={{ opacity: 0, x: 10 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: -10 }}
                transition={{ duration: 0.2 }}
                onSubmit={handlePasswordLogin} 
                className="space-y-4"
              >
                <div>
                  <label htmlFor="email-pwd" className="block text-sm font-medium mb-2">
                    Correo Electrónico
                  </label>
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
                  <label htmlFor="password" className="block text-sm font-medium mb-2">
                    Contraseña
                  </label>
                  <input
                    id="password"
                    type="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="••••••••"
                    className="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all"
                    required
                  />
                </div>

                <Button type="submit" disabled={loading} className="w-full h-12 rounded-xl text-base font-bold mt-2">
                  {loading ? "Entrando..." : "Iniciar Sesión"}
                </Button>
              </motion.form>
            )}
          </AnimatePresence>

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
