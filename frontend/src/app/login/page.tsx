"use client"
import { useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { useAuthStore } from "@/store/useAuthStore";
import { GoogleOAuthProvider, GoogleLogin } from '@react-oauth/google';
import { motion, AnimatePresence } from "framer-motion";
import { Mail, Lock, KeyRound, ArrowRight, ShieldCheck, CheckCircle2, RefreshCw, Sparkles, ArrowLeft } from "lucide-react";

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
      setSuccessMsg("Te hemos enviado tu código temporal de acceso");
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
      <div className="min-h-[calc(100vh-80px)] bg-background relative flex items-center justify-center p-4 sm:p-6 overflow-hidden">
        
        {/* Ambient glowing background orbs */}
        <div className="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-gradient-to-tr from-emerald-500/15 to-teal-500/15 rounded-full blur-[120px] pointer-events-none -z-10 animate-pulse" style={{ animationDuration: '8s' }} />
        <div className="absolute bottom-10 right-10 w-[300px] h-[300px] bg-emerald-500/10 rounded-full blur-[100px] pointer-events-none -z-10" />

        <motion.div 
          initial={{ opacity: 0, y: 20, scale: 0.98 }}
          animate={{ opacity: 1, y: 0, scale: 1 }}
          transition={{ duration: 0.4, ease: [0.16, 1, 0.3, 1] }}
          className="w-full max-w-md bg-white/80 dark:bg-zinc-900/80 backdrop-blur-2xl border border-gray-200/80 dark:border-zinc-800/80 shadow-2xl shadow-emerald-950/5 rounded-[32px] p-6 sm:p-10 relative z-10"
        >
          {/* Header Badge */}
          <div className="text-center mb-8">
            <motion.div 
              initial={{ scale: 0 }}
              animate={{ scale: 1 }}
              transition={{ type: "spring", stiffness: 300, damping: 20, delay: 0.1 }}
              className="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-500/25 text-white"
            >
              <ShieldCheck className="w-7 h-7" />
            </motion.div>
            <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-foreground">
              Bienvenido de nuevo
            </h1>
            <p className="text-muted-foreground mt-2 text-xs sm:text-sm leading-relaxed">
              Ingresa a tu cuenta de <span className="font-semibold text-foreground">Compra Saludable</span> para ver pedidos y rastrear envíos
            </p>
          </div>

          {/* Login Method Toggle with Sliding Indicator */}
          <div className="grid grid-cols-2 gap-1.5 p-1.5 bg-gray-100/80 dark:bg-zinc-800/80 rounded-2xl mb-8 border border-gray-200/50 dark:border-zinc-700/50 relative">
            <button
              type="button"
              onClick={() => { setLoginMethod("otp"); setError(""); setSuccessMsg(""); }}
              className={`py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold transition-colors flex items-center justify-center gap-2 relative z-10 ${
                loginMethod === "otp" 
                  ? "text-emerald-950 dark:text-emerald-300" 
                  : "text-muted-foreground hover:text-foreground"
              }`}
            >
              <Mail className="w-4 h-4" />
              <span>Código por Correo</span>
              {loginMethod === "otp" && (
                <motion.div 
                  layoutId="activeLoginTab" 
                  transition={{ type: "spring", bounce: 0.2, duration: 0.5 }}
                  className="absolute inset-0 bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-gray-200/80 dark:border-zinc-700/80 -z-10" 
                />
              )}
            </button>
            <button
              type="button"
              onClick={() => { setLoginMethod("password"); setError(""); setSuccessMsg(""); }}
              className={`py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold transition-colors flex items-center justify-center gap-2 relative z-10 ${
                loginMethod === "password" 
                  ? "text-emerald-950 dark:text-emerald-300" 
                  : "text-muted-foreground hover:text-foreground"
              }`}
            >
              <Lock className="w-4 h-4" />
              <span>Contraseña</span>
              {loginMethod === "password" && (
                <motion.div 
                  layoutId="activeLoginTab" 
                  transition={{ type: "spring", bounce: 0.2, duration: 0.5 }}
                  className="absolute inset-0 bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-gray-200/80 dark:border-zinc-700/80 -z-10" 
                />
              )}
            </button>
          </div>

          {error && (
            <motion.div 
              initial={{ opacity: 0, scale: 0.95 }} 
              animate={{ opacity: 1, scale: 1 }} 
              className="bg-destructive/10 text-destructive p-3.5 rounded-2xl text-xs sm:text-sm mb-6 text-center font-semibold border border-destructive/20 flex items-center justify-center gap-2"
            >
              <span>⚠️</span>
              <span>{error}</span>
            </motion.div>
          )}

          {successMsg && (
            <motion.div 
              initial={{ opacity: 0, scale: 0.95 }} 
              animate={{ opacity: 1, scale: 1 }} 
              className="bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 p-3.5 rounded-2xl text-xs sm:text-sm mb-6 text-center font-semibold border border-emerald-500/20 flex items-center justify-center gap-2"
            >
              <CheckCircle2 className="w-4 h-4 shrink-0" />
              <span>{successMsg}</span>
            </motion.div>
          )}

          <AnimatePresence mode="wait">
            {/* METHOD 1: OTP CODE LOGIN (MERCADO LIBRE STYLE) */}
            {loginMethod === "otp" && (
              <motion.div 
                key="otp-method"
                initial={{ opacity: 0, x: -10 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: 10 }}
                transition={{ duration: 0.2 }}
              >
                {otpStep === "request" ? (
                  <form onSubmit={handleRequestOtp} className="space-y-5">
                    <div>
                      <label htmlFor="email-otp" className="block text-xs font-bold uppercase tracking-wider text-muted-foreground mb-2">
                        Correo Electrónico
                      </label>
                      <div className="relative group">
                        <div className="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-emerald-600 transition-colors">
                          <Mail className="w-5 h-5" />
                        </div>
                        <input
                          id="email-otp"
                          type="email"
                          value={email}
                          onChange={(e) => setEmail(e.target.value)}
                          placeholder="ejemplo@outlook.com o gmail.com"
                          className="w-full pl-12 pr-4 py-3.5 bg-gray-50/80 dark:bg-zinc-800/50 border border-gray-200 dark:border-zinc-700/80 rounded-2xl focus:bg-background focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 outline-none transition-all duration-200 font-medium text-foreground placeholder:text-muted-foreground/50 shadow-inner"
                          required
                        />
                      </div>
                      <div className="flex items-center gap-2 mt-2.5 text-[11px] text-muted-foreground font-medium px-1">
                        <Sparkles className="w-3.5 h-3.5 text-emerald-500 shrink-0" />
                        <span>Recibirás un código de 6 dígitos. Sin necesidad de recordar contraseñas.</span>
                      </div>
                    </div>
                    
                    <button 
                      type="submit" 
                      disabled={loading} 
                      className="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-600 hover:from-emerald-500 hover:via-teal-500 hover:to-emerald-600 text-white font-bold text-sm sm:text-base shadow-lg shadow-emerald-500/25 hover:shadow-xl hover:shadow-emerald-500/40 hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none transition-all duration-300 flex items-center justify-center gap-2.5 group"
                    >
                      <span>{loading ? "Enviando código..." : "Recibir Código por Correo"}</span>
                      {!loading && <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />}
                    </button>
                  </form>
                ) : (
                  <motion.form 
                    initial={{ opacity: 0, scale: 0.98 }}
                    animate={{ opacity: 1, scale: 1 }}
                    onSubmit={handleVerifyOtp} 
                    className="space-y-5"
                  >
                    <div className="bg-gray-50 dark:bg-zinc-800/50 rounded-2xl p-3.5 border border-gray-200/60 dark:border-zinc-700/60 flex items-center justify-between">
                      <div className="text-left">
                        <p className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Enviado a</p>
                        <p className="text-xs font-bold text-foreground truncate max-w-[200px] sm:max-w-[240px]">{email}</p>
                      </div>
                      <button
                        type="button"
                        onClick={() => { setOtpStep("request"); setError(""); setSuccessMsg(""); }}
                        className="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-1.5 rounded-lg transition-colors"
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
                        className="w-full px-4 py-4 bg-gray-50/80 dark:bg-zinc-800/80 border-2 border-gray-200 dark:border-zinc-700 rounded-2xl focus:bg-background focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 outline-none transition-all text-center text-2xl sm:text-3xl font-mono tracking-[0.4em] font-extrabold text-emerald-700 dark:text-emerald-400 shadow-inner"
                        required
                        autoFocus
                      />
                    </div>

                    <button 
                      type="submit" 
                      disabled={loading || otpCode.length < 6} 
                      className="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-600 hover:from-emerald-500 hover:via-teal-500 hover:to-emerald-600 text-white font-bold text-sm sm:text-base shadow-lg shadow-emerald-500/25 hover:shadow-xl hover:shadow-emerald-500/40 hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none transition-all duration-300 flex items-center justify-center gap-2"
                    >
                      <span>{loading ? "Verificando..." : "Verificar e Iniciar Sesión"}</span>
                      {!loading && <CheckCircle2 className="w-4 h-4" />}
                    </button>
                  </motion.form>
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
                  <label htmlFor="email-pwd" className="block text-xs font-bold uppercase tracking-wider text-muted-foreground mb-2">
                    Correo Electrónico
                  </label>
                  <div className="relative group">
                    <div className="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-emerald-600 transition-colors">
                      <Mail className="w-5 h-5" />
                    </div>
                    <input
                      id="email-pwd"
                      type="email"
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      placeholder="ejemplo@correo.com"
                      className="w-full pl-12 pr-4 py-3.5 bg-gray-50/80 dark:bg-zinc-800/50 border border-gray-200 dark:border-zinc-700/80 rounded-2xl focus:bg-background focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 outline-none transition-all duration-200 font-medium text-foreground placeholder:text-muted-foreground/50 shadow-inner"
                      required
                    />
                  </div>
                </div>

                <div>
                  <label htmlFor="password" className="block text-xs font-bold uppercase tracking-wider text-muted-foreground mb-2">
                    Contraseña
                  </label>
                  <div className="relative group">
                    <div className="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-emerald-600 transition-colors">
                      <Lock className="w-5 h-5" />
                    </div>
                    <input
                      id="password"
                      type="password"
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      placeholder="••••••••"
                      className="w-full pl-12 pr-4 py-3.5 bg-gray-50/80 dark:bg-zinc-800/50 border border-gray-200 dark:border-zinc-700/80 rounded-2xl focus:bg-background focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 outline-none transition-all duration-200 font-medium text-foreground placeholder:text-muted-foreground/50 shadow-inner"
                      required
                    />
                  </div>
                </div>

                <button 
                  type="submit" 
                  disabled={loading} 
                  className="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-600 hover:from-emerald-500 hover:via-teal-500 hover:to-emerald-600 text-white font-bold text-sm sm:text-base shadow-lg shadow-emerald-500/25 hover:shadow-xl hover:shadow-emerald-500/40 hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none transition-all duration-300 flex items-center justify-center gap-2 group mt-2"
                >
                  <span>{loading ? "Entrando..." : "Iniciar Sesión"}</span>
                  {!loading && <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />}
                </button>
              </motion.form>
            )}
          </AnimatePresence>

          {/* Divider */}
          <div className="my-8 flex items-center justify-center relative">
            <div className="absolute border-t border-gray-200 dark:border-zinc-800 w-full"></div>
            <span className="bg-white dark:bg-zinc-900 px-3 text-xs font-bold text-muted-foreground uppercase tracking-wider relative z-10">
              O continúa con
            </span>
          </div>

          {/* Google Login Button */}
          <div className="flex justify-center hover:scale-[1.02] transition-transform duration-200">
            <GoogleLogin
              onSuccess={handleGoogleSuccess}
              onError={() => setError('Google Login Falló')}
              text="signin_with"
              shape="pill"
              theme="outline"
              size="large"
            />
          </div>

          {/* Register Link */}
          <div className="mt-8 pt-6 border-t border-gray-100 dark:border-zinc-800/80 text-center text-xs sm:text-sm text-muted-foreground">
            ¿Aún no tienes cuenta?{" "}
            <Link href="/register" className="text-emerald-600 dark:text-emerald-400 hover:underline font-bold transition-colors">
              Regístrate aquí
            </Link>
          </div>
        </motion.div>
      </div>
    </GoogleOAuthProvider>
  );
}
