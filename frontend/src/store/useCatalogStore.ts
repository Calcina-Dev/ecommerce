import { create } from 'zustand'

interface CatalogFilters {
  categoryId?: number;
  brandId?: number;
  minPrice?: number;
  maxPrice?: number;
  search?: string;
  page?: number;
  onSale?: boolean;
  sortBy?: string;
}

interface CatalogState {
  filters: CatalogFilters;
  filterData: any;
  setFilters: (filters: Partial<CatalogFilters>) => void;
  setFilterData: (data: any) => void;
  resetFilters: () => void;
}

export const useCatalogStore = create<CatalogState>((set) => ({
  filters: {},
  filterData: null,
  setFilters: (newFilters) => set((state) => ({ 
    filters: { ...state.filters, ...newFilters } 
  })),
  setFilterData: (data) => set({ filterData: data }),
  resetFilters: () => set({ filters: {} }),
}))
