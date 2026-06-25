import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'core/theme/app_theme.dart';
import 'core/network/api_client.dart';
import 'main_screen.dart';
import 'features/catalog/screens/product_detail_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  ApiClient.setupInterceptors();
  runApp(const ProviderScope(child: VitaminApp()));
}

final _router = GoRouter(
  initialLocation: '/',
  routes: [
    GoRoute(
      path: '/',
      builder: (context, state) => const MainScreen(),
    ),
    GoRoute(
      path: '/product',
      pageBuilder: (context, state) {
        final product = state.extra as Map<String, dynamic>;
        return CustomTransitionPage(
          key: state.pageKey,
          child: ProductDetailScreen(product: product),
          transitionsBuilder: (context, animation, secondaryAnimation, child) {
            return FadeTransition(opacity: animation, child: child);
          },
          transitionDuration: const Duration(milliseconds: 400),
        );
      },
    ),
  ],
);

class VitaminApp extends StatelessWidget {
  const VitaminApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      title: 'Compra Saludable',
      theme: AppTheme.lightTheme,
      routerConfig: _router,
      debugShowCheckedModeBanner: false,
    );
  }
}
