import { Metadata } from "next";
import ProductDetailClient from "@/components/ProductDetailClient";
import Link from "next/link";

async function getProduct(slug: string) {
  try {
    const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/catalog/products/${slug}`, {
      next: { revalidate: 60 } // ISR cada 60 segundos para SEO y rendimiento
    });
    if (!res.ok) return null;
    const data = await res.json();
    if (!data || !data.id || !data.name || data.price === undefined || data.price === null) {
      return null;
    }
    return data;
  } catch (err) {
    console.error(err);
    return null;
  }
}

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params;
  const product = await getProduct(slug);
  
  if (!product) {
    return {
      title: "Producto no encontrado | CompraSaludable",
      description: "El producto que estás buscando no se encuentra disponible en el catálogo en este momento."
    };
  }

  const imageUrl = product.primary_image?.image_url
    ? (product.primary_image.image_url.startsWith('http')
        ? product.primary_image.image_url
        : `${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/storage/${product.primary_image.image_url}`)
    : (product.images?.[0]?.image_url
        ? (product.images[0].image_url.startsWith('http')
            ? product.images[0].image_url
            : `${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/storage/${product.images[0].image_url}`)
        : "https://images.unsplash.com/photo-1584308666744-24d5e47ac9db?q=80&w=600&auto=format&fit=crop");

  return {
    title: `${product.name} | CompraSaludable`,
    description: product.short_description || `Compra ${product.name} al mejor precio en CompraSaludable. Calidad clínica garantizada.`,
    openGraph: {
      title: `${product.name} | CompraSaludable`,
      description: product.short_description || `Compra ${product.name} al mejor precio en CompraSaludable.`,
      images: [
        {
          url: imageUrl,
          width: 800,
          height: 800,
          alt: product.name,
        }
      ],
      type: "website",
    },
  };
}

export default async function ProductDetailPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const product = await getProduct(slug);

  if (!product) {
    return (
      <div className="bg-background min-h-screen flex items-center justify-center">
        <div className="max-w-md mx-auto px-6 py-16 text-center">
          <div className="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-6 text-2xl">
            📦
          </div>
          <h2 className="text-2xl font-bold text-foreground mb-3">Producto no disponible</h2>
          <p className="text-muted-foreground mb-8 text-sm leading-relaxed">
            El producto que estás buscando fue modificado, cambió de enlace o ya no se encuentra en el catálogo en este momento.
          </p>
          <Link 
            href="/productos" 
            className="inline-flex items-center justify-center px-6 py-3.5 rounded-xl bg-primary text-primary-foreground font-bold text-sm hover:bg-primary/90 transition-all shadow-md hover:shadow-lg"
          >
            ← Volver al Catálogo
          </Link>
        </div>
      </div>
    );
  }

  // Schema.org JSON-LD para producto (SEO Técnico y Rich Snippets de Google)
  const imageUrl = product.primary_image?.image_url
    ? (product.primary_image.image_url.startsWith('http')
        ? product.primary_image.image_url
        : `${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/storage/${product.primary_image.image_url}`)
    : (product.images?.[0]?.image_url
        ? (product.images[0].image_url.startsWith('http')
            ? product.images[0].image_url
            : `${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/storage/${product.images[0].image_url}`)
        : "https://images.unsplash.com/photo-1584308666744-24d5e47ac9db?q=80&w=600&auto=format&fit=crop");

  const jsonLd = {
    "@context": "https://schema.org",
    "@type": "Product",
    "name": product.name,
    "image": imageUrl,
    "description": product.short_description || product.name,
    "sku": product.sku || `${product.id}`,
    "brand": {
      "@type": "Brand",
      "name": product.brand?.name || "CompraSaludable"
    },
    "offers": {
      "@type": "Offer",
      "url": `${process.env.NEXT_PUBLIC_FRONTEND_URL || "http://localhost:3000"}/productos/${product.slug}`,
      "priceCurrency": "PEN",
      "price": parseFloat(product.price).toFixed(2),
      "availability": "https://schema.org/InStock",
      "itemCondition": "https://schema.org/NewCondition"
    }
  };

  return (
    <>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }}
      />
      <ProductDetailClient product={product} />
    </>
  );
}
