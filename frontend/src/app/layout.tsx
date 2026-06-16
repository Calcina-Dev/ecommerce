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

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html
      lang="es"
      className="h-full antialiased"
    >
      <body className={`${poppins.className} min-h-full flex flex-col bg-background`}>
        <Header />
        <div vaul-drawer-wrapper="" className="flex-1 flex flex-col bg-background pt-16 pb-16 md:pb-0">
          <CartSheet />
          {children}
          <WhatsAppButton />
          <Footer />
        </div>
        <MobileTabBar />
        <Toaster position="bottom-center" toastOptions={{ style: { borderRadius: '12px' } }} richColors />
      </body>
    </html>
  );
}
