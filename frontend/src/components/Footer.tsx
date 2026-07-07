"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import { StoreSettings } from '@/services/settings';
import { Phone, ArrowUp, Plus, Minus, Mail, MapPin } from 'lucide-react';
import { toast } from "sonner";

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

const YoutubeIcon = ({ className }: { className?: string }) => (
  <svg className={className} viewBox="0 0 24 24" fill="currentColor">
    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
  </svg>
);

const WhatsAppIcon = ({ className }: { className?: string }) => (
  <svg className={className} viewBox="0 0 24 24" fill="currentColor">
    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.052 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
  </svg>
);

const HeadsetIcon = ({ className }: { className?: string }) => (
  <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M3 18v-6a9 9 0 0 1 18 0v6" />
    <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z" />
  </svg>
);

export function Footer({ settings }: { settings?: StoreSettings | null }) {
  const storeName = settings?.store_name || "Compra Saludable";
  const isDark = settings?.footer_theme === 'dark';
  
  const bgClass = isDark ? 'bg-[#0d1b2a] text-slate-300' : 'bg-white text-gray-600';
  const borderClass = isDark ? 'border-slate-800' : 'border-gray-200';
  const headingClass = isDark ? 'text-white' : 'text-gray-900';
  const iconBgClass = isDark ? 'bg-slate-800' : 'bg-gray-100';
  const iconBorderClass = isDark ? 'border-transparent' : 'border-gray-200';

  // State for mobile accordions
  const [openSection, setOpenSection] = useState<number | null>(null);
  const [newsletterOpen, setNewsletterOpen] = useState(false);
  const [showScrollTop, setShowScrollTop] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      // El botón de subir solo se debe mostrar cuando esté al final de la página / scrolleado hacia el footer
      const scrollPosition = window.innerHeight + window.scrollY;
      const threshold = document.documentElement.scrollHeight - 700;
      if (scrollPosition >= threshold && window.scrollY > 300) {
        setShowScrollTop(true);
      } else {
        setShowScrollTop(false);
      }
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const toggleSection = (index: number) => {
    setOpenSection(openSection === index ? null : index);
  };

  const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  return (
    <footer className={`${bgClass} mt-auto transition-colors duration-300`}>
      {/* Top Header - Phone */}
      <div className={`border-b ${borderClass}`}>
        <div className="max-w-7xl mx-auto px-6 py-6 flex items-center gap-4">
          <HeadsetIcon className="w-8 h-8 text-orange-500" />
          <div>
            <h2 className="text-orange-500 text-2xl md:text-3xl font-bold leading-none">
              {settings?.whatsapp_number || "+51 928 586 883"}
            </h2>
            <p className="text-orange-500 text-sm font-medium mt-1">Atención al Cliente</p>
          </div>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-6 py-12">
        <div className="flex flex-col lg:flex-row gap-12 lg:gap-8 justify-between">
          
          {/* Column 1: Info & Socials */}
          <div className="w-full lg:w-1/4 space-y-6">
            <div>
              <h3 className={`text-lg font-bold ${headingClass} mb-1`}>
                {settings?.store_address || "Lima, Perú"}
              </h3>
              <p className={`font-semibold ${headingClass}`}>Correo de Ayuda</p>
              <p className="mt-2 text-[15px] opacity-90">
                {settings?.contact_email || "hola@comprasaludable.com"}
              </p>
            </div>
            
            <div className="flex gap-3">
              {settings?.facebook_url && (
                <Link href={settings.facebook_url} target="_blank" className={`w-10 h-10 rounded-full ${iconBgClass} border ${iconBorderClass} flex items-center justify-center hover:bg-orange-500 hover:text-white transition-colors`}><FacebookIcon className="w-4 h-4" /></Link>
              )}
              {settings?.instagram_url && (
                <Link href={settings.instagram_url} target="_blank" className={`w-10 h-10 rounded-full ${iconBgClass} border ${iconBorderClass} flex items-center justify-center hover:bg-orange-500 hover:text-white transition-colors`}><InstagramIcon className="w-4 h-4" /></Link>
              )}
              <Link href="#" className={`w-10 h-10 rounded-full ${iconBgClass} border ${iconBorderClass} flex items-center justify-center hover:bg-orange-500 hover:text-white transition-colors`}><YoutubeIcon className="w-4 h-4" /></Link>
              {settings?.tiktok_url && (
                <Link href={settings.tiktok_url} target="_blank" className={`w-10 h-10 rounded-full ${iconBgClass} border ${iconBorderClass} flex items-center justify-center hover:bg-orange-500 hover:text-white transition-colors`}><TikTokIcon className="w-4 h-4" /></Link>
              )}
              <Link href={`https://wa.me/${(settings?.whatsapp_number || '').replace(/\D/g, '')}`} target="_blank" className={`w-10 h-10 rounded-full ${iconBgClass} border ${iconBorderClass} flex items-center justify-center hover:bg-orange-500 hover:text-white transition-colors`}><WhatsAppIcon className="w-5 h-5" /></Link>
            </div>
          </div>

          {/* Columns 2 & 3: Dynamic Links */}
          <div className="w-full lg:w-2/4 grid grid-cols-1 md:grid-cols-2 gap-0 md:gap-8">
            {settings?.footer_columns && settings.footer_columns.length > 0 ? (
              settings.footer_columns.map((col, idx) => (
                <div key={idx} className={`border-b md:border-none ${borderClass} py-4 md:py-0`}>
                  {/* Mobile Accordion Header */}
                  <button 
                    onClick={() => toggleSection(idx)}
                    className="w-full flex items-center justify-between md:cursor-default md:pointer-events-none"
                  >
                    <h3 className={`text-lg font-semibold ${headingClass}`}>
                      {col.title}
                    </h3>
                    <div className="md:hidden">
                      {openSection === idx ? <Minus className="w-5 h-5" /> : <Plus className="w-5 h-5" />}
                    </div>
                  </button>
                  
                  {/* Links List */}
                  <ul className={`mt-4 space-y-4 text-[15px] ${openSection === idx ? 'block' : 'hidden md:block'} opacity-90`}>
                    {col.links?.map((link, lidx) => (
                      <li key={lidx}>
                        <Link href={link.url} className="hover:text-orange-500 transition-colors">{link.label}</Link>
                      </li>
                    ))}
                  </ul>
                </div>
              ))
            ) : (
              // Fallback
              <>
                <div className={`border-b md:border-none ${borderClass} py-4 md:py-0`}>
                  <button onClick={() => toggleSection(0)} className="w-full flex items-center justify-between md:cursor-default md:pointer-events-none">
                    <h3 className={`text-lg font-semibold ${headingClass}`}>Nosotros</h3>
                    <div className="md:hidden">{openSection === 0 ? <Minus className="w-5 h-5" /> : <Plus className="w-5 h-5" />}</div>
                  </button>
                  <ul className={`mt-4 space-y-4 text-[15px] ${openSection === 0 ? 'block' : 'hidden md:block'} opacity-90`}>
                    <li><Link href="/about-us" className="hover:text-orange-500 transition-colors">¿Quiénes somos?</Link></li>
                    <li><Link href="/contactanos" className="hover:text-orange-500 transition-colors">Contáctanos</Link></li>
                  </ul>
                </div>
                <div className={`border-b md:border-none ${borderClass} py-4 md:py-0`}>
                  <button onClick={() => toggleSection(1)} className="w-full flex items-center justify-between md:cursor-default md:pointer-events-none">
                    <h3 className={`text-lg font-semibold ${headingClass}`}>Ayuda</h3>
                    <div className="md:hidden">{openSection === 1 ? <Minus className="w-5 h-5" /> : <Plus className="w-5 h-5" />}</div>
                  </button>
                  <ul className={`mt-4 space-y-4 text-[15px] ${openSection === 1 ? 'block' : 'hidden md:block'} opacity-90`}>
                    <li><Link href="/preguntas-frecuentes" className="hover:text-orange-500 transition-colors">Preguntas frecuentes</Link></li>
                    <li><Link href="/envios-y-entregas" className="hover:text-orange-500 transition-colors">Envíos y entregas</Link></li>
                    <li><Link href="/devoluciones-y-reembolsos" className="hover:text-orange-500 transition-colors">Devoluciones y reembolsos</Link></li>
                  </ul>
                </div>
              </>
            )}
          </div>

          {/* Column 4: Newsletter */}
          <div className={`w-full lg:w-1/4 border-b md:border-none ${borderClass} py-4 md:py-0`}>
            <button 
              onClick={() => setNewsletterOpen(!newsletterOpen)}
              className="w-full flex items-center justify-between md:cursor-default md:pointer-events-none mb-4"
            >
              <h3 className={`text-lg font-semibold ${headingClass}`}>
                Suscríbete a nuestro Newsletter
              </h3>
              <div className="md:hidden">
                {newsletterOpen ? <Minus className="w-5 h-5" /> : <Plus className="w-5 h-5" />}
              </div>
            </button>
            
            <div className={`${newsletterOpen ? 'block' : 'hidden md:block'}`}>
              <p className="text-[14px] opacity-90 mb-4 leading-relaxed">
                Entérate de los nuevos productos, e información de valor de nuestras principales categorías.
              </p>
              <form 
                className="relative flex items-center mt-3 shadow-sm" 
                onSubmit={(e) => { 
                  e.preventDefault(); 
                  toast.success("¡Gracias por suscribirte a nuestro boletín!"); 
                }}
              >
                <div className="absolute left-4 text-gray-400 pointer-events-none z-10">
                  <Mail className="w-4 h-4" />
                </div>
                <input 
                  type="email" 
                  placeholder="Tu correo electrónico..." 
                  className="w-full py-3.5 pl-11 pr-32 rounded-full bg-white text-gray-900 placeholder-gray-400 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 border border-gray-300 shadow-inner"
                  required
                />
                <button 
                  type="submit" 
                  className="absolute right-1.5 top-1.5 bottom-1.5 px-5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-xs uppercase tracking-wider rounded-full shadow-md hover:shadow-lg transition-all transform hover:scale-105 flex items-center gap-1.5 z-10"
                >
                  <span>Suscribir</span>
                </button>
              </form>
            </div>
          </div>

        </div>
      </div>

      {/* Bottom Bar & Payment Methods */}
      <div className="max-w-7xl mx-auto px-6 pb-12 pt-6">
        <div className={`border-t ${borderClass} pt-8 flex flex-col-reverse md:flex-row items-center justify-between gap-6`}>
          <p className="text-sm opacity-80 text-center md:text-left w-full md:w-auto">
            © {new Date().getFullYear()} {storeName} Perú. Todos los Derechos Reservados.
          </p>
          
          <div className="flex flex-wrap justify-center md:justify-end gap-2 items-center max-w-full">
            {/* Mercado Pago */}
            <div className="h-8 w-12 bg-yellow-400 rounded flex items-center justify-center">
              <svg viewBox="0 0 24 24" className="h-6 w-8 text-blue-600" fill="currentColor"><path d="M11 12.5c-.3.2-.8.2-1.1 0l-2.4-1.4c-1.3-.8-1.7-2.5-1-3.8.8-1.3 2.5-1.7 3.8-1l2.4 1.4c1.3.8 1.7 2.5 1 3.8-.8 1.3-2.4 1.7-3.7 1zM7 11.5l1.5.9c.7.4 1.6.2 2-.5.4-.7.2-1.6-.5-2l-1.5-.9c-.7-.4-1.6-.2-2 .5-.4.7-.2 1.6.5 2z"/></svg>
            </div>
            {/* VISA */}
            <div className="h-8 w-12 bg-blue-600 rounded flex items-center justify-center">
              <span className="text-white font-bold text-[13px] tracking-wider italic">VISA</span>
            </div>
            {/* Mastercard */}
            <div className="h-8 w-12 bg-[#1a1f26] rounded flex items-center justify-center overflow-hidden">
              <div className="relative flex items-center justify-center w-full">
                <div className="w-5 h-5 rounded-full bg-red-500 absolute -ml-3 opacity-90 mix-blend-screen"></div>
                <div className="w-5 h-5 rounded-full bg-yellow-500 absolute ml-3 opacity-90 mix-blend-screen"></div>
              </div>
            </div>
            {/* AMEX */}
            <div className="h-8 w-12 bg-blue-500 rounded flex items-center justify-center">
              <span className="text-white font-bold text-[10px] leading-tight text-center">AM<br/>EX</span>
            </div>
            {/* Diners */}
            <div className="h-8 w-12 bg-white rounded flex items-center justify-center">
              <span className="text-[#004b8d] font-serif font-bold text-lg">D</span>
            </div>
            {/* Yape */}
            <div className="h-8 w-12 bg-purple-600 rounded flex items-center justify-center">
              <span className="text-white font-bold text-xs">yape</span>
            </div>
            {/* PagoEfectivo */}
            <div className="h-8 w-12 bg-yellow-400 rounded flex items-center justify-center">
              <span className="text-black font-bold italic text-lg leading-none pr-1">P</span>
            </div>
            {/* Apple Pay */}
            <div className="h-8 w-16 bg-white rounded flex items-center justify-center gap-1">
              <svg viewBox="0 0 24 24" className="w-3 h-4" fill="currentColor"><path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.05 2.53.8 3.26.8s2.1-.85 3.65-.72c1.41.13 2.73.71 3.65 1.69-2.85 1.57-2.38 5.48.55 6.7-1.04 2.22-2.18 3.55-3.11 4.5zm-3.28-14.8c-.36 1.83-2.16 3.19-3.9 3.03-.43-1.84 1.34-3.4 3.17-3.79.13-.03.26-.04.38-.04.13.01.24.41.35.8z"/></svg>
              <span className="font-bold text-[11px]">Pay</span>
            </div>
            {/* Google Pay */}
            <div className="h-8 w-16 bg-white rounded flex items-center justify-center gap-1">
              <span className="font-bold text-[13px] text-gray-500"><span className="text-red-500">G</span> Pay</span>
            </div>
            {/* Shop */}
            <div className="h-8 w-14 bg-purple-600 rounded flex items-center justify-center">
              <span className="text-white font-bold text-[13px]">shop</span>
            </div>
          </div>
        </div>
      </div>

      {/* Floating Buttons - Volver arriba (Solo visible al final de la página) */}
      <div className={`fixed bottom-24 right-6 flex flex-col gap-3 z-50 transition-all duration-300 transform ${showScrollTop ? 'opacity-100 translate-y-0 pointer-events-auto' : 'opacity-0 translate-y-4 pointer-events-none'}`}>
        <button 
          onClick={scrollToTop}
          className="w-12 h-12 bg-white text-gray-900 rounded-full shadow-xl border border-gray-100 flex items-center justify-center hover:bg-emerald-500 hover:text-white transition-all duration-300 group"
          aria-label="Volver arriba"
          title="Volver al inicio"
        >
          <ArrowUp className="w-6 h-6 group-hover:-translate-y-0.5 transition-transform" />
        </button>
      </div>
    </footer>
  );
}
