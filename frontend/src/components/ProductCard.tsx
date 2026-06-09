import Image from "next/image";
import Link from "next/link";
import { Button } from "./ui/button";
import { useCartStore } from "@/store/useCartStore";

interface Product {
  id: number;
  name: string;
  slug: string;
  price: string;
  compare_at_price: string | null;
  primary_image?: {
    image_url: string;
  };
  brand?: {
    name: string;
  };
}

export function ProductCard({ product }: { product: Product }) {
  const addItem = useCartStore((state) => state.addItem);

  const imageUrl = product.primary_image?.image_url 
    ? `http://localhost:8000/storage/${product.primary_image.image_url}` 
    : "https://images.unsplash.com/photo-1584308666744-24d5e47ac9db?q=80&w=600&auto=format&fit=crop";

  const handleAddToCart = (e: React.MouseEvent) => {
    e.preventDefault(); // Evitar navegación al detalle
    addItem({
      id: product.id,
      name: product.name,
      price: product.price,
      image_url: product.primary_image?.image_url || null,
      quantity: 1,
      slug: product.slug
    });
  };

  return (
    <Link href={`/productos/${product.slug}`} className="group block">
      <div className="bg-card rounded-2xl overflow-hidden border shadow-sm transition-all hover:shadow-md h-full flex flex-col">
        <div className="relative aspect-square bg-muted/50 overflow-hidden">
          <Image
            src={imageUrl}
            alt={product.name}
            fill
            className="object-cover transition-transform group-hover:scale-105 duration-300"
          />
        </div>
        <div className="p-5 flex flex-col flex-grow">
          {product.brand && (
            <span className="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1">
              {product.brand.name}
            </span>
          )}
          <h3 className="font-medium text-lg leading-tight mb-2 line-clamp-2">
            {product.name}
          </h3>
          <div className="mt-auto pt-4 flex items-center justify-between">
            <div className="flex flex-col">
              <span className="text-lg font-bold">
                S/ {parseFloat(product.price).toFixed(2)}
              </span>
              {product.compare_at_price && (
                <span className="text-sm text-muted-foreground line-through">
                  S/ {parseFloat(product.compare_at_price).toFixed(2)}
                </span>
              )}
            </div>
            <Button 
              variant="default" 
              size="sm" 
              className="rounded-xl px-4"
              onClick={handleAddToCart}
            >
              Agregar
            </Button>
          </div>
        </div>
      </div>
    </Link>
  );
}
