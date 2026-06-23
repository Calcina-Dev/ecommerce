export interface StoreSettings {
  store_name: string;
  whatsapp_number: string;
  facebook_url: string;
  instagram_url: string;
  tiktok_url: string;
  contact_email: string;
  store_address: string;
}

export async function getStoreSettings(): Promise<StoreSettings | null> {
  try {
    const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/storefront/settings`, {
      cache: 'no-store' // Deshabilitamos caché para ver cambios instantáneos
    });
    if (!res.ok) {
      return null;
    }
    return await res.json();
  } catch (error) {
    console.error("Failed to fetch store settings:", error);
    return null;
  }
}
