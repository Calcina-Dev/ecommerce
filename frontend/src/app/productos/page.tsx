"use client"
import { Suspense, useEffect, useState } from "react";
import { useSearchParams } from "next/navigation";
import { useCatalogStore } from "@/store/useCatalogStore";
import { ProductCard } from "@/components/ProductCard";

function CatalogContent() {
  const { filters, setFilters } = useCatalogStore();
  const [products, setProducts] = useState<any[]>([]);
  const [pagination, setPagination] = useState({ current_page: 1, last_page: 1 });
  const [filterData, setFilterData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const searchParams = useSearchParams();

  // Sincronizar URL parameters con el store al cargar la página
  useEffect(() => {
    const categoryId = searchParams.get('category');
    const search = searchParams.get('search');
    const ofertas = searchParams.get('ofertas');
    
    // Si hay parámetros en la URL, los seteamos (solo en el montaje inicial)
    if (categoryId || search || ofertas) {
      setFilters({ 
        categoryId: categoryId ? parseInt(categoryId) : undefined,
        search: search || undefined,
        onSale: ofertas === 'true' || ofertas === '1'
      });
    }
  }, [searchParams]);

  // Cargar filtros disponibles
  useEffect(() => {
    fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/catalog/filters`)
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
    if (filters.page) queryParams.append('page', filters.page.toString());
    if (filters.onSale) queryParams.append('on_sale', '1');

    fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/catalog/products?${queryParams.toString()}`)
      .then(res => res.json())
      .then(data => {
        setProducts(data.data || []);
        setPagination({ current_page: data.current_page || 1, last_page: data.last_page || 1 });
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
              onChange={(e) => setFilters({ search: e.target.value, page: 1 })}
            />
          </div>

          <div>
            <h3 className="font-medium text-lg mb-4">Ofertas</h3>
            <label className="flex items-center space-x-3 cursor-pointer group">
              <input 
                type="checkbox"
                className="w-5 h-5 rounded border-gray-300 text-primary focus:ring-primary cursor-pointer transition-colors"
                checked={!!filters.onSale}
                onChange={(e) => setFilters({ onSale: e.target.checked, page: 1 })}
              />
              <span className="text-sm font-medium group-hover:text-primary transition-colors">Solo productos con descuento</span>
            </label>
          </div>

          {filterData?.categories && (
            <div>
              <h3 className="font-medium text-lg mb-4">Categorías</h3>
              <div className="space-y-2">
                <button 
                  onClick={() => setFilters({ categoryId: undefined, page: 1 })}
                  className={`block w-full text-left px-3 py-2 rounded-xl text-sm transition-colors ${!filters.categoryId ? 'bg-muted font-medium' : 'hover:bg-muted/50'}`}
                >
                  Todas
                </button>
                {filterData.categories.map((cat: any) => (
                  <button 
                    key={cat.id}
                    onClick={() => setFilters({ categoryId: cat.id, page: 1 })}
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
                  onClick={() => setFilters({ brandId: undefined, page: 1 })}
                  className={`block w-full text-left px-3 py-2 rounded-xl text-sm transition-colors ${!filters.brandId ? 'bg-muted font-medium' : 'hover:bg-muted/50'}`}
                >
                  Todas
                </button>
                {filterData.brands.map((brand: any) => (
                  <button 
                    key={brand.id}
                    onClick={() => setFilters({ brandId: brand.id, page: 1 })}
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
                <div key={i} className="animate-shimmer rounded-2xl aspect-[3/4]"></div>
              ))}
            </div>
          ) : products.length > 0 ? (
            <>
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                {products.map(product => (
                  <ProductCard key={product.id} product={product} />
                ))}
              </div>

              {/* Pagination Controls */}
              {pagination.last_page > 1 && (
                <div className="flex justify-center items-center gap-4 mt-12">
                  <button
                    onClick={() => {
                      if (pagination.current_page > 1) {
                        setFilters({ page: pagination.current_page - 1 });
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                      }
                    }}
                    disabled={pagination.current_page === 1}
                    className="px-4 py-2 border rounded-xl disabled:opacity-50 hover:bg-muted transition-colors"
                  >
                    Anterior
                  </button>
                  <span className="text-sm font-medium">
                    Página {pagination.current_page} de {pagination.last_page}
                  </span>
                  <button
                    onClick={() => {
                      if (pagination.current_page < pagination.last_page) {
                        setFilters({ page: pagination.current_page + 1 });
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                      }
                    }}
                    disabled={pagination.current_page === pagination.last_page}
                    className="px-4 py-2 border rounded-xl disabled:opacity-50 hover:bg-muted transition-colors"
                  >
                    Siguiente
                  </button>
                </div>
              )}
            </>
          ) : (
            <div className="text-center py-24 bg-white rounded-3xl border shadow-[0_2px_10px_rgba(0,0,0,0.02)] ring-1 ring-black/[0.03]">
              <div className="mx-auto w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6 relative">
                <div className="absolute inset-0 border-2 border-gray-100 rounded-full animate-ping opacity-30"></div>
                <svg className="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
              <h3 className="text-xl font-bold text-gray-900 tracking-tight">No encontramos resultados</h3>
              <p className="mt-2 text-sm text-gray-500 max-w-sm mx-auto">Prueba con otros términos de búsqueda o quita los filtros actuales para ver más productos.</p>
              <button 
                onClick={() => setFilters({ search: undefined, categoryId: undefined, brandId: undefined, onSale: undefined, page: 1 })}
                className="mt-6 px-6 py-2.5 bg-gray-900 text-white font-medium rounded-xl hover:bg-gray-800 transition-all active:scale-95 duration-200 ease-[var(--spring-easing)] shadow-sm"
              >
                Limpiar todos los filtros
              </button>
            </div>
          )}
        </main>
      </div>
    </div>
  );
}

export default function CatalogPage() {
  return (
    <Suspense fallback={<div className="max-w-7xl mx-auto px-6 sm:px-12 py-12 text-center text-muted-foreground">Cargando catálogo...</div>}>
      <CatalogContent />
    </Suspense>
  );
}
