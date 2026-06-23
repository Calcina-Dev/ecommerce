import { useState, useEffect } from "react";
import Link from "next/link";
import { ChevronLeft, ChevronRight } from "lucide-react";

export function CarouselBlock({ data }: { data: any }) {
  const [currentIndex, setCurrentIndex] = useState(0);

  const slides = data.slides || [];
  const autoplay = data.autoplay !== false;

  useEffect(() => {
    if (!autoplay || slides.length <= 1) return;
    
    const interval = setInterval(() => {
      setCurrentIndex((prev) => (prev + 1) % slides.length);
    }, 5000);
    
    return () => clearInterval(interval);
  }, [autoplay, slides.length]);

  if (slides.length === 0) return null;

  return (
    <section className="max-w-7xl mx-auto px-6 py-6">
      <div className="relative w-full h-[300px] md:h-[450px] rounded-3xl overflow-hidden group">
        {slides.map((slide: any, index: number) => {
          const isCurrent = index === currentIndex;
          const imageUrl = slide.image.startsWith('http') ? slide.image : `${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/storage/${slide.image}`;
          
          return (
            <div 
              key={index}
              className={`absolute inset-0 transition-opacity duration-700 ease-in-out ${isCurrent ? 'opacity-100' : 'opacity-0 pointer-events-none'}`}
            >
              {slide.link ? (
                <Link href={slide.link} className="block w-full h-full">
                  <img src={imageUrl} alt="Banner" className="w-full h-full object-cover" />
                </Link>
              ) : (
                <img src={imageUrl} alt="Banner" className="w-full h-full object-cover" />
              )}
            </div>
          );
        })}

        {slides.length > 1 && (
          <>
            <button 
              onClick={() => setCurrentIndex((prev) => (prev - 1 + slides.length) % slides.length)}
              className="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/50 hover:bg-white backdrop-blur-md rounded-full flex items-center justify-center text-gray-900 shadow-sm opacity-0 group-hover:opacity-100 transition-all"
            >
              <ChevronLeft className="w-6 h-6" />
            </button>
            <button 
              onClick={() => setCurrentIndex((prev) => (prev + 1) % slides.length)}
              className="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/50 hover:bg-white backdrop-blur-md rounded-full flex items-center justify-center text-gray-900 shadow-sm opacity-0 group-hover:opacity-100 transition-all"
            >
              <ChevronRight className="w-6 h-6" />
            </button>

            <div className="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
              {slides.map((_: any, index: number) => (
                <button
                  key={index}
                  onClick={() => setCurrentIndex(index)}
                  className={`w-2 h-2 rounded-full transition-all ${index === currentIndex ? 'bg-white w-6' : 'bg-white/50'}`}
                />
              ))}
            </div>
          </>
        )}
      </div>
    </section>
  );
}
