"use client"
import { useEffect, useState } from "react";
import { useCatalogStore } from "@/store/useCatalogStore";
import { ProductCard } from "@/components/ProductCard";

export default function CatalogPage() {
  const { filters, setFilters } = useCatalogStore();
  const [products, setProducts] = useState<any[]>([]);
  const [filterData, setFilterData] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  // Cargar filtros disponibles
  useEffect(() => {
    fetch('http://localhost:8000/api/catalog/filters')
      .then(res => res.json())
      .then(data => setFilterData(data))
      .catch(console.error);
  }, []);

  // Cargar productos
  useEffect(() => {
    setLoading(true);
    const queryParams = new URLSearchParams();
    if (filters.categoryId) queryParams.append('category_id', filters.categoryId.toString());
    if (filters.brandId) queryParams.append('brand_id', filters.brandId.toString());
    if (filters.search) queryParams.append('search', filters.search);

    fetch(`http://localhost:8000/api/catalog/products?${queryParams.toString()}`)
      .then(res => res.json())
      .then(data => {
        setProducts(data.data || []);
        setLoading(false);
      })
      .catch(err => {
        console.error(err);
        setLoading(false);
      });
  }, [filters]);

  return (
    <div className="max-w-7xl mx-auto px-6 sm:px-12 py-12">
      <div className="mb-8">
        <h1 className="text-3xl font-bold">Catálogo de Productos</h1>
        <p className="text-muted-foreground mt-2">Encuentra los mejores suplementos para ti.</p>
      </div>

      <div className="flex flex-col lg:flex-row gap-8">
        {/* Sidebar Filters */}
        <aside className="w-full lg:w-64 flex-shrink-0 space-y-8">
          <div>
            <h3 className="font-medium text-lg mb-4">Buscar</h3>
            <input 
              type="text" 
              placeholder="Ej. Vitamina C..." 
              className="w-full px-4 py-2 border rounded-xl bg-background"
              value={filters.search || ""}
              onChange={(e) => setFilters({ search: e.target.value })}
            />
          </div>

          {filterData?.categories && (
            <div>
              <h3 className="font-medium text-lg mb-4">Categorías</h3>
              <div className="space-y-2">
                <button 
                  onClick={() => setFilters({ categoryId: undefined })}
                  className={`block w-full text-left px-3 py-2 rounded-xl text-sm transition-colors ${!filters.categoryId ? 'bg-muted font-medium' : 'hover:bg-muted/50'}`}
                >
                  Todas
                </button>
                {filterData.categories.map((cat: any) => (
                  <button 
                    key={cat.id}
                    onClick={() => setFilters({ categoryId: cat.id })}
                    className={`block w-full text-left px-3 py-2 rounded-xl text-sm transition-colors ${filters.categoryId === cat.id ? 'bg-muted font-medium' : 'hover:bg-muted/50'}`}
                  >
                    {cat.name}
                  </button>
                ))}
              </div>
            </div>
          )}

          {filterData?.brands && (
            <div>
              <h3 className="font-medium text-lg mb-4">Marcas</h3>
              <div className="space-y-2">
                <button 
                  onClick={() => setFilters({ brandId: undefined })}
                  className={`block w-full text-left px-3 py-2 rounded-xl text-sm transition-colors ${!filters.brandId ? 'bg-muted font-medium' : 'hover:bg-muted/50'}`}
                >
                  Todas
                </button>
                {filterData.brands.map((brand: any) => (
                  <button 
                    key={brand.id}
                    onClick={() => setFilters({ brandId: brand.id })}
                    className={`block w-full text-left px-3 py-2 rounded-xl text-sm transition-colors ${filters.brandId === brand.id ? 'bg-muted font-medium' : 'hover:bg-muted/50'}`}
                  >
                    {brand.name}
                  </button>
                ))}
              </div>
            </div>
          )}
        </aside>

        {/* Product Grid */}
        <main className="flex-1">
          {loading ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              {[1, 2, 3, 4, 5, 6].map(i => (
                <div key={i} className="animate-pulse bg-muted rounded-2xl aspect-[3/4]"></div>
              ))}
            </div>
          ) : products.length > 0 ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              {products.map(product => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>
          ) : (
            <div className="text-center py-20 bg-muted/30 rounded-2xl border border-dashed">
              <h3 className="text-xl font-medium text-muted-foreground">No se encontraron productos</h3>
              <button 
                onClick={() => setFilters({ search: undefined, categoryId: undefined, brandId: undefined })}
                className="mt-4 text-primary font-medium hover:underline"
              >
                Limpiar filtros
              </button>
            </div>
          )}
        </main>
      </div>
    </div>
  );
}
