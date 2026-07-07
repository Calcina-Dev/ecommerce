import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { useAuthStore } from './useAuthStore';

export interface UserAddressItem {
  id: number;
  alias: string;
  recipient_name: string;
  phone: string;
  department: string;
  province: string;
  district: string;
  address: string;
  postal_code: string;
  reference?: string;
  is_default: boolean;
}

interface AddressState {
  savedAddresses: UserAddressItem[];
  selectedAddress: UserAddressItem | null;
  fetchAddresses: () => Promise<void>;
  setSelectedAddress: (addr: UserAddressItem | null) => void;
  setDefaultAddress: (id: number) => Promise<void>;
}

export const useAddressStore = create<AddressState>()(
  persist(
    (set, get) => ({
      savedAddresses: [],
      selectedAddress: null,
      setSelectedAddress: (addr) => {
        set({ selectedAddress: addr });
      },
      fetchAddresses: async () => {
        const token = useAuthStore.getState().token;
        if (!token) {
          set({ savedAddresses: [] });
          return;
        }
        try {
          const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/user/addresses`, {
            headers: {
              'Authorization': `Bearer ${token}`,
              'Accept': 'application/json',
            },
          });
          if (res.ok) {
            const data = await res.json();
            if (Array.isArray(data)) {
              set({ savedAddresses: data });
              // Si no hay selectedAddress o la que estaba ya no existe, elegir la default o la primera
              const currentSel = get().selectedAddress;
              const exists = currentSel ? data.find(a => Number(a.id) === Number(currentSel.id)) : null;
              if (!exists && data.length > 0) {
                const def = data.find(a => a.is_default) || data[0];
                set({ selectedAddress: def });
              } else if (exists) {
                set({ selectedAddress: exists });
              }
            }
          }
        } catch (err) {
          console.error("Error fetching addresses in useAddressStore", err);
        }
      },
      setDefaultAddress: async (id) => {
        const token = useAuthStore.getState().token;
        if (!token) return;
        try {
          const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/user/addresses/${id}/default`, {
            method: 'PATCH',
            headers: {
              'Authorization': `Bearer ${token}`,
              'Accept': 'application/json',
            },
          });
          if (res.ok) {
            await get().fetchAddresses();
          }
        } catch (err) {
          console.error("Error setting default address", err);
        }
      }
    }),
    {
      name: 'address-storage',
    }
  )
);
