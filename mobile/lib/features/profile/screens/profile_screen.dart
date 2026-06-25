import 'dart:ui';
import 'package:flutter/material.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  bool _isLogin = true;

  void _setLogin(bool isLogin) {
    if (_isLogin != isLogin) {
      setState(() {
        _isLogin = isLogin;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFAFAFA), // Off-white minimalist background
      body: Stack(
        children: [
          // Background Orbs
          Positioned(
            top: -100, right: -50,
            child: Container(
              width: 300, height: 300,
              decoration: BoxDecoration(shape: BoxShape.circle, color: const Color(0xFF10B981).withOpacity(0.15)),
            ),
          ),
          Positioned(
            top: 200, left: -100,
            child: Container(
              width: 250, height: 250,
              decoration: BoxDecoration(shape: BoxShape.circle, color: const Color(0xFF3B82F6).withOpacity(0.10)),
            ),
          ),
          Positioned.fill(
            child: BackdropFilter(filter: ImageFilter.blur(sigmaX: 80, sigmaY: 80), child: Container(color: Colors.transparent)),
          ),
          
          SafeArea(
            bottom: false,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Minimal Header
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.black.withOpacity(0.05)),
                          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 10, offset: const Offset(0, 4))],
                        ),
                        child: const Icon(Icons.shopping_cart, color: Color(0xFF059669), size: 20),
                      ),
                      const SizedBox(width: 12),
                      const Text(
                        'Mi Cuenta',
                        style: TextStyle(color: Color(0xFF09090B), fontSize: 20, fontWeight: FontWeight.w800, letterSpacing: -0.5),
                      ),
                    ],
                  ),
                ),
                
                Expanded(
                  child: SingleChildScrollView(
                    physics: const BouncingScrollPhysics(),
                    padding: const EdgeInsets.only(left: 20, right: 20, top: 10, bottom: 40),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
                      decoration: _cardDecoration(),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          _buildTitleSwitch(),
                          
                          AnimatedSize(
                            duration: const Duration(milliseconds: 350),
                            curve: Curves.easeInOutCubic,
                            alignment: Alignment.topCenter,
                            child: AnimatedSwitcher(
                              duration: const Duration(milliseconds: 350),
                              switchInCurve: Curves.easeOutCubic,
                              switchOutCurve: Curves.easeInCubic,
                              layoutBuilder: (Widget? currentChild, List<Widget> previousChildren) {
                                return Stack(
                                  alignment: Alignment.topCenter,
                                  children: <Widget>[
                                    ...previousChildren.map((child) => Positioned(
                                          top: 0,
                                          left: 0,
                                          right: 0,
                                          child: child,
                                        )),
                                    if (currentChild != null) currentChild,
                                  ],
                                );
                              },
                              transitionBuilder: (Widget child, Animation<double> animation) {
                                // Identify if the incoming/outgoing child is login or register
                                final isLoginChild = (child.key as ValueKey).value == 'login';
                                
                                // If it's Login, it slides from Left (-1). If Register, from Right (1).
                                final offsetX = isLoginChild ? -1.0 : 1.0;
                                
                                final offsetAnimation = Tween<Offset>(
                                  begin: Offset(offsetX, 0.0),
                                  end: Offset.zero,
                                ).animate(animation);

                                return FadeTransition(
                                  opacity: animation,
                                  child: SlideTransition(
                                    position: offsetAnimation,
                                    child: child,
                                  ),
                                );
                              },
                              child: _isLogin ? _buildLoginContent() : _buildRegisterContent(),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTitleSwitch() {
    return Padding(
      padding: const EdgeInsets.only(bottom: 24),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          _buildTabTitle('Iniciar Sesión', _isLogin, () => _setLogin(true)),
          const SizedBox(width: 20),
          _buildTabTitle('Registro', !_isLogin, () => _setLogin(false)),
        ],
      ),
    );
  }

  Widget _buildTabTitle(String title, bool isSelected, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      behavior: HitTestBehavior.opaque,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          AnimatedDefaultTextStyle(
            duration: const Duration(milliseconds: 300),
            curve: Curves.easeOutCubic,
            style: TextStyle(
              fontSize: isSelected ? 28 : 16,
              fontWeight: isSelected ? FontWeight.w900 : FontWeight.w600,
              color: isSelected ? const Color(0xFF09090B) : const Color(0xFFA1A1AA),
              letterSpacing: isSelected ? -1.2 : 0.0,
              fontFamily: DefaultTextStyle.of(context).style.fontFamily,
            ),
            child: Text(title),
          ),
          const SizedBox(height: 6),
          // Animated Underline Indicator
          AnimatedContainer(
            duration: const Duration(milliseconds: 300),
            curve: Curves.easeOutCubic,
            height: 3,
            width: isSelected ? 32 : 0, // Grows when selected, shrinks to 0 when not
            decoration: BoxDecoration(
              color: const Color(0xFF10B981), // Emerald green indicator
              borderRadius: BorderRadius.circular(2),
            ),
          )
        ],
      ),
    );
  }

  Widget _buildLoginContent() {
    return Column(
      key: const ValueKey('login'),
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Ingresa para continuar tus compras.', style: TextStyle(fontSize: 14, color: Color(0xFF71717A), letterSpacing: -0.2)),
        const SizedBox(height: 32),
        
        _buildTextFieldTitle('CORREO ELECTRÓNICO'),
        _buildTextField(hint: 'ejemplo@correo.com', icon: Icons.email_outlined, keyboardType: TextInputType.emailAddress),
        const SizedBox(height: 16),
        
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            _buildTextFieldTitle('CONTRASEÑA'),
            GestureDetector(
              onTap: () {},
              child: const Text('¿Olvidaste tu contraseña?', style: TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF71717A), fontSize: 11)),
            ),
          ],
        ),
        const SizedBox(height: 8),
        _buildTextField(hint: '••••••••', icon: Icons.lock_outline, isPassword: true),
        const SizedBox(height: 32),
        
        _buildMainButton('Iniciar Sesión'),
        const SizedBox(height: 24),
        _buildDivider('O continuar con'),
        const SizedBox(height: 24),
        _buildGoogleButton(),
      ],
    );
  }

  Widget _buildRegisterContent() {
    return Column(
      key: const ValueKey('register'),
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Únete a nuestra comunidad hoy.', style: TextStyle(fontSize: 14, color: Color(0xFF71717A), letterSpacing: -0.2)),
        const SizedBox(height: 32),
        
        _buildTextFieldTitle('NOMBRE COMPLETO'),
        _buildTextField(hint: 'Tu nombre', icon: Icons.person_outline),
        const SizedBox(height: 16),
        
        _buildTextFieldTitle('CORREO ELECTRÓNICO'),
        _buildTextField(hint: 'ejemplo@correo.com', icon: Icons.email_outlined, keyboardType: TextInputType.emailAddress),
        const SizedBox(height: 16),
        
        _buildTextFieldTitle('CONTRASEÑA'),
        _buildTextField(hint: '••••••••', icon: Icons.lock_outline, isPassword: true),
        const SizedBox(height: 32),
        
        _buildMainButton('Crear Cuenta'),
        const SizedBox(height: 24),
        _buildDivider('O regístrate con'),
        const SizedBox(height: 24),
        _buildGoogleButton(),
      ],
    );
  }

  BoxDecoration _cardDecoration() {
    return BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(24),
      border: Border.all(color: Colors.black.withOpacity(0.04)),
      boxShadow: [
        BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 8, offset: const Offset(0, 4)),
        BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 24, offset: const Offset(0, 12)),
      ],
    );
  }

  Widget _buildTextFieldTitle(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Text(title, style: const TextStyle(fontWeight: FontWeight.w800, color: Color(0xFF52525B), fontSize: 10, letterSpacing: 0.5)),
    );
  }

  Widget _buildTextField({required String hint, required IconData icon, bool isPassword = false, TextInputType? keyboardType}) {
    return TextField(
      obscureText: isPassword,
      keyboardType: keyboardType,
      cursorColor: const Color(0xFF10B981),
      style: const TextStyle(fontWeight: FontWeight.w500, fontSize: 14, color: Color(0xFF09090B)),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: const TextStyle(color: Color(0xFFA1A1AA)),
        prefixIcon: Icon(icon, color: const Color(0xFFA1A1AA), size: 18),
        suffixIcon: isPassword ? const Icon(Icons.visibility_off_outlined, color: Color(0xFFA1A1AA), size: 18) : null,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        filled: true,
        fillColor: const Color(0xFFF4F4F5),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.black.withOpacity(0.1), width: 1)),
      ),
    );
  }

  Widget _buildMainButton(String text) {
    return _SpringButton(
      onTap: () {},
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(vertical: 14),
        decoration: BoxDecoration(
          color: const Color(0xFF09090B),
          borderRadius: BorderRadius.circular(12),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.1), blurRadius: 10, offset: const Offset(0, 4))],
        ),
        child: Center(child: Text(text, style: const TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.w600))),
      ),
    );
  }

  Widget _buildDivider(String text) {
    return Row(
      children: [
        Expanded(child: Divider(color: Colors.black.withOpacity(0.06))),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: Text(text, style: const TextStyle(color: Color(0xFFA1A1AA), fontSize: 12, fontWeight: FontWeight.w500)),
        ),
        Expanded(child: Divider(color: Colors.black.withOpacity(0.06))),
      ],
    );
  }

  Widget _buildGoogleButton() {
    return _SpringButton(
      onTap: () {},
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: Colors.white,
          border: Border.all(color: Colors.black.withOpacity(0.06)),
          borderRadius: BorderRadius.circular(12),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 4, offset: const Offset(0, 2))],
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(4),
              decoration: BoxDecoration(color: Colors.black.withOpacity(0.03), borderRadius: BorderRadius.circular(8)),
              child: const Text('G', style: TextStyle(color: Color(0xFF09090B), fontWeight: FontWeight.w900, fontSize: 14)),
            ),
            const SizedBox(width: 12),
            const Text('Continuar con Google', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: Color(0xFF09090B))),
          ],
        ),
      ),
    );
  }
}

// Spring Button Component
class _SpringButton extends StatefulWidget {
  final Widget child;
  final VoidCallback onTap;
  
  const _SpringButton({required this.child, required this.onTap});

  @override
  State<_SpringButton> createState() => _SpringButtonState();
}

class _SpringButtonState extends State<_SpringButton> with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _scaleAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(vsync: this, duration: const Duration(milliseconds: 100));
    _scaleAnimation = Tween<double>(begin: 1.0, end: 0.96).animate(CurvedAnimation(parent: _controller, curve: Curves.easeInOut));
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _handleTapDown(TapDownDetails details) => _controller.forward();
  void _handleTapUp(TapUpDetails details) { _controller.reverse(); widget.onTap(); }
  void _handleTapCancel() => _controller.reverse();

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTapDown: _handleTapDown,
      onTapUp: _handleTapUp,
      onTapCancel: _handleTapCancel,
      behavior: HitTestBehavior.opaque,
      child: ScaleTransition(scale: _scaleAnimation, child: widget.child),
    );
  }
}
