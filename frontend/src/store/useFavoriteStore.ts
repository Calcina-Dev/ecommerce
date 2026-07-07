import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { useAuthStore } from './useAuthStore';

export interface FavoriteItem {
  id: number;
  name: string;
  price: string;
  image_url: string | null;
  slug: string;
}

interface FavoriteState {
  items: FavoriteItem[];
  toggleItem: (item: FavoriteItem) => Promise<boolean>; // returns true if added, false if removed
  removeItem: (id: number) => Promise<void>;
  isFavorite: (id: number) => boolean;
  syncWithBackend: () => Promise<void>;
}

export const useFavoriteStore = create<FavoriteState>()(
  persist(
    (set, get) => ({
      items: [],
      isFavorite: (id) => {
        return get().items.some((item) => Number(item.id) === Number(id));
      },
      toggleItem: async (item) => {
        const state = get();
        const exists = state.items.some((i) => Number(i.id) === Number(item.id));
        const token = useAuthStore.getState().token;

        if (exists) {
          set({ items: state.items.filter((i) => Number(i.id) !== Number(item.id)) });
          if (token) {
            try {
              await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/favorites/toggle/${item.id}`, {
                method: 'POST',
                headers: {
                  'Authorization': `Bearer ${token}`,
                  'Accept': 'application/json',
                },
              });
            } catch (err) {
              console.error("Error syncing favorite removal", err);
            }
          }
          return false;
        } else {
          set({ items: [...state.items, item] });
          if (token) {
            try {
              await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/favorites/toggle/${item.id}`, {
                method: 'POST',
                headers: {
                  'Authorization': `Bearer ${token}`,
                  'Accept': 'application/json',
                },
              });
            } catch (err) {
              console.error("Error syncing favorite addition", err);
            }
          }
          return true;
        }
      },
      removeItem: async (id) => {
        set((state) => ({
          items: state.items.filter((item) => Number(item.id) !== Number(id)),
        }));
        const token = useAuthStore.getState().token;
        if (token) {
          try {
            await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/favorites/toggle/${id}`, {
              method: 'POST',
              headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
              },
            });
          } catch (err) {
            console.error("Error syncing favorite removal", err);
          }
        }
      },
      syncWithBackend: async () => {
        const token = useAuthStore.getState().token;
        if (!token) return;

        const currentIds = get().items.map((i) => i.id);
        try {
          const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/favorites/sync`, {
            method: 'POST',
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
            body: JSON.stringify({ product_ids: currentIds }),
          });
          if (res.ok) {
            const data = await res.json();
            if (data.favorites && Array.isArray(data.favorites)) {
              const syncedItems: FavoriteItem[] = data.favorites.map((fav: any) => ({
                id: fav.product.id,
                name: fav.product.name,
                price: fav.product.price,
                image_url: fav.product.primary_image?.image_url || fav.product.images?.[0]?.image_url || null,
                slug: fav.product.slug,
              }));
              set({ items: syncedItems });
            }
          }
        } catch (err) {
          console.error("Error syncing favorites with backend", err);
        }
      },
    }),
    {
      name: 'favorites-storage',
    }
  )
);
