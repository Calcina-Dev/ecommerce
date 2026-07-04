"use client"
import { Suspense, useEffect, useState } from "react";
import { useSearchParams } from "next/navigation";
import { useCatalogStore } from "@/store/useCatalogStore";
import { ProductCard } from "@/components/ProductCard";

function CatalogContent() {
  const { filters, setFilters, filterData, setFilterData } = useCatalogStore();
  const [products, setProducts] = useState<any[]>([]);
  const [pagination, setPagination] = useState({ current_page: 1, last_page: 1 });
  const [loading, setLoading] = useState(true);
  const [expandedCats, setExpandedCats] = useState<Record<number, boolean>>({});
  const [showMobileFilters, setShowMobileFilters] = useState(false);
  const [isCategoriesExpanded, setIsCategoriesExpanded] = useState(true);
  const [isBrandsExpanded, setIsBrandsExpanded] = useState(true);
  const [showAllBrands, setShowAllBrands] = useState(false);
  const [brandSearch, setBrandSearch] = useState("");
  const searchParams = useSearchParams();

  // Sincronizar URL parameters con el store siempre que cambie la URL
  useEffect(() => {
    const categoryId = searchParams.get('category');
    const search = searchParams.get('search');
    const ofertas = searchParams.get('ofertas');
    
    setFilters({ 
      categoryId: categoryId ? parseInt(categoryId) : undefined,
      search: search || undefined,
      onSale: ofertas === 'true' || ofertas === '1',
      page: 1
    });
  }, [searchParams]);

  // Cargar filtros disponibles si no existen en el store
  useEffect(() => {
    if (!filterData) {
      fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/catalog/filters`)
        .then(res => res.json())
        .then(data => {
          if (Array.isArray(data?.categories)) setFilterData(data);
        })
        .catch(console.error);
    }
  }, [filterData]);

  // Cargar productos
  useEffect(() => {
    const timer = setTimeout(() => {
      setLoading(true);
      const queryParams = new URLSearchParams();
      if (filters.categoryId && !isNaN(Number(filters.categoryId))) queryParams.append('category_id', filters.categoryId.toString());
      if (filters.brandId && !isNaN(Number(filters.brandId))) queryParams.append('brand_id', filters.brandId.toString());
      if (filters.search && filters.search !== 'undefined') queryParams.append('search', filters.search);
      if (filters.page && !isNaN(Number(filters.page))) queryParams.append('page', filters.page.toString());
      if (filters.onSale) queryParams.append('on_sale', '1');

      fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/catalog/products?${queryParams.toString()}`)
        .then(res => res.json())
        .then(data => {
          setProducts(Array.isArray(data?.data) ? data.data : []);
          setPagination({ current_page: data?.current_page || 1, last_page: data?.last_page || 1 });
          setLoading(false);
        })
        .catch(err => {
          console.error(err);
          setLoading(false);
        });
    }, 300);
    return () => clearTimeout(timer);
  }, [filters]);

  const renderFilterContent = () => (
    <div className="space-y-6 text-left">
      <div>
        <h3 className="font-bold text-base sm:text-lg mb-3 text-gray-900">Buscar</h3>
        <div className="relative">
          <input 
            type="text" 
            placeholder="Ej. Vitamina C..." 
            className="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50/50 focus:bg-white focus:border-accent focus:ring-2 focus:ring-accent/20 text-sm transition-all"
            value={filters.search || ""}
            onChange={(e) => setFilters({ search: e.target.value || undefined, page: 1 })}
          />
          <svg className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </div>
      </div>

      <div className="border-t border-gray-100 pt-5">
        <h3 className="font-bold text-base sm:text-lg mb-3 text-gray-900">Ofertas</h3>
        <label className="flex items-center space-x-3 cursor-pointer group p-2 rounded-xl hover:bg-gray-50 transition-colors">
          <input 
            type="checkbox"
            className="w-5 h-5 rounded border-gray-300 text-accent focus:ring-accent cursor-pointer transition-colors"
            checked={!!filters.onSale}
            onChange={(e) => setFilters({ onSale: e.target.checked || undefined, page: 1 })}
          />
          <span className="text-sm font-medium text-gray-700 group-hover:text-accent transition-colors flex items-center gap-1.5">
            <span className="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
            Solo productos con descuento
          </span>
        </label>
      </div>

      {Array.isArray(filterData?.categories) && (
        <div className="border-t border-gray-100 pt-5">
          <button 
            type="button"
            onClick={() => setIsCategoriesExpanded(!isCategoriesExpanded)}
            className="w-full flex items-center justify-between font-bold text-base sm:text-lg text-gray-900 mb-3 group"
          >
            <span>Categorías</span>
            <svg className={`w-5 h-5 text-gray-400 group-hover:text-accent transition-transform duration-200 ${isCategoriesExpanded ? 'rotate-180 text-accent' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path></svg>
          </button>
          
          {isCategoriesExpanded && (
            <div className="space-y-1 max-h-[55vh] overflow-y-auto pr-1 custom-scrollbar animate-in fade-in duration-200">
              <button 
                onClick={() => { setFilters({ categoryId: undefined, page: 1 }); setShowMobileFilters(false); }}
                className={`block w-full text-left px-3 py-2 rounded-xl text-sm transition-all ${!filters.categoryId ? 'bg-accent text-white font-bold shadow-sm' : 'hover:bg-gray-100 text-gray-700 font-medium'}`}
              >
                Todas las categorías
              </button>
              {filterData.categories.map((parent: any) => {
                const isParentExpanded = !!expandedCats[parent.id] || filters.categoryId === parent.id;
                const hasParentChildren = parent.children && parent.children.length > 0;
                return (
                  <div key={parent.id} className="pt-1">
                    <div className="flex items-center justify-between gap-1">
                      <button 
                        onClick={() => {
                          setFilters({ categoryId: parent.id, page: 1 });
                          setShowMobileFilters(false);
                          if (hasParentChildren && !isParentExpanded) {
                            setExpandedCats(prev => ({ ...prev, [parent.id]: true }));
                          }
                        }}
                        className={`flex-1 text-left px-3 py-2 rounded-xl text-sm transition-all font-bold ${filters.categoryId === parent.id ? 'bg-accent text-white shadow-sm' : 'hover:bg-gray-100 text-gray-800'}`}
                      >
                        {parent.name}
                      </button>
                      {hasParentChildren && (
                        <button
                          type="button"
                          onClick={(e) => {
                            e.stopPropagation();
                            setExpandedCats(prev => ({ ...prev, [parent.id]: !isParentExpanded }));
                          }}
                          className={`p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-transform duration-200 ${isParentExpanded ? 'rotate-90 text-accent' : ''}`}
                        >
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                      )}
                    </div>
                    {isParentExpanded && hasParentChildren && (
                      <div className="pl-3 mt-1 space-y-1 border-l-2 border-gray-100 ml-3">
                        {parent.children.map((child: any) => {
                          const isChildExpanded = !!expandedCats[child.id] || filters.categoryId === child.id;
                          const hasChildChildren = child.children && child.children.length > 0;
                          return (
                            <div key={child.id}>
                              <div className="flex items-center justify-between gap-1">
                                <button 
                                  onClick={() => {
                                    setFilters({ categoryId: child.id, page: 1 });
                                    setShowMobileFilters(false);
                                    if (hasChildChildren && !isChildExpanded) {
                                      setExpandedCats(prev => ({ ...prev, [child.id]: true }));
                                    }
                                  }}
                                  className={`flex-1 text-left px-2.5 py-1.5 rounded-lg text-xs transition-all font-medium ${filters.categoryId === child.id ? 'bg-accent/10 text-accent font-bold' : 'hover:bg-gray-50 text-gray-600'}`}
                                >
                                  {child.name}
                                </button>
                                {hasChildChildren && (
                                  <button
                                    type="button"
                                    onClick={(e) => {
                                      e.stopPropagation();
                                      setExpandedCats(prev => ({ ...prev, [child.id]: !isChildExpanded }));
                                    }}
                                    className={`p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-transform duration-200 ${isChildExpanded ? 'rotate-90 text-accent' : ''}`}
                                  >
                                    <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path></svg>
                                  </button>
                                )}
                              </div>
                              {isChildExpanded && hasChildChildren && (
                                <div className="pl-3 mt-0.5 space-y-0.5 border-l border-gray-100 ml-2">
                                  {child.children.map((sub: any) => (
                                    <button 
                                      key={sub.id}
                                      onClick={() => { setFilters({ categoryId: sub.id, page: 1 }); setShowMobileFilters(false); }}
                                      className={`block w-full text-left px-2 py-1 rounded-md text-[11px] transition-all ${filters.categoryId === sub.id ? 'text-accent font-bold bg-accent/5' : 'hover:text-accent text-gray-400'}`}
                                    >
                                      • {sub.name}
                                    </button>
                                  ))}
                                </div>
                              )}
                            </div>
                          );
                        })}
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          )}
        </div>
      )}

      {Array.isArray(filterData?.brands) && (
        <div className="border-t border-gray-100 pt-5">
          <button 
            type="button"
            onClick={() => setIsBrandsExpanded(!isBrandsExpanded)}
            className="w-full flex items-center justify-between font-bold text-base sm:text-lg text-gray-900 mb-3 group"
          >
            <span>Marcas</span>
            <svg className={`w-5 h-5 text-gray-400 group-hover:text-accent transition-transform duration-200 ${isBrandsExpanded ? 'rotate-180 text-accent' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path></svg>
          </button>

          {isBrandsExpanded && (
            <div className="space-y-2 animate-in fade-in duration-200">
              {filterData.brands.length > 6 && (
                <div className="relative mb-2">
                  <input 
                    type="text" 
                    placeholder="Buscar marca..." 
                    value={brandSearch}
                    onChange={(e) => setBrandSearch(e.target.value)}
                    className="w-full px-3 py-1.5 pl-8 text-xs bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-accent focus:ring-1 focus:ring-accent/20"
                  />
                  <svg className="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
              )}

              <button 
                onClick={() => { setFilters({ brandId: undefined, page: 1 }); setShowMobileFilters(false); }}
                className={`block w-full text-left px-3 py-2 rounded-xl text-sm transition-all ${!filters.brandId ? 'bg-accent text-white font-bold shadow-sm' : 'hover:bg-gray-100 text-gray-700 font-medium'}`}
              >
                Todas las marcas
              </button>

              <div className="space-y-1 max-h-[45vh] overflow-y-auto pr-1 custom-scrollbar">
                {filterData.brands
                  .filter((b: any) => !brandSearch || b.name.toLowerCase().includes(brandSearch.toLowerCase()))
                  .slice(0, showAllBrands || brandSearch ? undefined : 6)
                  .map((brand: any) => (
                    <button 
                      key={brand.id}
                      onClick={() => { setFilters({ brandId: brand.id, page: 1 }); setShowMobileFilters(false); }}
                      className={`block w-full text-left px-3 py-2 rounded-xl text-sm transition-all ${filters.brandId === brand.id ? 'bg-accent text-white font-bold shadow-sm' : 'hover:bg-gray-100 text-gray-700 font-medium'}`}
                    >
                      {brand.name}
                    </button>
                  ))}
              </div>

              {filterData.brands.length > 6 && !brandSearch && (
                <button 
                  type="button"
                  onClick={() => setShowAllBrands(!showAllBrands)}
                  className="w-full py-2 mt-1 text-xs font-bold text-accent hover:text-accent/80 flex items-center justify-center gap-1 border border-accent/20 rounded-xl bg-accent/5 transition-colors"
                >
                  {showAllBrands ? (
                    <>– Ver menos marcas</>
                  ) : (
                    <>+ Ver todas ({filterData.brands.length})</>
                  )}
                </button>
              )}
            </div>
          )}
        </div>
      )}
    </div>
  );

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 py-6 sm:py-12">
      <div className="mb-6 sm:mb-8">
        <h1 className="text-2xl sm:text-3xl font-bold text-gray-900">Catálogo de Productos</h1>
        <p className="text-sm sm:text-base text-muted-foreground mt-1 sm:mt-2">Encuentra los mejores suplementos para tu bienestar.</p>
      </div>

      <div className="flex flex-col lg:flex-row gap-8">
        {/* Desktop Sidebar Filters */}
        <aside className="hidden lg:block w-64 flex-shrink-0 space-y-6 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm h-fit sticky top-28">
          {renderFilterContent()}
        </aside>

        {/* Product Grid & Mobile Controls */}
        <main className="flex-1">
          {/* Mobile Filter Toolbar */}
          <div className="lg:hidden mb-6 flex items-center justify-between gap-2.5 bg-white p-2.5 rounded-2xl border border-gray-200/80 shadow-sm sticky top-20 z-30">
            <button
              type="button"
              onClick={() => setShowMobileFilters(true)}
              className="flex-1 flex items-center justify-center gap-2 py-2.5 px-4 bg-gray-900 text-white font-bold text-xs sm:text-sm rounded-xl shadow-sm active:scale-95 transition-all"
            >
              <svg className="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
              </svg>
              Filtrar y Ordenar
              {(filters.categoryId || filters.brandId || filters.onSale || filters.search) && (
                <span className="bg-accent text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold shadow">
                  {[filters.categoryId, filters.brandId, filters.onSale, filters.search].filter(Boolean).length}
                </span>
              )}
            </button>

            {/* Quick Offer Toggle */}
            <button
              type="button"
              onClick={() => setFilters({ onSale: filters.onSale ? undefined : true, page: 1 })}
              className={`px-3 sm:px-4 py-2.5 rounded-xl font-bold text-xs flex items-center gap-1.5 border transition-all ${filters.onSale ? 'bg-red-50 border-red-200 text-red-600 shadow-sm' : 'bg-gray-50 border-gray-200 text-gray-700 hover:bg-gray-100'}`}
            >
              <span className="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
              Ofertas
            </button>
          </div>

          {loading ? (
            <div className="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6">
              {[1, 2, 3, 4, 5, 6, 7, 8].map(i => (
                <div key={i} className="animate-shimmer rounded-2xl aspect-[3/4]"></div>
              ))}
            </div>
          ) : products.length > 0 ? (
            <>
              <div className="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6">
                {products.map(product => (
                  <ProductCard key={product.id} product={product} />
                ))}
              </div>

              {/* Minimalist Numbered Pagination Controls */}
              {pagination.last_page > 1 && (
                <div className="flex justify-center items-center gap-1 sm:gap-1.5 mt-12 sm:mt-16 text-xs sm:text-base select-none">
                  {pagination.current_page > 1 && (
                    <button
                      onClick={() => {
                        setFilters({ page: pagination.current_page - 1 });
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                      }}
                      className="flex items-center gap-1 mr-1 sm:mr-2 px-2.5 sm:px-3 py-1.5 text-muted-foreground hover:text-foreground font-medium transition-colors"
                    >
                      <svg className="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7"></path></svg>
                      Anterior
                    </button>
                  )}

                  {(() => {
                    const pages = [];
                    const maxShown = 8;
                    let start = Math.max(1, pagination.current_page - Math.floor(maxShown / 2));
                    let end = Math.min(pagination.last_page, start + maxShown - 1);
                    if (end - start + 1 < maxShown) {
                      start = Math.max(1, end - maxShown + 1);
                    }
                    for (let i = start; i <= end; i++) pages.push(i);

                    return pages.map((page) => {
                      const isActive = page === pagination.current_page;
                      return (
                        <button
                          key={page}
                          onClick={() => {
                            if (!isActive) {
                              setFilters({ page });
                              window.scrollTo({ top: 0, behavior: 'smooth' });
                            }
                          }}
                          className={`min-w-[34px] sm:min-w-[38px] h-[34px] sm:h-[38px] px-2 sm:px-2.5 rounded-lg flex items-center justify-center font-semibold transition-all ${
                            isActive 
                              ? 'border-2 border-blue-500 text-foreground shadow-sm bg-background scale-105 z-10' 
                              : 'text-muted-foreground hover:text-foreground hover:bg-muted/40'
                          }`}
                        >
                          {page}
                        </button>
                      );
                    });
                  })()}

                  {pagination.current_page < pagination.last_page && (
                    <button
                      onClick={() => {
                        setFilters({ page: pagination.current_page + 1 });
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                      }}
                      className="flex items-center gap-1 ml-1 sm:ml-2 px-2.5 sm:px-3 py-1.5 text-muted-foreground hover:text-foreground font-medium transition-colors"
                    >
                      Siguiente
                      <svg className="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                  )}
                </div>
              )}
            </>
          ) : (
            <div className="text-center py-16 sm:py-24 bg-white rounded-3xl border shadow-[0_2px_10px_rgba(0,0,0,0.02)] ring-1 ring-black/[0.03]">
              <div className="mx-auto w-20 h-20 sm:w-24 sm:h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6 relative">
                <div className="absolute inset-0 border-2 border-gray-100 rounded-full animate-ping opacity-30"></div>
                <svg className="w-8 h-8 sm:w-10 sm:h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
              <h3 className="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">No encontramos resultados</h3>
              <p className="mt-2 text-xs sm:text-sm text-gray-500 max-w-sm mx-auto px-4">Prueba con otros términos de búsqueda o quita los filtros actuales para ver más productos.</p>
              <button 
                onClick={() => setFilters({ search: undefined, categoryId: undefined, brandId: undefined, onSale: undefined, page: 1 })}
                className="mt-6 px-6 py-2.5 bg-gray-900 text-white font-medium text-xs sm:text-sm rounded-xl hover:bg-gray-800 transition-all active:scale-95 duration-200 shadow-sm"
              >
                Limpiar todos los filtros
              </button>
            </div>
          )}
        </main>
      </div>

      {/* Mobile Filter Modal / Drawer */}
      {showMobileFilters && (
        <div className="fixed inset-0 z-50 lg:hidden flex justify-end bg-black/60 backdrop-blur-sm animate-in fade-in duration-200">
          <div className="w-full max-w-md bg-white h-full flex flex-col shadow-2xl animate-in slide-in-from-right duration-300">
            {/* Modal Header */}
            <div className="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
              <div className="flex items-center gap-2">
                <svg className="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                <h3 className="font-black text-base sm:text-lg text-gray-900">Filtros y Categorías</h3>
              </div>
              <button 
                type="button"
                onClick={() => setShowMobileFilters(false)}
                className="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 transition-colors"
              >
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path></svg>
              </button>
            </div>

            {/* Modal Body */}
            <div className="p-5 sm:p-6 overflow-y-auto flex-1 space-y-6">
              {renderFilterContent()}
            </div>

            {/* Modal Footer */}
            <div className="p-4 border-t border-gray-100 bg-gray-50 flex items-center gap-3">
              <button
                type="button"
                onClick={() => {
                  setFilters({ search: undefined, categoryId: undefined, brandId: undefined, onSale: undefined, page: 1 });
                  setShowMobileFilters(false);
                }}
                className="px-4 py-3 rounded-xl font-bold text-xs sm:text-sm text-gray-600 hover:bg-gray-200/60 transition-colors"
              >
                Limpiar todo
              </button>
              <button
                type="button"
                onClick={() => setShowMobileFilters(false)}
                className="flex-1 bg-accent hover:bg-accent/90 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition-transform active:scale-95 text-center text-xs sm:text-sm"
              >
                Ver {products.length} Resultados →
              </button>
            </div>
          </div>
        </div>
      )}
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
