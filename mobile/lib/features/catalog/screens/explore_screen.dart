import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:animations/animations.dart';
import '../providers/catalog_provider.dart';
import 'product_detail_screen.dart';
import '../../cart/providers/cart_provider.dart';

class ExploreScreen extends ConsumerStatefulWidget {
  const ExploreScreen({super.key});

  @override
  ConsumerState<ExploreScreen> createState() => _ExploreScreenState();
}

class _ExploreScreenState extends ConsumerState<ExploreScreen> {
  String selectedCategory = 'Todas';
  String selectedBrand = 'Todas';
  bool onlyOffers = false;

  void _openFilterBottomSheet(List<String> brands) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (ctx) => _FilterBottomSheet(
        brands: brands,
        initialBrand: selectedBrand,
        initialOffers: onlyOffers,
        onApply: (brand, offers) {
          setState(() {
            selectedBrand = brand;
            onlyOffers = offers;
          });
          Navigator.pop(ctx);
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final productsAsync = ref.watch(productsProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC), // Slate 50
      body: productsAsync.when(
        data: (products) {
          final categories = ['Todas', ...products.map((p) => p['category']?['name'] ?? 'Otros').toSet().cast<String>()];
          final brands = ['Todas', ...products.map((p) => p['brand']?['name'] ?? 'Otros').toSet().cast<String>()];

          final filteredProducts = products.where((p) {
            if (selectedCategory != 'Todas' && p['category']?['name'] != selectedCategory) return false;
            if (selectedBrand != 'Todas' && p['brand']?['name'] != selectedBrand) return false;
            if (onlyOffers && p['compare_at_price'] == null) return false;
            return true;
          }).toList();

          return CustomScrollView(
            physics: const BouncingScrollPhysics(),
            slivers: [
              // Dynamic Header
              // Dynamic Header matching web Header.tsx parity
              SliverAppBar(
                expandedHeight: 135.0,
                floating: true,
                pinned: true,
                elevation: 0,
                backgroundColor: Colors.white,
                surfaceTintColor: Colors.white,
                title: RichText(
                  text: const TextSpan(
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900, letterSpacing: -0.5),
                    children: [
                      TextSpan(text: 'COMPRA', style: TextStyle(color: Color(0xFF0F172A))),
                      TextSpan(text: 'SALUDABLE', style: TextStyle(color: Color(0xFF059669))),
                    ],
                  ),
                ),
                centerTitle: false,
                actions: [
                  IconButton(
                    icon: const Icon(Icons.person_outline, color: Color(0xFF475569), size: 22),
                    onPressed: () {},
                  ),
                  Consumer(
                    builder: (context, ref, _) {
                      final cartCount = ref.watch(cartCountProvider);
                      return Stack(
                        clipBehavior: Clip.none,
                        children: [
                          IconButton(
                            icon: const Icon(Icons.shopping_bag_outlined, color: Color(0xFF475569), size: 22),
                            onPressed: () {},
                          ),
                          if (cartCount > 0)
                            Positioned(
                              top: 8,
                              right: 8,
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                                decoration: const BoxDecoration(color: Color(0xFF059669), shape: BoxShape.circle),
                                child: Text('$cartCount', style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold)),
                              ),
                            ),
                        ],
                      );
                    },
                  ),
                  const SizedBox(width: 6),
                ],
                bottom: PreferredSize(
                  preferredSize: const Size.fromHeight(75),
                  child: Container(
                    padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
                    color: Colors.white,
                    child: Row(
                      children: [
                        Expanded(
                          child: Container(
                            height: 48,
                            padding: const EdgeInsets.symmetric(horizontal: 16),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF1F5F9), // Slate 100
                              borderRadius: BorderRadius.circular(16),
                            ),
                            child: Row(
                              children: const [
                                Icon(Icons.search, color: Color(0xFF94A3B8), size: 20),
                                SizedBox(width: 12),
                                Expanded(child: Text('Buscar suplementos...', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 14))),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        GestureDetector(
                          onTap: () => _openFilterBottomSheet(brands),
                          child: Container(
                            height: 48,
                            width: 48,
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(colors: [Color(0xFF10B981), Color(0xFF059669)]),
                              borderRadius: BorderRadius.circular(16),
                              boxShadow: [
                                BoxShadow(color: const Color(0xFF10B981).withOpacity(0.3), blurRadius: 12, offset: const Offset(0, 4)),
                              ],
                            ),
                            child: Stack(
                              alignment: Alignment.center,
                              children: [
                                const Icon(Icons.tune, color: Colors.white, size: 20),
                                if (selectedBrand != 'Todas' || onlyOffers)
                                  Positioned(
                                    top: 12, right: 12,
                                    child: Container(width: 6, height: 6, decoration: const BoxDecoration(color: Colors.redAccent, shape: BoxShape.circle)),
                                  ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),

              // Categories Chips
              SliverToBoxAdapter(
                child: Container(
                  height: 70,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  color: const Color(0xFFF8FAFC),
                  child: ListView.builder(
                    scrollDirection: Axis.horizontal,
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    itemCount: categories.length,
                    itemBuilder: (context, index) {
                      final cat = categories[index];
                      final isSelected = cat == selectedCategory;
                      return GestureDetector(
                        onTap: () => setState(() => selectedCategory = cat),
                        child: AnimatedContainer(
                          duration: const Duration(milliseconds: 300),
                          margin: const EdgeInsets.only(right: 12),
                          padding: const EdgeInsets.symmetric(horizontal: 20),
                          alignment: Alignment.center,
                          decoration: BoxDecoration(
                            color: isSelected ? const Color(0xFF0F172A) : Colors.white,
                            borderRadius: BorderRadius.circular(24),
                            boxShadow: isSelected ? [BoxShadow(color: const Color(0xFF0F172A).withOpacity(0.2), blurRadius: 8, offset: const Offset(0, 4))] : [BoxShadow(color: Colors.black.withOpacity(0.03), blurRadius: 4, offset: const Offset(0, 2))],
                            border: Border.all(color: isSelected ? const Color(0xFF0F172A) : Colors.grey.shade200),
                          ),
                          child: Text(
                            cat,
                            style: TextStyle(
                              color: isSelected ? Colors.white : const Color(0xFF64748B),
                              fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                              fontSize: 14,
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                ),
              ),

              // Products Grid
              if (filteredProducts.isEmpty)
                const SliverFillRemaining(
                  child: Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.search_off, size: 64, color: Color(0xFFCBD5E1)),
                        SizedBox(height: 16),
                        Text('No encontramos productos.', style: TextStyle(color: Color(0xFF64748B), fontSize: 16)),
                      ],
                    ),
                  ),
                )
              else
                SliverPadding(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 120),
                  sliver: SliverGrid(
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      childAspectRatio: 0.58,
                      mainAxisSpacing: 16,
                      crossAxisSpacing: 16,
                    ),
                    delegate: SliverChildBuilderDelegate(
                      (context, index) => _PremiumProductCard(product: filteredProducts[index]),
                      childCount: filteredProducts.length,
                    ),
                  ),
                ),
            ],
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, stack) => Center(child: Text('Error: $err')),
      ),
    );
  }
}

class _PremiumProductCard extends ConsumerWidget {
  final Map<String, dynamic> product;

  const _PremiumProductCard({required this.product});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final hasOffer = product['compare_at_price'] != null;

    return OpenContainer(
      closedElevation: 0,
      openElevation: 0,
      closedColor: Colors.white,
      openColor: Colors.white,
      middleColor: Colors.white,
      closedShape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
      transitionDuration: const Duration(milliseconds: 350),
      transitionType: ContainerTransitionType.fade,
      openBuilder: (context, action) => ProductDetailScreen(product: product),
      closedBuilder: (context, action) => Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(24),
          boxShadow: [
            BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 20, offset: const Offset(0, 10)),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image Area
            Expanded(
              flex: 5,
              child: Stack(
                fit: StackFit.expand,
                children: [
                  Container(
                    decoration: const BoxDecoration(
                      color: Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
                    ),
                    child: product['primary_image'] != null
                        ? ClipRRect(
                            borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
                            child: Image.network(product['primary_image']['image_url'], fit: BoxFit.cover),
                          )
                        : const Icon(Icons.image, color: Colors.grey),
                  ),
                  if (hasOffer)
                    Positioned(
                      top: 12, left: 12,
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(20),
                        child: BackdropFilter(
                          filter: ImageFilter.blur(sigmaX: 8, sigmaY: 8),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(color: Colors.white.withOpacity(0.8), borderRadius: BorderRadius.circular(20)),
                            child: const Text('OFERTA', style: TextStyle(color: Color(0xFF059669), fontWeight: FontWeight.w900, fontSize: 9, letterSpacing: 0.5)),
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ),
            
            // Details Area
            Expanded(
              flex: 4,
              child: Padding(
                padding: const EdgeInsets.all(12.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      (product['brand']?['name'] ?? '').toUpperCase(),
                      style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 9, fontWeight: FontWeight.w800, letterSpacing: 1),
                    ),
                    const SizedBox(height: 4),
                    Expanded(
                      child: Text(
                        product['name'] ?? '',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, height: 1.2, color: Color(0xFF0F172A)),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            if (hasOffer)
                              Text('S/ ${product['compare_at_price']}', style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 10, decoration: TextDecoration.lineThrough)),
                            Text('S/ ${product['price']}', style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16, color: Color(0xFF059669))),
                          ],
                        ),
                        GestureDetector(
                          onTap: () {
                            ref.read(cartProvider.notifier).addItem(product);
                            ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                              content: Text('Agregado al carrito'),
                              backgroundColor: Color(0xFF059669),
                              duration: Duration(seconds: 1),
                              behavior: SnackBarBehavior.floating,
                            ));
                          },
                          child: Container(
                            width: 32, height: 32,
                            decoration: BoxDecoration(color: const Color(0xFF0F172A), borderRadius: BorderRadius.circular(10)),
                            child: const Icon(Icons.add, color: Colors.white, size: 18),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// Bottom Sheet Filter
class _FilterBottomSheet extends StatefulWidget {
  final List<String> brands;
  final String initialBrand;
  final bool initialOffers;
  final Function(String, bool) onApply;

  const _FilterBottomSheet({super.key, required this.brands, required this.initialBrand, required this.initialOffers, required this.onApply});

  @override
  State<_FilterBottomSheet> createState() => _FilterBottomSheetState();
}

class _FilterBottomSheetState extends State<_FilterBottomSheet> {
  late String selectedBrand;
  late bool onlyOffers;

  @override
  void initState() {
    super.initState();
    selectedBrand = widget.initialBrand;
    onlyOffers = widget.initialOffers;
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(color: Colors.white, borderRadius: BorderRadius.vertical(top: Radius.circular(32))),
      padding: const EdgeInsets.fromLTRB(24, 16, 24, 48),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(child: Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2)))),
          const SizedBox(height: 24),
          const Text('Filtros Avanzados', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w900, color: Color(0xFF0F172A))),
          const SizedBox(height: 32),
          
          const Text('Ofertas Especiales', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF64748B))),
          const SizedBox(height: 12),
          SwitchListTile(
            title: const Text('Solo productos con descuento', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 16)),
            value: onlyOffers,
            activeColor: const Color(0xFF10B981),
            contentPadding: EdgeInsets.zero,
            onChanged: (val) => setState(() => onlyOffers = val),
          ),
          
          const SizedBox(height: 32),
          const Text('Marca', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF64748B))),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8, runSpacing: 8,
            children: widget.brands.map((brand) {
              final isSelected = brand == selectedBrand;
              return GestureDetector(
                onTap: () => setState(() => selectedBrand = brand),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  decoration: BoxDecoration(
                    color: isSelected ? const Color(0xFF10B981) : Colors.white,
                    border: Border.all(color: isSelected ? const Color(0xFF10B981) : Colors.grey.shade300),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    brand,
                    style: TextStyle(color: isSelected ? Colors.white : const Color(0xFF0F172A), fontWeight: isSelected ? FontWeight.bold : FontWeight.w500),
                  ),
                ),
              );
            }).toList(),
          ),
          
          const SizedBox(height: 48),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () => widget.onApply(selectedBrand, onlyOffers),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF0F172A),
                padding: const EdgeInsets.symmetric(vertical: 18),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              ),
              child: const Text('Aplicar Filtros', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            ),
          )
        ],
      ),
    );
  }
}
