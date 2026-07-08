import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:animations/animations.dart';
import '../providers/home_provider.dart';
import '../../../core/providers/settings_provider.dart';
import '../../catalog/screens/product_detail_screen.dart';
import '../../cart/providers/cart_provider.dart';

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final settingsAsyncValue = ref.watch(settingsProvider);
    final homeDataAsync = ref.watch(homeDataProvider);
    final settingsAsync = ref.watch(settingsProvider);

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: PreferredSize(
        preferredSize: const Size.fromHeight(115),
        child: SafeArea(
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            decoration: const BoxDecoration(
              color: Colors.white,
              border: Border(bottom: BorderSide(color: Color(0xFFF1F5F9))),
            ),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                // Top Row: Logo & Action Icons
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    // Exact COMPRASALUDABLE text logo matching Header.tsx line 200 with overflow protection
                    Flexible(
                      child: FittedBox(
                        fit: BoxFit.scaleDown,
                        alignment: Alignment.centerLeft,
                        child: GestureDetector(
                          onTap: () => context.go('/'),
                          child: RichText(
                            text: const TextSpan(
                              style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900, letterSpacing: -0.5),
                              children: [
                                TextSpan(text: 'COMPRA', style: TextStyle(color: Color(0xFF0F172A))),
                                TextSpan(text: 'SALUDABLE', style: TextStyle(color: Color(0xFF059669))),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),

                    const SizedBox(width: 8),

                    // Right Icons: User, Login, Wishlist (badge 1), Bag (badge cartCount) - Compact 30x30 boxes for zero overflow
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        GestureDetector(
                          onTap: () => context.go('/account'),
                          behavior: HitTestBehavior.opaque,
                          child: const SizedBox(
                            width: 30, height: 30,
                            child: Icon(Icons.person_outline, color: Color(0xFF475569), size: 22),
                          ),
                        ),
                        const SizedBox(width: 6),
                        GestureDetector(
                          onTap: () => context.go('/account'),
                          behavior: HitTestBehavior.opaque,
                          child: const SizedBox(
                            width: 30, height: 30,
                            child: Icon(Icons.exit_to_app_outlined, color: Color(0xFF475569), size: 22),
                          ),
                        ),
                        const SizedBox(width: 6),
                        GestureDetector(
                          onTap: () => context.go('/catalog'),
                          behavior: HitTestBehavior.opaque,
                          child: SizedBox(
                            width: 30, height: 30,
                            child: Stack(
                              clipBehavior: Clip.none,
                              alignment: Alignment.center,
                              children: [
                                const Icon(Icons.favorite_border, color: Color(0xFF475569), size: 22),
                                Positioned(
                                  top: 0,
                                  right: 0,
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                                    decoration: const BoxDecoration(color: Color(0xFFEF4444), shape: BoxShape.circle),
                                    child: const Text('1', style: TextStyle(color: Colors.white, fontSize: 8, fontWeight: FontWeight.bold)),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(width: 6),
                        Consumer(
                          builder: (context, ref, _) {
                            final cartCount = ref.watch(cartCountProvider);
                            return GestureDetector(
                              onTap: () => context.go('/cart'),
                              behavior: HitTestBehavior.opaque,
                              child: SizedBox(
                                width: 30, height: 30,
                                child: Stack(
                                  clipBehavior: Clip.none,
                                  alignment: Alignment.center,
                                  children: [
                                    const Icon(Icons.shopping_bag_outlined, color: Color(0xFF475569), size: 22),
                                    if (cartCount > 0)
                                      Positioned(
                                        top: 0,
                                        right: 0,
                                        child: Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                                          decoration: const BoxDecoration(color: Color(0xFF059669), shape: BoxShape.circle),
                                          child: Text('$cartCount', style: const TextStyle(color: Colors.white, fontSize: 8, fontWeight: FontWeight.bold)),
                                        ),
                                      ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
                      ],
                    ),
                  ],
                ),

                const SizedBox(height: 8),

                // Bottom Row: Search Box with Buscar Pill Button
                GestureDetector(
                  onTap: () => context.go('/catalog'),
                  behavior: HitTestBehavior.opaque,
                  child: Container(
                    height: 42,
                    padding: const EdgeInsets.only(left: 14, right: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F5F9), // Slate 100
                      borderRadius: BorderRadius.circular(30),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.search, color: Color(0xFF94A3B8), size: 18),
                        const SizedBox(width: 8),
                        const Expanded(
                          child: Text(
                            '¿Qué estás buscando hoy?',
                            style: TextStyle(color: Color(0xFF94A3B8), fontSize: 13, fontWeight: FontWeight.w500),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        GestureDetector(
                          onTap: () => context.go('/catalog'),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 7),
                            decoration: BoxDecoration(
                              color: const Color(0xFF0F172A), // Dark Slate / Black pill button
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: const Text(
                              'Buscar',
                              style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                            ),
                          ),
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
      body: homeDataAsync.when(
        data: (data) {
          final blocks = data['blocks'] as List? ?? [];
          
          return RefreshIndicator(
            onRefresh: () async {
              ref.refresh(homeDataProvider.future);
              ref.refresh(settingsProvider.future);
            },
            child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  ...blocks.map((block) {
                    final bType = block['type'];
                    final bData = block['data'] as Map<String, dynamic>? ?? {};
                    if (bType == 'hero_modern') return _buildHeroModern(bData, context);
                    if (bType == 'category_grid') return _buildCategoryGrid(bData, context);
                    if (bType == 'featured_products') return _buildFeaturedProducts(bData, ref, context);
                    if (bType == 'carousel') return _HomeCarouselBlock(data: bData);
                    if (bType == 'value_proposition') return _buildValueProp(bData, context);
                    if (bType == 'custom_html') return _buildCustomHtml(bData, context);
                    return const SizedBox.shrink();
                  }).toList(),
                  
                  const SizedBox(height: 40),
                  
                  // Dynamic Footer
                  settingsAsync.when(
                    data: (settings) => Container(
                      width: double.infinity,
                      color: const Color(0xFF0F172A),
                      padding: const EdgeInsets.fromLTRB(24, 40, 24, 120),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.headset_mic_outlined, color: Color(0xFFF97316), size: 32),
                              const SizedBox(width: 16),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(settings['whatsapp_number'] ?? '+51', style: const TextStyle(color: Color(0xFFF97316), fontSize: 20, fontWeight: FontWeight.bold)),
                                  const Text('Atención al Cliente', style: TextStyle(color: Color(0xFFF97316), fontSize: 14)),
                                ],
                              ),
                            ],
                          ),
                          const SizedBox(height: 32),
                          const Divider(color: Colors.white10, height: 1),
                          const SizedBox(height: 32),
                          Text(settings['store_address'] ?? '', style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
                          const SizedBox(height: 16),
                          const Text('Correo de Ayuda', style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
                          const SizedBox(height: 4),
                          Text(settings['contact_email'] ?? '', style: const TextStyle(color: Colors.white70, fontSize: 14)),
                          const SizedBox(height: 24),
                          Row(
                            children: [
                              Container(
                                width: 40, height: 40, margin: const EdgeInsets.only(right: 12),
                                decoration: BoxDecoration(color: Colors.white.withOpacity(0.1), shape: BoxShape.circle),
                                child: const Icon(Icons.play_arrow, color: Colors.white, size: 20),
                              ),
                              Container(
                                width: 40, height: 40,
                                decoration: BoxDecoration(color: Colors.white.withOpacity(0.1), shape: BoxShape.circle),
                                child: const Icon(Icons.chat_bubble_outline, color: Colors.white, size: 20),
                              ),
                            ],
                          ),
                          const SizedBox(height: 40),
                          ...(settings['footer_columns'] as List? ?? []).map((col) => Column(
                                children: [
                                  Padding(
                                    padding: const EdgeInsets.symmetric(vertical: 20),
                                    child: Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(col['title'] ?? '', style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
                                        const Icon(Icons.add, color: Colors.white, size: 20),
                                      ],
                                    ),
                                  ),
                                  const Divider(color: Colors.white10, height: 1),
                                ],
                              )),
                          const SizedBox(height: 40),
                          Align(
                            alignment: Alignment.centerRight,
                            child: Container(
                              width: 48, height: 48,
                              decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle),
                              child: const Icon(Icons.arrow_upward, color: Colors.black),
                            ),
                          ),
                        ],
                      ),
                    ),
                    loading: () => const SizedBox(height: 100, child: Center(child: CircularProgressIndicator())),
                    error: (err, stack) => const SizedBox(height: 100, child: Center(child: Text('Error al cargar ajustes'))),
                  ),
                ],
              ),
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, stack) => Center(child: Text('Error: $err')),
      ),
    );
  }

  Widget _buildHeroModern(Map<String, dynamic> heroData, BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.center,
      children: [
        const SizedBox(height: 40),
        if (heroData['badge'] != null)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: Colors.green.shade50,
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: Colors.green.shade100),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(width: 8, height: 8, decoration: const BoxDecoration(color: Colors.green, shape: BoxShape.circle)),
                const SizedBox(width: 8),
                Text(heroData['badge'], style: const TextStyle(color: Colors.green, fontWeight: FontWeight.bold, fontSize: 12)),
              ],
            ),
          ),
        const SizedBox(height: 24),
        Text(heroData['title_line_1'] ?? '', style: const TextStyle(fontSize: 42, fontWeight: FontWeight.w900, color: Color(0xFF0F172A), height: 1.1, letterSpacing: -1), textAlign: TextAlign.center),
        Text(heroData['title_line_2'] ?? '', style: const TextStyle(fontSize: 42, fontWeight: FontWeight.w900, color: Color(0xFF059669), height: 1.1, letterSpacing: -1), textAlign: TextAlign.center),
        const SizedBox(height: 20),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 32),
          child: Text(heroData['description'] ?? '', style: const TextStyle(fontSize: 16, color: Color(0xFF64748B), height: 1.5), textAlign: TextAlign.center),
        ),
        const SizedBox(height: 32),
        if (heroData['button_text'] != null)
          ElevatedButton(
            onPressed: () => context.go('/catalog'),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF0F172A),
              padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 16),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(heroData['button_text'], style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                const SizedBox(width: 8),
                const Icon(Icons.arrow_forward, size: 20),
              ],
            ),
          ),
        const SizedBox(height: 24),
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            SizedBox(
              width: 70, height: 32,
              child: Stack(
                children: const [
                  Positioned(left: 0, child: CircleAvatar(radius: 16, backgroundImage: NetworkImage('https://i.pravatar.cc/100?img=1'))),
                  Positioned(left: 20, child: CircleAvatar(radius: 16, backgroundImage: NetworkImage('https://i.pravatar.cc/100?img=2'))),
                  Positioned(left: 40, child: CircleAvatar(radius: 16, backgroundImage: NetworkImage('https://i.pravatar.cc/100?img=3'))),
                ],
              ),
            ),
            const SizedBox(width: 8),
            const Text('+2,000 clientes felices', style: TextStyle(color: Color(0xFF64748B), fontSize: 13, fontWeight: FontWeight.w500)),
          ],
        ),
        const SizedBox(height: 48),
        Container(
          width: double.infinity, height: 300,
          margin: const EdgeInsets.symmetric(horizontal: 16),
          decoration: BoxDecoration(
            borderRadius: const BorderRadius.vertical(top: Radius.circular(40), bottom: Radius.circular(40)),
            boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.1), blurRadius: 20, offset: const Offset(0, 10))],
            image: const DecorationImage(
              image: NetworkImage('https://images.unsplash.com/photo-1593095948071-474c5cc2989d?q=80&w=800&auto=format&fit=crop'),
              fit: BoxFit.cover,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildCategoryGrid(Map<String, dynamic> data, BuildContext context) {
    final categories = data['categories'] as List? ?? [];
    if (categories.isEmpty) return const SizedBox.shrink();

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            data['title'] ?? 'Compra por Categoría',
            style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
          ),
          const SizedBox(height: 16),
          Wrap(
            spacing: 10,
            runSpacing: 10,
            children: categories.map((cat) {
              final catMap = cat as Map<String, dynamic>? ?? {};
              return GestureDetector(
                onTap: () => context.go('/catalog?category=${catMap['id']}'),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    border: Border.all(color: Colors.grey.shade200),
                    borderRadius: BorderRadius.circular(30),
                    boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.03), blurRadius: 6, offset: const Offset(0, 2))],
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(width: 8, height: 8, decoration: const BoxDecoration(color: Color(0xFF059669), shape: BoxShape.circle)),
                      const SizedBox(width: 8),
                      Text(
                        catMap['name'] ?? 'Categoría',
                        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: Color(0xFF334155)),
                      ),
                    ],
                  ),
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  Widget _buildFeaturedProducts(Map<String, dynamic> data, WidgetRef ref, BuildContext context) {
    final products = data['products'] as List? ?? [];
    if (products.isEmpty) return const SizedBox.shrink();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const SizedBox(height: 40),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Expanded(
                child: Text(
                  data['title'] ?? 'Productos\nDestacados',
                  style: const TextStyle(fontSize: 32, fontWeight: FontWeight.w900, color: Color(0xFF0F172A), height: 1.1, letterSpacing: -1),
                ),
              ),
              GestureDetector(
                onTap: () => context.go('/catalog'),
                behavior: HitTestBehavior.opaque,
                child: Row(
                  children: const [
                    Text('VER\nTODO', style: TextStyle(color: Colors.green, fontWeight: FontWeight.bold, fontSize: 12), textAlign: TextAlign.right),
                    SizedBox(width: 4),
                    Icon(Icons.chevron_right, color: Colors.green, size: 16),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 24),
        SizedBox(
          height: 450,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16),
            itemCount: products.length,
            itemBuilder: (context, index) {
              final p = products[index];
              return OpenContainer(
                closedElevation: 0,
                openElevation: 0,
                closedColor: Colors.white,
                openColor: Colors.white,
                middleColor: Colors.white,
                closedShape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
                transitionDuration: const Duration(milliseconds: 350),
                transitionType: ContainerTransitionType.fade,
                openBuilder: (context, action) => ProductDetailScreen(product: p),
                closedBuilder: (context, action) => Container(
                  width: 260,
                  margin: const EdgeInsets.only(right: 16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(24),
                    border: Border.all(color: Colors.grey.shade200),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Image Section
                      Expanded(
                        child: Container(
                          width: double.infinity,
                          decoration: BoxDecoration(
                            color: Colors.orange.shade400, // Fallback color
                            borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
                          ),
                          child: p['primary_image'] != null
                              ? ClipRRect(
                                  borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
                                  child: Image.network(
                                    p['primary_image']['image_url'],
                                    fit: BoxFit.cover,
                                    errorBuilder: (c, e, s) => const Icon(Icons.image, color: Colors.white),
                                  ),
                                )
                              : const Icon(Icons.image, color: Colors.white),
                        ),
                      ),
                      // Details Section
                      Padding(
                        padding: const EdgeInsets.all(16.0),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                ...List.generate(4, (i) => const Icon(Icons.star, color: Colors.amber, size: 14)),
                                const Icon(Icons.star, color: Colors.grey, size: 14),
                                const SizedBox(width: 4),
                                const Text('(12)', style: TextStyle(color: Colors.grey, fontSize: 12)),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text(
                              (p['brand']?['name'] ?? '').toUpperCase(),
                              style: TextStyle(color: Colors.grey.shade600, fontSize: 10, fontWeight: FontWeight.bold, letterSpacing: 1),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              p['name'] ?? '',
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, height: 1.2),
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 16),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  'S/ ${p['price']}',
                                  style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 22, color: Color(0xFF0F172A)),
                                ),
                                GestureDetector(
                                  onTap: () {
                                    ref.read(cartProvider.notifier).addItem(p);
                                    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
                                      content: Text('Agregado al carrito'),
                                      backgroundColor: Color(0xFF059669),
                                      duration: Duration(seconds: 1),
                                      behavior: SnackBarBehavior.floating,
                                    ));
                                  },
                                  child: Container(
                                    width: 40, height: 40,
                                    decoration: const BoxDecoration(color: Color(0xFF0F172A), shape: BoxShape.circle),
                                    child: const Icon(Icons.add, color: Colors.white),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildValueProp(Map<String, dynamic> data, BuildContext context) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(top: 48),
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 50),
      decoration: const BoxDecoration(color: Color(0xFF0F172A)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Text(
            data['title'] ?? 'Propuesta de Valor',
            style: const TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: Colors.white, height: 1.2),
            textAlign: TextAlign.center,
          ),
          if (data['description'] != null) ...[
            const SizedBox(height: 16),
            Text(
              data['description'],
              style: const TextStyle(fontSize: 16, color: Color(0xFF94A3B8), height: 1.5),
              textAlign: TextAlign.center,
            ),
          ],
          if (data['button_text'] != null) ...[
            const SizedBox(height: 32),
            OutlinedButton(
              onPressed: () => context.go('/catalog'),
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: Colors.white, width: 2),
                padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
              ),
              child: Text(
                data['button_text'],
                style: const TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildCustomHtml(Map<String, dynamic> data, BuildContext context) {
    final content = data['content'] as String? ?? '';
    if (content.isEmpty) return const SizedBox.shrink();
    // Strip simple HTML tags for clean text display if any custom HTML block is passed
    final cleanText = content.replaceAll(RegExp(r'<[^>]*>'), ' ').trim();
    if (cleanText.isEmpty) return const SizedBox.shrink();

    return Container(
      width: double.infinity,
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Text(
        cleanText,
        style: const TextStyle(fontSize: 14, color: Color(0xFF334155), height: 1.5),
        textAlign: TextAlign.center,
      ),
    );
  }
}

class _HomeCarouselBlock extends StatefulWidget {
  final Map<String, dynamic> data;
  const _HomeCarouselBlock({required this.data});

  @override
  State<_HomeCarouselBlock> createState() => _HomeCarouselBlockState();
}

class _HomeCarouselBlockState extends State<_HomeCarouselBlock> {
  late PageController _pageController;
  int _currentIndex = 0;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _pageController = PageController();
    final slides = widget.data['slides'] as List? ?? [];
    final autoplay = widget.data['autoplay'] != false;
    if (autoplay && slides.length > 1) {
      _timer = Timer.periodic(const Duration(seconds: 5), (timer) {
        if (!mounted) return;
        final nextIndex = (_currentIndex + 1) % slides.length;
        _pageController.animateToPage(
          nextIndex,
          duration: const Duration(milliseconds: 600),
          curve: Curves.easeInOut,
        );
      });
    }
  }

  @override
  void dispose() {
    _timer?.cancel();
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final slides = widget.data['slides'] as List? ?? [];
    if (slides.isEmpty) return const SizedBox.shrink();

    return Container(
      width: double.infinity,
      height: 340,
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(32),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 20, offset: const Offset(0, 10))],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(32),
        child: Stack(
          children: [
            PageView.builder(
              controller: _pageController,
              itemCount: slides.length,
              onPageChanged: (idx) => setState(() => _currentIndex = idx),
              itemBuilder: (context, index) {
                final slide = slides[index] as Map<String, dynamic>? ?? {};
                final imgUrl = slide['image'] as String? ?? '';
                final fullUrl = imgUrl.startsWith('http')
                    ? imgUrl
                    : 'http://10.0.2.2:8000/storage/$imgUrl'; // iOS/Android compatible
                return GestureDetector(
                  onTap: () => context.go('/catalog'),
                  child: Image.network(
                    fullUrl,
                    fit: BoxFit.cover,
                    errorBuilder: (c, e, s) => Container(
                      color: const Color(0xFF0F172A),
                      alignment: Alignment.center,
                      child: const Icon(Icons.image, color: Colors.white38, size: 48),
                    ),
                  ),
                );
              },
            ),
            if (slides.length > 1)
              Positioned(
                bottom: 16,
                left: 0,
                right: 0,
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: List.generate(slides.length, (index) {
                    final isSelected = index == _currentIndex;
                    return AnimatedContainer(
                      duration: const Duration(milliseconds: 300),
                      width: isSelected ? 24 : 8,
                      height: 8,
                      margin: const EdgeInsets.symmetric(horizontal: 4),
                      decoration: BoxDecoration(
                        color: isSelected ? Colors.white : Colors.white.withOpacity(0.5),
                        borderRadius: BorderRadius.circular(4),
                      ),
                    );
                  }),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
