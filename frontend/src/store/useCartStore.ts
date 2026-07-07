import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export interface CartItem {
  id: number;
  name: string;
  price: string;
  image_url: string | null;
  quantity: number;
  slug: string;
}

export interface StockWarning {
  stock: number;
  priceChanged: boolean;
  newPrice: string;
  available: boolean;
}

interface CartState {
  items: CartItem[];
  isOpen: boolean;
  stockWarnings: Record<number, StockWarning>;
  addItem: (item: CartItem) => void;
  removeItem: (id: number) => void;
  updateQuantity: (id: number, quantity: number) => void;
  clearCart: () => void;
  setIsOpen: (isOpen: boolean) => void;
  totalItems: () => number;
  totalPrice: () => number;
  validateCart: () => Promise<boolean>;
  clearStockWarnings: () => void;
}

export const useCartStore = create<CartState>()(
  persist(
    (set, get) => ({
      items: [],
      isOpen: false,
      stockWarnings: {},
      addItem: (item) => {
        set((state) => {
          const existingItem = state.items.find((i) => i.id === item.id);
          if (existingItem) {
            return {
              items: state.items.map((i) =>
                i.id === item.id
                  ? { ...i, quantity: i.quantity + item.quantity }
                  : i
              ),
              isOpen: true,
            };
          }
          return { items: [...state.items, item], isOpen: true };
        });
      },
      removeItem: (id) => {
        set((state) => {
          const newWarnings = { ...state.stockWarnings };
          delete newWarnings[id];
          return {
            items: state.items.filter((item) => item.id !== id),
            stockWarnings: newWarnings,
          };
        });
      },
      updateQuantity: (id, quantity) => {
        set((state) => ({
          items: state.items.map((item) =>
            item.id === id ? { ...item, quantity: Math.max(1, quantity) } : item
          ),
        }));
      },
      clearCart: () => set({ items: [], stockWarnings: {} }),
      setIsOpen: (isOpen) => set({ isOpen }),
      totalItems: () => {
        return get().items.reduce((total, item) => total + item.quantity, 0);
      },
      totalPrice: () => {
        return get().items.reduce(
          (total, item) => total + parseFloat(item.price) * item.quantity,
          0
        );
      },
      validateCart: async () => {
        const items = get().items;
        if (items.length === 0) {
          set({ stockWarnings: {} });
          return true;
        }
        try {
          const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';
          const res = await fetch(`${apiUrl}/api/catalog/check-stock`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_ids: items.map((i) => i.id) }),
          });
          if (!res.ok) return true;
          const data = await res.json();

          const newWarnings: Record<number, StockWarning> = {};
          let allOk = true;

          set((state) => {
            const updatedItems = state.items.map((item) => {
              const info = data.find((d: any) => d.id === item.id);
              if (!info || !info.available) {
                newWarnings[item.id] = {
                  stock: info ? info.stock : 0,
                  priceChanged: false,
                  newPrice: item.price,
                  available: false,
                };
                allOk = false;
                return item;
              }
              const priceChanged = Math.abs(parseFloat(info.price) - parseFloat(item.price)) > 0.01;
              if (info.stock < item.quantity || priceChanged) {
                newWarnings[item.id] = {
                  stock: info.stock,
                  priceChanged,
                  newPrice: info.price,
                  available: true,
                };
                allOk = false;
              }
              return priceChanged ? { ...item, price: info.price } : item;
            });
            return { items: updatedItems, stockWarnings: newWarnings };
          });
          return allOk;
        } catch (e) {
          console.error('Error validating cart stock:', e);
          return true;
        }
      },
      clearStockWarnings: () => set({ stockWarnings: {} }),
    }),
    {
      name: 'cart-storage',
      partialize: (state) => ({ items: state.items }),
    }
  )
);
