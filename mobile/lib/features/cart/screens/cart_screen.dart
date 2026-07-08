import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../providers/cart_provider.dart';
import 'checkout_screen.dart';

class CartScreen extends ConsumerWidget {
  const CartScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final cartItems = ref.watch(cartProvider);
    final subtotal = ref.watch(cartTotalProvider);
    final isFreeShipping = subtotal >= 150.0 || subtotal == 0;
    final remainingForFree = (150.0 - subtotal).clamp(0.0, 150.0);
    final progress = (subtotal / 150.0).clamp(0.0, 1.0);
    final shippingCost = isFreeShipping ? 0.0 : 15.0;
    final total = subtotal + shippingCost;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC), // Slate 50 matching web
      appBar: AppBar(
        title: const Text('Mi Carrito de Compra', style: TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0F172A))),
        centerTitle: true,
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 0,
        actions: [
          if (cartItems.isNotEmpty)
            TextButton.icon(
              onPressed: () => ref.read(cartProvider.notifier).clearCart(),
              icon: const Icon(Icons.delete_sweep_outlined, color: Color(0xFFEF4444), size: 18),
              label: const Text('Vaciar', style: TextStyle(color: Color(0xFFEF4444), fontWeight: FontWeight.bold, fontSize: 13)),
            )
        ],
      ),
      body: cartItems.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Container(
                    padding: const EdgeInsets.all(28),
                    decoration: BoxDecoration(
                      color: const Color(0xFF059669).withOpacity(0.08),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.shopping_bag_outlined, size: 84, color: Color(0xFF059669)),
                  ),
                  const SizedBox(height: 24),
                  const Text('Tu carrito está vacío', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900, color: Color(0xFF0F172A))),
                  const SizedBox(height: 12),
                  const Text(
                    'Descubre nuestros suplementos certificados \ny añade lo que necesitas para tu bienestar.',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: Color(0xFF64748B), fontSize: 14, height: 1.5),
                  ),
                ],
              ),
            )
          : Stack(
              children: [
                Column(
                  children: [
                    // Web Parity: Free Shipping Progress Bar Banner
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                      decoration: BoxDecoration(
                        color: isFreeShipping ? const Color(0xFFECFDF5) : Colors.white,
                        border: Border(bottom: BorderSide(color: const Color(0xFFE2E8F0), width: 1)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Icon(
                                isFreeShipping ? Icons.local_shipping : Icons.local_shipping_outlined,
                                color: const Color(0xFF059669),
                                size: 20,
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  isFreeShipping
                                      ? '¡Felicidades! Tienes Envío Gratis desbloqueado 🚚🎉'
                                      : '¡Te faltan S/ ${remainingForFree.toStringAsFixed(2)} para Envío Gratis!',
                                  style: TextStyle(
                                    fontWeight: isFreeShipping ? FontWeight.w900 : FontWeight.bold,
                                    fontSize: 13,
                                    color: isFreeShipping ? const Color(0xFF064E3B) : const Color(0xFF0F172A),
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),
                          ClipRRect(
                            borderRadius: BorderRadius.circular(8),
                            child: LinearProgressIndicator(
                              value: progress,
                              minHeight: 8,
                              backgroundColor: const Color(0xFFE2E8F0),
                              valueColor: AlwaysStoppedAnimation<Color>(
                                isFreeShipping ? const Color(0xFF10B981) : const Color(0xFF059669),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Items List
                    Expanded(
                      child: ListView.separated(
                        padding: const EdgeInsets.only(top: 16, bottom: 220, left: 16, right: 16),
                        itemCount: cartItems.length,
                        separatorBuilder: (context, index) => const SizedBox(height: 14),
                        itemBuilder: (context, index) {
                          final item = cartItems[index];
                          final product = item.product;
                          final productId = product['id'] is int ? product['id'] as int : int.tryParse(product['id'].toString()) ?? 0;
                          final priceStr = product['price'].toString();
                          final price = double.tryParse(priceStr) ?? 0.0;
                          final imageUrl = product['primary_image']?['image_url'] ??
                              (product['images'] != null && (product['images'] as List).isNotEmpty ? product['images'][0]['image_url'] : null);

                          return Container(
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(22),
                              border: Border.all(color: const Color(0xFFE2E8F0)),
                              boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 10, offset: const Offset(0, 4))],
                            ),
                            child: Row(
                              children: [
                                // Thumbnail
                                Container(
                                  width: 80,
                                  height: 80,
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFF8FAFC),
                                    borderRadius: BorderRadius.circular(16),
                                  ),
                                  child: imageUrl != null
                                      ? ClipRRect(
                                          borderRadius: BorderRadius.circular(16),
                                          child: Image.network(imageUrl, fit: BoxFit.cover, errorBuilder: (c, e, s) => const Icon(Icons.medication_outlined, color: Color(0xFF94A3B8))),
                                        )
                                      : const Icon(Icons.medication_outlined, size: 36, color: Color(0xFF94A3B8)),
                                ),
                                const SizedBox(width: 14),

                                // Info & Stepper
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        (product['brand']?['name'] ?? 'COMPRA SALUDABLE').toString().toUpperCase(),
                                        style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: Color(0xFF64748B), letterSpacing: 1),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        product['name'] ?? '',
                                        style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 15, color: Color(0xFF0F172A)),
                                        maxLines: 2,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                      const SizedBox(height: 8),
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          Text(
                                            'S/ ${(price * item.quantity).toStringAsFixed(2)}',
                                            style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16, color: Color(0xFF059669)),
                                          ),
                                          // Stepper Buttons
                                          Container(
                                            height: 36,
                                            decoration: BoxDecoration(
                                              color: const Color(0xFFF1F5F9),
                                              borderRadius: BorderRadius.circular(12),
                                              border: Border.all(color: const Color(0xFFE2E8F0)),
                                            ),
                                            child: Row(
                                              mainAxisSize: MainAxisSize.min,
                                              children: [
                                                IconButton(
                                                  padding: EdgeInsets.zero,
                                                  constraints: const BoxConstraints(minWidth: 32, minHeight: 32),
                                                  onPressed: () => ref.read(cartProvider.notifier).updateQuantity(productId, item.quantity - 1),
                                                  icon: const Icon(Icons.remove, size: 16, color: Color(0xFF0F172A)),
                                                ),
                                                Text('${item.quantity}', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: Color(0xFF0F172A))),
                                                IconButton(
                                                  padding: EdgeInsets.zero,
                                                  constraints: const BoxConstraints(minWidth: 32, minHeight: 32),
                                                  onPressed: () => ref.read(cartProvider.notifier).updateQuantity(productId, item.quantity + 1),
                                                  icon: const Icon(Icons.add, size: 16, color: Color(0xFF0F172A)),
                                                ),
                                              ],
                                            ),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),

                                // Delete Button
                                IconButton(
                                  onPressed: () => ref.read(cartProvider.notifier).removeItem(productId),
                                  icon: const Icon(Icons.delete_outline, color: Color(0xFF94A3B8), size: 20),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ),

                // Bottom Sticky Checkout Bar matching web CartSheet
                Positioned(
                  bottom: 0,
                  left: 0,
                  right: 0,
                  child: Container(
                    padding: const EdgeInsets.fromLTRB(20, 18, 20, 28),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: const BorderRadius.vertical(top: Radius.circular(32)),
                      boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 20, offset: const Offset(0, -5))],
                    ),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('Subtotal:', style: TextStyle(fontSize: 14, color: Color(0xFF64748B), fontWeight: FontWeight.w600)),
                            Text('S/ ${subtotal.toStringAsFixed(2)}', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                          ],
                        ),
                        const SizedBox(height: 6),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('Envío estimado:', style: TextStyle(fontSize: 14, color: Color(0xFF64748B), fontWeight: FontWeight.w600)),
                            Text(
                              isFreeShipping ? 'GRATIS 🎉' : 'S/ ${shippingCost.toStringAsFixed(2)}',
                              style: TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.bold,
                                color: isFreeShipping ? const Color(0xFF059669) : const Color(0xFF0F172A),
                              ),
                            ),
                          ],
                        ),
                        const Divider(color: Color(0xFFF1F5F9), height: 24),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('Total a Pagar:', style: TextStyle(fontSize: 17, color: Color(0xFF0F172A), fontWeight: FontWeight.w900)),
                            Text('S/ ${total.toStringAsFixed(2)}', style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w900, color: Color(0xFF059669))),
                          ],
                        ),
                        const SizedBox(height: 18),
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton(
                            onPressed: () {
                              Navigator.push(context, MaterialPageRoute(builder: (_) => const CheckoutScreen()));
                            },
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF059669),
                              padding: const EdgeInsets.symmetric(vertical: 18),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                              elevation: 4,
                              shadowColor: const Color(0xFF059669).withOpacity(0.4),
                            ),
                            child: const Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.lock_outline, color: Colors.white, size: 20),
                                SizedBox(width: 8),
                                Text('Proceder al Pago Seguro', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white)),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
    );
  }
}
