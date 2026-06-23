"use client"
import { useEffect, useState } from "react";
import { useParams } from "next/navigation";
import { HeroModernBlock } from "@/components/blocks/HeroModernBlock";
import { CategoryGridBlock } from "@/components/blocks/CategoryGridBlock";
import { FeaturedProductsBlock } from "@/components/blocks/FeaturedProductsBlock";
import { CarouselBlock } from "@/components/blocks/CarouselBlock";
import { ValuePropBlock } from "@/components/blocks/ValuePropBlock";

export default function DynamicStorefrontPage() {
  const { slug } = useParams();
  const [pageData, setPageData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(false);

  useEffect(() => {
    if (!slug) return;
    
    fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/storefront/pages/${slug}`)
      .then(res => {
        if (!res.ok) {
          setError(true);
          throw new Error('Page not found');
        }
        return res.json();
      })
      .then(data => {
        setPageData(data);
        setLoading(false);
      })
      .catch(err => {
        console.error(err);
        setLoading(false);
      });
  }, [slug]);

  if (loading) {
    return (
      <div className="min-h-screen bg-transparent p-12">
        <div className="max-w-7xl mx-auto animate-pulse flex flex-col gap-12">
          <div className="h-96 bg-gray-200 rounded-3xl w-full"></div>
          <div className="h-32 bg-gray-200 rounded-3xl w-full"></div>
        </div>
      </div>
    );
  }

  if (error || !pageData || !pageData.blocks) {
    return (
      <div className="min-h-screen flex items-center justify-center text-gray-500">
        <div className="text-center">
          <h1 className="text-2xl font-bold mb-2">404 - Página no encontrada</h1>
          <p>La página que buscas no existe o no está configurada.</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-transparent pb-20">
      {/* Optional Page Title Header for text-heavy pages like About Us */}
      {pageData.blocks.length > 0 && pageData.blocks[0].type === 'value_proposition' && (
        <div className="bg-gray-50 py-12 text-center border-b border-gray-100">
          <h1 className="text-4xl font-bold text-gray-900">{pageData.title}</h1>
        </div>
      )}
      
      {pageData.blocks.map((block: any, index: number) => {
        switch (block.type) {
          case 'hero_modern':
            return <HeroModernBlock key={index} data={block.data} />;
          case 'category_grid':
            return (
              <main key={index} className="max-w-7xl mx-auto px-6 py-12">
                <CategoryGridBlock data={block.data} />
              </main>
            );
          case 'featured_products':
            return <FeaturedProductsBlock key={index} data={block.data} />;
          case 'carousel':
            return <CarouselBlock key={index} data={block.data} />;
          case 'value_proposition':
            return <ValuePropBlock key={index} data={block.data} />;
          default:
            return <div key={index} className="p-4 border border-red-200 text-red-500 text-sm">Bloque no soportado: {block.type}</div>;
        }
      })}
    </div>
  );
}
