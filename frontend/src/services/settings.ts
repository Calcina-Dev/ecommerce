export interface StoreSettings {
  store_name: string;
  whatsapp_number: string;
  facebook_url: string;
  instagram_url: string;
  tiktok_url: string;
  contact_email: string;
  store_address: string;
  footer_theme?: string;
  footer_columns?: { title: string; links: { label: string; url: string; }[] }[];
}

export async function getStoreSettings(): Promise<StoreSettings | null> {
  try {
    const res = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}/api/storefront/settings`, {
      next: { revalidate: 60 } // ISR: Caché inteligente con revalidación cada 60 segundos
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
