import Link from "next/link";
import Image from "next/image";
import { ArrowRight, Leaf } from "lucide-react";

export function HeroModernBlock({ data }: { data: any }) {
  return (
    <section className="relative w-full overflow-hidden bg-transparent">
      <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-emerald-100/40 via-background to-background pointer-events-none"></div>
      <div className="max-w-7xl mx-auto px-6 pt-20 pb-24 lg:pt-32 lg:pb-40 relative z-10 flex flex-col lg:flex-row items-center gap-12">
        
        {/* Texto Principal */}
        <div className="flex-1 space-y-8 text-center lg:text-left">
          {data.badge && (
            <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm font-semibold">
              <span className="relative flex h-2 w-2">
                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span className="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
              </span>
              {data.badge}
            </div>
          )}
          
          <h1 className="text-5xl lg:text-7xl font-extrabold tracking-tighter text-gray-900 leading-[1.1]">
            {data.title_line_1} <br />
            <span className="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500">
              {data.title_line_2}
            </span>
          </h1>
          
          <p className="text-lg text-gray-500 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
            {data.description}
          </p>
          
          <div className="flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start pt-4">
            {data.button_text && data.button_link && (
              <Link 
                href={data.button_link} 
                className="w-full sm:w-auto bg-gray-900 hover:bg-gray-800 text-white px-8 py-4 rounded-2xl font-bold transition-transform active:scale-[0.98] shadow-xl shadow-gray-900/20 flex items-center justify-center gap-2"
              >
                {data.button_text}
                <ArrowRight className="w-5 h-5" />
              </Link>
            )}
            <div className="flex items-center gap-4 text-sm font-medium text-gray-500">
              <div className="flex -space-x-2">
                <Image width={32} height={32} className="w-8 h-8 rounded-full border-2 border-white object-cover" src="https://i.pravatar.cc/100?img=1" alt="User 1" />
                <Image width={32} height={32} className="w-8 h-8 rounded-full border-2 border-white object-cover" src="https://i.pravatar.cc/100?img=2" alt="User 2" />
                <Image width={32} height={32} className="w-8 h-8 rounded-full border-2 border-white object-cover" src="https://i.pravatar.cc/100?img=3" alt="User 3" />
              </div>
              <span>+2,000 clientes felices</span>
            </div>
          </div>
        </div>

        {/* Imagen Hero Dinámica */}
        <div className="flex-1 relative w-full max-w-lg lg:max-w-none">
          <div className="relative aspect-square">
            {/* Círculo de fondo animado */}
            {data.animate_glow && (
              <div className="absolute inset-0 bg-gradient-to-tr from-emerald-100 to-teal-50 rounded-full blur-3xl opacity-70 animate-pulse"></div>
            )}
            
            <Image 
              fill
              src={data.hero_image 
                ? (data.hero_image.startsWith('http') ? data.hero_image : `${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/storage/${data.hero_image}`)
                : "https://images.unsplash.com/photo-1593095948071-474c5cc2989d?q=80&w=800&auto=format&fit=crop"
              } 
              alt={data.title_line_1 || "Hero Image"} 
              className={`relative z-10 w-full h-full object-cover rounded-[3rem] shadow-2xl transition-transform duration-700 ease-out ${data.animate_rotation ? 'rotate-3 hover:rotate-0' : ''}`}
            />
            
            {/* Tarjeta Flotante (Glassmorphism) */}
            {(data.floating_card_title || data.floating_card_subtitle) && (
              <div className="absolute -bottom-6 -left-6 z-20 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-4 flex items-center gap-4">
                <div className="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center">
                  <Leaf className="w-6 h-6" />
                </div>
                <div>
                  {data.floating_card_title && <p className="text-sm font-bold text-gray-900">{data.floating_card_title}</p>}
                  {data.floating_card_subtitle && <p className="text-xs text-gray-500">{data.floating_card_subtitle}</p>}
                </div>
              </div>
            )}
          </div>
        </div>

      </div>
    </section>
  );
}
