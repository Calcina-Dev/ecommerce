"use client"
import { useEffect, useState } from "react";
import Image from "next/image";
import Link from "next/link";
import { useParams } from "next/navigation";
import { Button } from "@/components/ui/button";
import { useCartStore } from "@/store/useCartStore";

export default function ProductDetailPage() {
  const params = useParams();
  const slug = params.slug;
  const [product, setProduct] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const addItem = useCartStore((state) => state.addItem);

  useEffect(() => {
    fetch(`http://localhost:8000/api/catalog/products/${slug}`)
      .then(res => res.json())
      .then(data => {
        setProduct(data);
        setLoading(false);
      })
      .catch(err => {
        console.error(err);
        setLoading(false);
      });
  }, [slug]);

  if (loading) {
    return <div className="max-w-6xl mx-auto px-6 py-12 animate-pulse">Cargando...</div>;
  }

  if (!product) {
    return <div className="max-w-6xl mx-auto px-6 py-12 text-center">Producto no encontrado.</div>;
  }

  const images = product.images?.length > 0 
    ? product.images.map((img: any) => `http://localhost:8000/storage/${img.image_url}`)
    : ["https://images.unsplash.com/photo-1584308666744-24d5e47ac9db?q=80&w=600&auto=format&fit=crop"];

  return (
    <div className="bg-background min-h-screen">
      <div className="max-w-6xl mx-auto px-6 sm:px-12 py-12">
        <nav className="text-sm text-muted-foreground mb-8">
          <Link href="/" className="hover:text-foreground">Inicio</Link>
          <span className="mx-2">/</span>
          <Link href="/productos" className="hover:text-foreground">Catálogo</Link>
          <span className="mx-2">/</span>
          <span className="text-foreground">{product.name}</span>
        </nav>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-20">
          {/* Gallery */}
          <div className="space-y-4">
            <div className="relative aspect-square rounded-3xl overflow-hidden bg-muted border">
              <Image 
                src={images[0]} 
                alt={product.name}
                fill
                className="object-cover"
              />
            </div>
            {images.length > 1 && (
              <div className="flex gap-4 overflow-x-auto pb-2">
                {images.map((img: string, idx: number) => (
                  <div key={idx} className="relative w-20 h-20 rounded-xl overflow-hidden bg-muted border flex-shrink-0 cursor-pointer">
                    <Image src={img} alt="" fill className="object-cover" />
                  </div>
                ))}
              </div>
            )}
          </div>

          {/* Details */}
          <div className="flex flex-col justify-center">
            {product.brand && (
              <span className="text-sm font-bold text-muted-foreground uppercase tracking-wider mb-2 block">
                {product.brand.name}
              </span>
            )}
            <h1 className="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight mb-4">{product.name}</h1>
            
            <div className="flex items-center gap-4 mb-6">
              <span className="text-3xl font-bold">S/ {parseFloat(product.price).toFixed(2)}</span>
              {product.compare_at_price && (
                <span className="text-xl text-muted-foreground line-through">S/ {parseFloat(product.compare_at_price).toFixed(2)}</span>
              )}
            </div>

            <p className="text-lg text-foreground/80 mb-8 leading-relaxed">
              {product.short_description || "Descripción breve no disponible."}
            </p>

            <div className="space-y-4 mb-8">
              <div className="flex items-center gap-4">
                <Button 
                  size="lg" 
                  className="w-full text-lg rounded-2xl h-14"
                  onClick={() => addItem({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    image_url: product.primary_image?.image_url || product.images?.[0]?.image_url || null,
                    quantity: 1,
                    slug: product.slug
                  })}
                >
                  Agregar al Carrito
                </Button>
              </div>
              <p className="text-sm text-center text-muted-foreground">
                Envío gratis en compras mayores a S/ 150
              </p>
            </div>

            <div className="prose prose-sm sm:prose-base dark:prose-invert">
              <h3 className="text-xl font-semibold mb-2">Detalles del Producto</h3>
              <div dangerouslySetInnerHTML={{ __html: product.description || "Sin descripción detallada." }} />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
