import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../cart/providers/cart_provider.dart';

class ProductDetailScreen extends ConsumerWidget {
  final Map<String, dynamic> product;

  const ProductDetailScreen({super.key, required this.product});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final hasOffer = product['compare_at_price'] != null;

    return Scaffold(
      backgroundColor: Colors.white,
      body: Stack(
        children: [
          SingleChildScrollView(
            physics: const BouncingScrollPhysics(),
            padding: const EdgeInsets.only(bottom: 120),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: 100), // Space for top buttons
                
                // Image
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  child: Container(
                    width: double.infinity,
                    height: 400,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(32),
                      boxShadow: [
                        BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 24, offset: const Offset(0, 12)),
                      ],
                    ),
                    child: product['primary_image'] != null
                        ? ClipRRect(
                            borderRadius: BorderRadius.circular(32),
                            child: Image.network(product['primary_image']['image_url'], fit: BoxFit.cover),
                          )
                        : const Icon(Icons.image, size: 64, color: Colors.grey),
                  ),
                ),
                
                const SizedBox(height: 32),
                
                // Details
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        (product['brand']?['name'] ?? '').toUpperCase(),
                        style: const TextStyle(color: Color(0xFF64748B), fontSize: 12, fontWeight: FontWeight.w800, letterSpacing: 1.5),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        product['name'] ?? '',
                        style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 28, color: Color(0xFF0F172A), height: 1.1, letterSpacing: -0.5),
                      ),
                      const SizedBox(height: 16),
                      
                      // Reviews
                      Row(
                        children: [
                          ...List.generate(4, (i) => const Icon(Icons.star, color: Colors.amber, size: 20)),
                          const Icon(Icons.star, color: Colors.grey, size: 20),
                          const SizedBox(width: 8),
                          const Text('4.8 (124 reseñas)', style: TextStyle(color: Color(0xFF64748B), fontSize: 14, decoration: TextDecoration.underline, decorationStyle: TextDecorationStyle.dashed)),
                        ],
                      ),
                      
                      const SizedBox(height: 24),
                      
                      // Price
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text('S/ ${product['price']}', style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 36, color: Color(0xFF0F172A))),
                          if (hasOffer) ...[
                            const SizedBox(width: 12),
                            Padding(
                              padding: const EdgeInsets.only(bottom: 6),
                              child: Text('S/ ${product['compare_at_price']}', style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 20, decoration: TextDecoration.lineThrough)),
                            ),
                          ]
                        ],
                      ),
                      
                      const SizedBox(height: 32),
                      const Divider(color: Color(0xFFF1F5F9), height: 1),
                      const SizedBox(height: 32),
                      
                      // Description
                      Text(product['short_description'] ?? '', style: const TextStyle(fontSize: 18, color: Color(0xFF475569), height: 1.5, fontWeight: FontWeight.w500)),
                      const SizedBox(height: 16),
                      Text(product['description'] ?? '', style: const TextStyle(fontSize: 15, color: Color(0xFF64748B), height: 1.6)),
                    ],
                  ),
                ),
              ],
            ),
          ),
          
          // Top Bar (Floating)
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            child: SafeArea(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    GestureDetector(
                      onTap: () => Navigator.pop(context), // Using Navigator since OpenContainer manages its own route
                      child: Container(
                        width: 48, height: 48,
                        decoration: BoxDecoration(color: Colors.white, shape: BoxShape.circle, border: Border.all(color: Colors.grey.shade200)),
                        child: const Icon(Icons.arrow_back_ios_new, size: 20, color: Color(0xFF0F172A)),
                      ),
                    ),
                    const Text('Detalles', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                    Container(
                      width: 48, height: 48,
                      decoration: BoxDecoration(color: Colors.white, shape: BoxShape.circle, border: Border.all(color: Colors.grey.shade200)),
                      child: const Icon(Icons.favorite_border, size: 20, color: Color(0xFF0F172A)),
                    ),
                  ],
                ),
              ),
            ),
          ),
          
          // Bottom CTA (Floating)
          Positioned(
            bottom: 0,
            left: 0,
            right: 0,
            child: Container(
              padding: const EdgeInsets.fromLTRB(24, 24, 24, 32),
              decoration: BoxDecoration(
                color: Colors.white,
                gradient: LinearGradient(
                  begin: Alignment.bottomCenter,
                  end: Alignment.topCenter,
                  colors: [Colors.white, Colors.white.withOpacity(0.9), Colors.white.withOpacity(0.0)],
                  stops: const [0.0, 0.7, 1.0],
                ),
              ),
              child: ElevatedButton(
                onPressed: () {
                  ref.read(cartProvider.notifier).addItem(product);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Row(
                        children: [
                          const Icon(Icons.check_circle, color: Colors.white),
                          const SizedBox(width: 8),
                          Expanded(child: Text('${product['name']} agregado al carrito')),
                        ],
                      ),
                      backgroundColor: const Color(0xFF059669),
                      behavior: SnackBarBehavior.floating,
                      margin: const EdgeInsets.all(16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      duration: const Duration(seconds: 2),
                    ),
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF0F172A),
                  padding: const EdgeInsets.symmetric(vertical: 20),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                  elevation: 10,
                  shadowColor: const Color(0xFF0F172A).withOpacity(0.3),
                ),
                child: const Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.shopping_bag_outlined, color: Colors.white, size: 24),
                    SizedBox(width: 12),
                    Text('Agregar al Carrito', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
