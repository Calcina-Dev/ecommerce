import { create } from 'zustand'

interface CatalogFilters {
  categoryId?: number;
  brandId?: number;
  minPrice?: number;
  maxPrice?: number;
  search?: string;
  page?: number;
  onSale?: boolean;
}

interface CatalogState {
  filters: CatalogFilters;
  setFilters: (filters: Partial<CatalogFilters>) => void;
  resetFilters: () => void;
}

export const useCatalogStore = create<CatalogState>((set) => ({
  filters: {},
  setFilters: (newFilters) => set((state) => ({ 
    filters: { ...state.filters, ...newFilters } 
  })),
  resetFilters: () => set({ filters: {} }),
}))
