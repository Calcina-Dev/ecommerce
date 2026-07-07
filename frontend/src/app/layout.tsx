import type { Metadata } from "next";
import { Poppins } from "next/font/google";
import { Header } from "@/components/Header";
import { CartSheet } from "@/components/CartSheet";
import { Footer } from "@/components/Footer";
import { WhatsAppButton } from "@/components/WhatsAppButton";
import "./globals.css";

const poppins = Poppins({
  variable: "--font-poppins",
  subsets: ["latin"],
  weight: ["300", "400", "500", "600", "700", "800"],
});

export const metadata: Metadata = {
  title: "Compra Saludable | Tu salud en buenas manos",
  description: "Vitaminas y suplementos para tu bienestar.",
};

import { Toaster } from 'sonner';
import { MobileTabBar } from "@/components/MobileTabBar";
import { getStoreSettings } from "@/services/settings";

export default async function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  const settings = await getStoreSettings();

  return (
    <html
      lang="es"
      className="h-full antialiased overflow-x-hidden max-w-full"
    >
      <body className={`${poppins.className} min-h-full flex flex-col bg-background overflow-x-hidden max-w-full`}>
        <Header />
        <div vaul-drawer-wrapper="" className="flex-1 flex flex-col bg-background pb-20 md:pb-0 overflow-x-hidden w-full max-w-full">
          <CartSheet />
          {children}
          <WhatsAppButton settings={settings} />
          <Footer settings={settings} />
        </div>
        <MobileTabBar />
        <Toaster position="bottom-center" closeButton duration={2000} toastOptions={{ style: { borderRadius: '12px' } }} richColors />
      </body>
    </html>
  );
}
