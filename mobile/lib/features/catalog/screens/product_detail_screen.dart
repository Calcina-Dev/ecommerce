import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../cart/providers/cart_provider.dart';

class ProductDetailScreen extends ConsumerStatefulWidget {
  final Map<String, dynamic> product;

  const ProductDetailScreen({super.key, required this.product});

  @override
  ConsumerState<ProductDetailScreen> createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends ConsumerState<ProductDetailScreen> with SingleTickerProviderStateMixin {
  int _quantity = 1;
  int _selectedImageIndex = 0;
  late AnimationController _pulseController;

  @override
  void initState() {
    super.initState();
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 2),
    )..repeat(reverse: true);
  }

  @override
  void dispose() {
    _pulseController.dispose();
    super.dispose();
  }

  List<String> _getImages() {
    final List<String> images = [];
    String resolve(String url) {
      if (url.isEmpty) return '';
      if (url.startsWith('http')) return url;
      final clean = url.replaceAll(RegExp(r'^/'), '');
      return 'http://127.0.0.1:8000/storage/$clean';
    }
    if (widget.product['primary_image'] != null && widget.product['primary_image']['image_url'] != null) {
      images.add(resolve(widget.product['primary_image']['image_url'] as String));
    }
    if (widget.product['images'] != null && widget.product['images'] is List) {
      for (final img in (widget.product['images'] as List)) {
        if (img is Map && img['image_url'] != null) {
          final url = resolve(img['image_url'] as String);
          if (!images.contains(url)) images.add(url);
        }
      }
    }
    if (images.isEmpty) {
      images.add('https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?q=80&w=800&auto=format&fit=crop');
    }
    return images;
  }

  double _getPrice() {
    final p = widget.product['price'];
    if (p == null) return 0.0;
    return double.tryParse(p.toString()) ?? 0.0;
  }

  double? _getComparePrice() {
    final cp = widget.product['compare_at_price'];
    if (cp == null) return null;
    return double.tryParse(cp.toString());
  }

  @override
  Widget build(BuildContext context) {
    final images = _getImages();
    final price = _getPrice();
    final comparePrice = _getComparePrice();
    final hasOffer = comparePrice != null && comparePrice > price;
    final brandName = (widget.product['brand']?['name'] ?? 'Compra Saludable').toString().toUpperCase();
    final productName = (widget.product['name'] ?? 'Suplemento Nutricional').toString();
    final shortDesc = (widget.product['short_description'] ?? 'Fórmula avanzada de alta biodisponibilidad para un bienestar integral. Desarrollado con estándares farmacéuticos.').toString();
    final fullDesc = (widget.product['description'] ?? shortDesc).toString();

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC), // Slate 50 matching web
      body: Stack(
        children: [
          SingleChildScrollView(
            physics: const BouncingScrollPhysics(),
            padding: const EdgeInsets.only(bottom: 150),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: 100), // Space for top floating bar

                // Image Gallery Container
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  child: Container(
                    width: double.infinity,
                    height: 380,
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(32),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.06),
                          blurRadius: 24,
                          offset: const Offset(0, 12),
                        ),
                      ],
                    ),
                    child: Stack(
                      children: [
                        Positioned.fill(
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(32),
                            child: AnimatedSwitcher(
                              duration: const Duration(milliseconds: 300),
                              child: Image.network(
                                images[_selectedImageIndex % images.length],
                                key: ValueKey(_selectedImageIndex),
                                fit: BoxFit.cover,
                                errorBuilder: (c, e, s) => const Center(child: Icon(Icons.medication_outlined, size: 64, color: Color(0xFF94A3B8))),
                              ),
                            ),
                          ),
                        ),
                        if (hasOffer)
                          Positioned(
                            top: 20,
                            left: 20,
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                              decoration: BoxDecoration(
                                color: const Color(0xFFEF4444), // Red 500
                                borderRadius: BorderRadius.circular(20),
                                boxShadow: [BoxShadow(color: const Color(0xFFEF4444).withOpacity(0.4), blurRadius: 8, offset: const Offset(0, 3))],
                              ),
                              child: const Text(
                                'OFERTA ESPECIAL',
                                style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w900, letterSpacing: 0.5),
                              ),
                            ),
                          ),
                        if (images.length > 1)
                          Positioned(
                            bottom: 16,
                            left: 0,
                            right: 0,
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: List.generate(images.length, (index) {
                                final isSelected = index == _selectedImageIndex;
                                return GestureDetector(
                                  onTap: () => setState(() => _selectedImageIndex = index),
                                  child: AnimatedContainer(
                                    duration: const Duration(milliseconds: 250),
                                    margin: const EdgeInsets.symmetric(horizontal: 4),
                                    width: isSelected ? 24 : 8,
                                    height: 8,
                                    decoration: BoxDecoration(
                                      color: isSelected ? const Color(0xFF059669) : Colors.black.withOpacity(0.2),
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                  ),
                                );
                              }),
                            ),
                          ),
                      ],
                    ),
                  ),
                ),

                const SizedBox(height: 28),

                // Main Details & Title
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: const Color(0xFF059669).withOpacity(0.1),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              brandName,
                              style: const TextStyle(color: Color(0xFF059669), fontSize: 11, fontWeight: FontWeight.w800, letterSpacing: 1.2),
                            ),
                          ),
                          Row(
                            children: [
                              ...List.generate(4, (i) => const Icon(Icons.star, color: Color(0xFFF59E0B), size: 18)),
                              const Icon(Icons.star_half, color: Color(0xFFF59E0B), size: 18),
                              const SizedBox(width: 6),
                              const Text('4.9 (124 reseñas)', style: TextStyle(color: Color(0xFF64748B), fontSize: 13, fontWeight: FontWeight.w600)),
                            ],
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Text(
                        productName,
                        style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 26, color: Color(0xFF0F172A), height: 1.15, letterSpacing: -0.5),
                      ),
                      const SizedBox(height: 16),

                      // Price Row
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(
                            'S/ ${price.toStringAsFixed(2)}',
                            style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 34, color: Color(0xFF0F172A), height: 1),
                          ),
                          if (hasOffer) ...[
                            const SizedBox(width: 12),
                            Padding(
                              padding: const EdgeInsets.only(bottom: 4),
                              child: Text(
                                'S/ ${comparePrice.toStringAsFixed(2)}',
                                style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 20, fontWeight: FontWeight.w600, decoration: TextDecoration.lineThrough),
                              ),
                            ),
                          ],
                        ],
                      ),

                      const SizedBox(height: 24),

                      // AI Clinical Specification Summary Card (Web Parity)
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: [const Color(0xFF064E3B).withOpacity(0.04), const Color(0xFF059669).withOpacity(0.08)],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                          borderRadius: BorderRadius.circular(24),
                          border: Border.all(color: const Color(0xFF059669).withOpacity(0.2)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                AnimatedBuilder(
                                  animation: _pulseController,
                                  builder: (context, child) {
                                    return Container(
                                      width: 12,
                                      height: 12,
                                      decoration: BoxDecoration(
                                        color: const Color(0xFF059669),
                                        shape: BoxShape.circle,
                                        boxShadow: [
                                          BoxShadow(
                                            color: const Color(0xFF10B981).withOpacity(0.6 * _pulseController.value),
                                            blurRadius: 8,
                                            spreadRadius: 3 * _pulseController.value,
                                          ),
                                        ],
                                      ),
                                    );
                                  },
                                ),
                                const SizedBox(width: 10),
                                const Text(
                                  'RESUMEN CLÍNICO AI',
                                  style: TextStyle(color: Color(0xFF064E3B), fontWeight: FontWeight.w900, fontSize: 13, letterSpacing: 0.8),
                                ),
                                const Spacer(),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                  decoration: BoxDecoration(color: const Color(0xFF059669), borderRadius: BorderRadius.circular(6)),
                                  child: const Text('SINTETIZADO', style: TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold)),
                                ),
                              ],
                            ),
                            const SizedBox(height: 14),
                            Text(
                              shortDesc,
                              style: const TextStyle(color: Color(0xFF1E293B), fontSize: 14, height: 1.5, fontWeight: FontWeight.w500),
                            ),
                            const SizedBox(height: 12),
                            Row(
                              children: [
                                const Icon(Icons.verified_user_outlined, size: 16, color: Color(0xFF059669)),
                                const SizedBox(width: 6),
                                const Text('Alta biodisponibilidad y grado médico', style: TextStyle(color: Color(0xFF064E3B), fontSize: 12, fontWeight: FontWeight.w600)),
                              ],
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 28),
                      const Text('Descripción Detallada', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Color(0xFF0F172A))),
                      const SizedBox(height: 12),
                      Text(
                        fullDesc,
                        style: const TextStyle(fontSize: 15, color: Color(0xFF475569), height: 1.6),
                      ),

                      const SizedBox(height: 32),

                      // Tabs / Benefits Accordions
                      _buildAccordionItem(
                        icon: Icons.science_outlined,
                        title: 'Ficha Clínica & Ingredientes',
                        content: 'Suplemento formulado con materias primas estandarizadas de alta purificación. No contiene OGM, gluten ni colorantes artificiales. Apto para dietas exigentes y absorción celular optimizada.',
                      ),
                      const SizedBox(height: 12),
                      _buildAccordionItem(
                        icon: Icons.schedule_outlined,
                        title: 'Modo de Uso Recomendado',
                        content: 'Tomar de 1 a 2 porciones al día con abundante agua, preferentemente junto a las comidas principales o según indicación de tu profesional de salud.',
                      ),
                      const SizedBox(height: 12),
                      _buildAccordionItem(
                        icon: Icons.local_shipping_outlined,
                        title: 'Garantía de Envío & Devolución',
                        content: 'Envío prioritario con cadena de temperatura controlada. Garantía de satisfacción total de 30 días con reemplazo inmediato ante cualquier inconveniente logístico.',
                      ),
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
                      onTap: () => Navigator.pop(context),
                      child: Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(color: Colors.white, shape: BoxShape.circle, boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 12, offset: const Offset(0, 4))]),
                        child: const Icon(Icons.arrow_back_ios_new, size: 18, color: Color(0xFF0F172A)),
                      ),
                    ),
                    const Text('Detalle de Producto', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Color(0xFF0F172A))),
                    GestureDetector(
                      onTap: () {
                        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Guardado en tu lista de favoritos'), backgroundColor: Color(0xFF059669), duration: Duration(seconds: 1)));
                      },
                      child: Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(color: Colors.white, shape: BoxShape.circle, boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 12, offset: const Offset(0, 4))]),
                        child: const Icon(Icons.favorite_border, size: 20, color: Color(0xFFEF4444)),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),

          // Floating WhatsApp Assistance Button (Web Parity)
          Positioned(
            bottom: 110,
            right: 20,
            child: FloatingActionButton.extended(
              onPressed: () {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text('Abriendo asesoría por WhatsApp para "$productName"...'),
                    backgroundColor: const Color(0xFF25D366),
                    duration: const Duration(seconds: 2),
                  ),
                );
              },
              backgroundColor: const Color(0xFF25D366),
              icon: const Icon(Icons.chat_bubble, color: Colors.white, size: 20),
              label: const Text('Consultar Asesor', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13)),
            ),
          ),

          // Bottom CTA (Quantity Stepper + Add to Cart)
          Positioned(
            bottom: 0,
            left: 0,
            right: 0,
            child: Container(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 28),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
                boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 20, offset: const Offset(0, -5))],
              ),
              child: Row(
                children: [
                  // Quantity Stepper
                  Container(
                    height: 56,
                    padding: const EdgeInsets.symmetric(horizontal: 6),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Row(
                      children: [
                        IconButton(
                          onPressed: _quantity > 1 ? () => setState(() => _quantity--) : null,
                          icon: const Icon(Icons.remove, size: 18),
                          color: const Color(0xFF0F172A),
                        ),
                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 6),
                          child: Text('$_quantity', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Color(0xFF0F172A))),
                        ),
                        IconButton(
                          onPressed: () => setState(() => _quantity++),
                          icon: const Icon(Icons.add, size: 18),
                          color: const Color(0xFF0F172A),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 14),

                  // Add to Cart Button
                  Expanded(
                    child: SizedBox(
                      height: 56,
                      child: ElevatedButton(
                        onPressed: () {
                          ref.read(cartProvider.notifier).addItem(widget.product, quantity: _quantity);
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Row(
                                children: [
                                  const Icon(Icons.check_circle, color: Colors.white),
                                  const SizedBox(width: 8),
                                  Expanded(child: Text('¡$_quantity und. de "$productName" agregadas!')),
                                ],
                              ),
                              backgroundColor: const Color(0xFF059669),
                              behavior: SnackBarBehavior.floating,
                              margin: const EdgeInsets.all(16),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                              duration: const Duration(seconds: 2),
                            ),
                          );
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF059669), // Emerald 600
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                          elevation: 4,
                          shadowColor: const Color(0xFF059669).withOpacity(0.4),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.shopping_bag_outlined, color: Colors.white, size: 22),
                            const SizedBox(width: 8),
                            Text(
                              'S/ ${(price * _quantity).toStringAsFixed(2)}',
                              style: const TextStyle(color: Colors.white, fontSize: 17, fontWeight: FontWeight.w800),
                            ),
                          ],
                        ),
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

  Widget _buildAccordionItem({required IconData icon, required String title, required String content}) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Theme(
        data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
        child: ExpansionTile(
          leading: Icon(icon, color: const Color(0xFF059669)),
          title: Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A))),
          childrenPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
          children: [
            Text(content, style: const TextStyle(color: Color(0xFF64748B), fontSize: 13, height: 1.5)),
          ],
        ),
      ),
    );
  }
}
