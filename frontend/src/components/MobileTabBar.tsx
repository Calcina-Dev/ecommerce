"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { Home, Search, ShoppingBag, User } from "lucide-react";
import { useCartStore } from "@/store/useCartStore";
import { motion } from "framer-motion";

export function MobileTabBar() {
  const pathname = usePathname();
  const cartItems = useCartStore((state) => state.items);
  const cartCount = cartItems.reduce((acc, item) => acc + item.quantity, 0);
  const setIsOpen = useCartStore((state) => state.setIsOpen);

  const tabs = [
    { name: "Inicio", href: "/", icon: Home },
    { name: "Catálogo", href: "/productos", icon: Search },
    { name: "Carrito", href: "/checkout", icon: ShoppingBag, badge: cartCount },
    { name: "Cuenta", href: "/mi-cuenta", icon: User },
  ];

  return (
    <div className="fixed bottom-0 left-0 right-0 z-50 md:hidden px-4 pb-4 pt-2 bg-gradient-to-t from-background via-background to-transparent pointer-events-none [transform:translateZ(0)]">
      <div className="flex items-center justify-around bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-gray-200/50 dark:border-zinc-800/80 rounded-3xl p-2 shadow-[0_8px_30px_rgb(0,0,0,0.12)] pointer-events-auto">
        {tabs.map((tab) => {
          const isActive = pathname === tab.href || (pathname.startsWith(tab.href) && tab.href !== "/" && tab.name !== "Carrito");
          const Icon = tab.icon;

          if (tab.name === "Carrito") {
            return (
              <button
                key={tab.name}
                type="button"
                onClick={() => setIsOpen(true)}
                className="relative flex-1 flex flex-col items-center justify-center p-2 rounded-2xl transition-colors cursor-pointer"
              >
                <motion.div
                  whileTap={{ scale: 0.9 }}
                  className="relative z-10 flex flex-col items-center justify-center gap-1 text-gray-400 hover:text-gray-600 dark:text-zinc-400 dark:hover:text-zinc-200"
                >
                  <div className="relative">
                    <Icon className="w-6 h-6" strokeWidth={2} />
                    {tab.badge !== undefined && tab.badge > 0 && (
                      <span className="absolute -top-1.5 -right-1.5 bg-accent text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full ring-2 ring-white dark:ring-zinc-900">
                        {tab.badge}
                      </span>
                    )}
                  </div>
                  <span className="text-[10px] font-medium">{tab.name}</span>
                </motion.div>
              </button>
            );
          }

          return (
            <Link key={tab.name} href={tab.href} className="relative flex-1 flex flex-col items-center justify-center p-2 rounded-2xl transition-colors">
              <motion.div
                whileTap={{ scale: 0.9 }}
                className={`relative z-10 flex flex-col items-center justify-center gap-1 ${isActive ? "text-emerald-600 dark:text-emerald-400" : "text-gray-400 hover:text-gray-600 dark:text-zinc-400 dark:hover:text-zinc-200"}`}
              >
                <div className="relative">
                  <Icon className="w-6 h-6" strokeWidth={isActive ? 2.5 : 2} />
                  {tab.badge !== undefined && tab.badge > 0 && (
                    <span className="absolute -top-1.5 -right-1.5 bg-accent text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full ring-2 ring-white dark:ring-zinc-900">
                      {tab.badge}
                    </span>
                  )}
                </div>
                <span className="text-[10px] font-medium">{tab.name}</span>
              </motion.div>
              
              {isActive && (
                <motion.div
                  layoutId="mobile-tab-indicator"
                  className="absolute inset-0 bg-emerald-50 dark:bg-emerald-950/40 rounded-2xl z-0"
                  transition={{ type: "spring", stiffness: 400, damping: 25 }}
                />
              )}
            </Link>
          );
        })}
      </div>
    </div>
  );
}
