import Link from "next/link";
import { StoreSettings } from "@/services/settings";

export function WhatsAppButton({ settings }: { settings?: StoreSettings | null }) {
  const defaultPhone = "51928586883";
  const rawPhone = settings?.whatsapp_number || defaultPhone;
  // Clean phone number (remove non-digits)
  const phoneNumber = rawPhone.replace(/\D/g, '');
  
  const storeName = settings?.store_name || "Compra Saludable";
  const message = `¡Hola! Más información de ${storeName} - TIENDA ONLINE DE SUPLEMENTOS Y VITAMINAS en https://comprasaludable.com/`;
  const whatsappUrl = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;

  return (
    <div className="fixed bottom-6 right-6 z-50 flex items-center justify-center group">
      {/* Efecto de onda pulsante (ping / beacon) */}
      <span className="absolute inline-flex h-full w-full rounded-full bg-[#25D366] opacity-60 animate-ping duration-1000 pointer-events-none"></span>
      
      {/* Brillo ambiental suave (glow) */}
      <span className="absolute -inset-1 rounded-full bg-gradient-to-r from-[#25D366] to-[#128C7E] opacity-40 blur-md group-hover:opacity-80 transition-opacity duration-500 animate-pulse pointer-events-none"></span>

      <Link 
        href={whatsappUrl}
        target="_blank"
        rel="noopener noreferrer"
        className="relative flex items-center justify-center w-14 h-14 bg-gradient-to-tr from-[#128C7E] via-[#25D366] to-[#40E0d0] text-white rounded-full shadow-lg shadow-[#25D366]/30 hover:shadow-2xl hover:shadow-[#25D366]/50 hover:scale-110 active:scale-95 transition-all duration-300 ease-out group-hover:-translate-y-1"
        aria-label="Chatear por WhatsApp"
      >
        <svg 
          className="w-8 h-8 transition-transform duration-300 ease-out group-hover:rotate-[15deg] group-hover:scale-110 drop-shadow-sm" 
          fill="currentColor" 
          viewBox="0 0 24 24" 
          xmlns="http://www.w3.org/2000/svg"
        >
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
        </svg>
      </Link>

      {/* Tooltip moderno con glassmorphism y microanimación de entrada */}
      <div className="absolute right-16 top-1/2 -translate-y-1/2 bg-white/95 backdrop-blur-md text-gray-800 text-sm font-bold py-2 px-4 rounded-2xl shadow-xl border border-gray-100 opacity-0 translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300 pointer-events-none whitespace-nowrap hidden sm:flex items-center gap-2">
        <span className="w-2.5 h-2.5 rounded-full bg-[#25D366] animate-pulse"></span>
        <span>¡Escríbenos en línea!</span>
      </div>
    </div>
  );
}
