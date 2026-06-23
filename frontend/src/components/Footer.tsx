import Link from "next/link";
import { Phone, Mail, MapPin } from 'lucide-react';
import { StoreSettings } from '@/services/settings';

const FacebookIcon = ({ className }: { className?: string }) => (
  <svg className={className} fill="currentColor" viewBox="0 0 24 24">
    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
  </svg>
);

const InstagramIcon = ({ className }: { className?: string }) => (
  <svg className={className} fill="currentColor" viewBox="0 0 24 24">
    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
  </svg>
);

const TikTokIcon = ({ className }: { className?: string }) => (
  <svg className={className} viewBox="0 0 24 24" fill="currentColor">
    <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z" />
  </svg>
);

export function Footer({ settings }: { settings?: StoreSettings | null }) {
  const storeName = settings?.store_name || "Compra Saludable";
  const isDark = settings?.footer_theme === 'dark';
  
  const bgClass = isDark ? 'bg-slate-950 text-slate-400' : 'bg-white text-gray-600';
  const borderClass = isDark ? 'border-slate-800' : 'border-gray-100';
  const headingClass = isDark ? 'text-white' : 'text-gray-900';
  const iconBgClass = isDark ? 'bg-slate-800' : 'bg-gray-50';
  const iconBorderClass = isDark ? 'border-slate-700' : 'border-gray-200';
  const dotClass = isDark ? 'bg-white' : 'bg-gray-900';

  return (
    <footer className={`${bgClass} border-t ${borderClass} mt-auto transition-colors duration-300`}>
      {/* Top Footer with Trust Badges */}
      <div className={`max-w-7xl mx-auto px-6 py-12 border-b ${borderClass}`}>
        <div className="grid grid-cols-2 md:grid-cols-5 gap-8 text-center">
          <div className="flex flex-col items-center gap-3">
            <div className="w-12 h-12 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center mb-2">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
            </div>
            <h4 className={`font-bold ${headingClass} text-[13px]`}>Packs y Ofertas Exclusivas</h4>
            <p className="text-xs opacity-80">Ahorra comprando en combo</p>
          </div>
          <div className="flex flex-col items-center gap-3">
            <div className="w-12 h-12 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center mb-2">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            </div>
            <h4 className={`font-bold ${headingClass} text-[13px]`}>Suplementos Naturales</h4>
            <p className="text-xs opacity-80">Calidad certificada y comprobada</p>
          </div>
          <div className="flex flex-col items-center gap-3">
            <div className="w-12 h-12 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center mb-2">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <h4 className={`font-bold ${headingClass} text-[13px]`}>Compra 100% segura</h4>
            <p className="text-xs opacity-80">Pagos con Yape, Plin, Tarjetas</p>
          </div>
          <div className="flex flex-col items-center gap-3">
            <div className="w-12 h-12 rounded-full bg-red-50 text-red-500 flex items-center justify-center mb-2">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <h4 className={`font-bold ${headingClass} text-[13px]`}>Asesoría personalizada</h4>
            <p className="text-xs opacity-80">Escríbenos al WhatsApp</p>
          </div>
          <div className="flex flex-col items-center gap-3">
            <div className="w-12 h-12 rounded-full bg-teal-50 text-teal-500 flex items-center justify-center mb-2">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <h4 className={`font-bold ${headingClass} text-[13px]`}>Envío gratis desde S/100</h4>
            <p className="text-xs opacity-80">A todo el Perú, rápido y seguro</p>
          </div>
        </div>
      </div>

      {/* Main Footer Content */}
      <div className="max-w-7xl mx-auto px-6 py-16">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-8">
          
          {/* Column 1: Info */}
          <div className="space-y-6">
            <h3 className={`text-xl font-black tracking-tight ${headingClass}`}>{storeName}</h3>
            <div className="space-y-4 text-[13px] opacity-80">
              <div>
                <p className={`font-semibold ${headingClass} mb-1 flex items-center gap-2`}><Phone className="w-3 h-3" /> Atención al cliente</p>
                <p>{settings?.whatsapp_number || "+51 928 586 883"}</p>
                <p>{settings?.contact_email || "ventas@comprasaludable.com"}</p>
                <p className="opacity-60">Lun–Sáb 9:00–19:00</p>
              </div>
              <div>
                <p className={`font-semibold ${headingClass} mb-1 flex items-center gap-2`}><MapPin className="w-3 h-3" /> Dirección</p>
                <p>{settings?.store_address || "Av. Tomás Valle 917, SMP – Lima, Perú"}</p>
              </div>
            </div>
            
            <div className="flex gap-3">
              {settings?.facebook_url && (
                <Link href={settings.facebook_url} target="_blank" className={`w-8 h-8 rounded-full border ${iconBorderClass} flex items-center justify-center hover:text-accent hover:border-accent transition-colors`}><FacebookIcon className="w-4 h-4" /></Link>
              )}
              {settings?.instagram_url && (
                <Link href={settings.instagram_url} target="_blank" className={`w-8 h-8 rounded-full border ${iconBorderClass} flex items-center justify-center hover:text-accent hover:border-accent transition-colors`}><InstagramIcon className="w-4 h-4" /></Link>
              )}
              {settings?.tiktok_url && (
                <Link href={settings.tiktok_url} target="_blank" className={`w-8 h-8 rounded-full border ${iconBorderClass} flex items-center justify-center hover:text-accent hover:border-accent transition-colors`}><TikTokIcon className="w-4 h-4" /></Link>
              )}
            </div>
            
            <div className="flex gap-2 flex-wrap text-[10px] font-bold opacity-70 uppercase">
              <span className={`px-2 py-1 rounded border ${iconBorderClass} ${iconBgClass}`}>Pago Seguro</span>
              <span className={`px-2 py-1 rounded border ${iconBorderClass} ${iconBgClass}`}>Yape</span>
              <span className={`px-2 py-1 rounded border ${iconBorderClass} ${iconBgClass}`}>Plin</span>
              <span className={`px-2 py-1 rounded border ${iconBorderClass} ${iconBgClass}`}>Izipay</span>
              <span className={`px-2 py-1 rounded border ${iconBorderClass} ${iconBgClass}`}>Visa • Mastercard</span>
            </div>
          </div>

          {/* Dynamic Footer Columns */}
          {settings?.footer_columns && settings.footer_columns.length > 0 ? (
            settings.footer_columns.map((col, idx) => (
              <div key={idx}>
                <h3 className={`font-bold ${headingClass} mb-6 flex items-center gap-2`}>
                  <span className={`w-1.5 h-1.5 ${dotClass} rounded-full`}></span>
                  {col.title}
                </h3>
                <ul className="space-y-4 text-[13px] opacity-80">
                  {col.links?.map((link, lidx) => (
                    <li key={lidx}>
                      <Link href={link.url} className="hover:text-accent transition-colors">{link.label}</Link>
                    </li>
                  ))}
                </ul>
              </div>
            ))
          ) : (
            // Fallback content if no columns are defined
            <>
              <div>
                <h3 className={`font-bold ${headingClass} mb-6 flex items-center gap-2`}>
                  <span className={`w-1.5 h-1.5 ${dotClass} rounded-full`}></span>
                  Empresa
                </h3>
                <ul className="space-y-4 text-[13px] opacity-80">
                  <li><Link href="/about-us" className="hover:text-accent transition-colors">Sobre Nosotros</Link></li>
                  <li><Link href="/contactanos" className="hover:text-accent transition-colors">Contáctanos</Link></li>
                  <li><Link href="/consejos-de-salud" className="hover:text-accent transition-colors">Consejos de Salud</Link></li>
                  <li><Link href="/testimonios" className="hover:text-accent transition-colors">Testimonios</Link></li>
                </ul>
              </div>
              <div>
                <h3 className={`font-bold ${headingClass} mb-6 flex items-center gap-2`}>
                  <span className={`w-1.5 h-1.5 ${dotClass} rounded-full`}></span>
                  Ayuda al cliente
                </h3>
                <ul className="space-y-4 text-[13px] opacity-80">
                  <li><Link href="/preguntas-frecuentes" className="hover:text-accent transition-colors">Preguntas frecuentes</Link></li>
                  <li><Link href="/envios-y-entregas" className="hover:text-accent transition-colors">Envíos y entregas</Link></li>
                  <li><Link href="/devoluciones-y-reembolsos" className="hover:text-accent transition-colors">Devoluciones y reembolsos</Link></li>
                </ul>
              </div>
              <div>
                <h3 className={`font-bold ${headingClass} mb-6 flex items-center gap-2`}>
                  <span className={`w-1.5 h-1.5 ${dotClass} rounded-full`}></span>
                  Tienda & Legal
                </h3>
                <ul className="space-y-4 text-[13px] opacity-80">
                  <li><Link href="/terms-and-conditions" className="hover:text-accent transition-colors">Términos y condiciones</Link></li>
                  <li><Link href="/politica-de-privacidad" className="hover:text-accent transition-colors">Política de privacidad</Link></li>
                </ul>
              </div>
            </>
          )}

        </div>
      </div>

      {/* Bottom Bar */}
      <div className={`${isDark ? 'bg-slate-900' : 'bg-gray-50'} border-t ${borderClass}`}>
        <div className="max-w-7xl mx-auto px-6 py-6 flex flex-col md:flex-row items-center justify-between gap-4">
          <p className="text-xs opacity-60">
            © {new Date().getFullYear()} {storeName}. Todos los derechos reservados.
          </p>
          <div className="flex gap-4 text-xs opacity-60">
            <Link href="/libro-de-reclamaciones" className={`hover:${headingClass}`}>Libro de Reclamaciones</Link>
            <span>·</span>
            <Link href="/politica-de-privacidad" className={`hover:${headingClass}`}>Privacidad</Link>
            <span>·</span>
            <Link href="/terms-and-conditions" className={`hover:${headingClass}`}>Términos</Link>
          </div>
        </div>
      </div>
    </footer>
  );
}
