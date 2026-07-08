import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../providers/cart_provider.dart';

class CheckoutScreen extends ConsumerStatefulWidget {
  const CheckoutScreen({super.key});

  @override
  ConsumerState<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends ConsumerState<CheckoutScreen> {
  final _formKey = GlobalKey<FormState>();
  
  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _dniController = TextEditingController();
  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _addressController = TextEditingController();
  final TextEditingController _cityController = TextEditingController();
  final TextEditingController _operationController = TextEditingController();

  int _selectedPaymentMethod = 0; // 0: Yape/Plin, 1: Tarjeta, 2: Contraentrega
  bool _isProcessing = false;

  @override
  void dispose() {
    _nameController.dispose();
    _dniController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    _cityController.dispose();
    _operationController.dispose();
    super.dispose();
  }

  void _confirmOrder(double totalAmount) async {
    if (!_formKey.currentState!.validate()) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Por favor, completa los campos obligatorios de envío'), backgroundColor: Color(0xFFEF4444)),
      );
      return;
    }

    if (_selectedPaymentMethod == 0 && _operationController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Ingresa el código u operación de tu transferencia Yape/Plin'), backgroundColor: Color(0xFFF59E0B)),
      );
      return;
    }

    setState(() => _isProcessing = true);
    await Future.delayed(const Duration(seconds: 2));
    if (!mounted) return;
    setState(() => _isProcessing = false);

    final orderId = 'CS-${(1000 + (DateTime.now().millisecondsSinceEpoch % 8999))}';
    final name = _nameController.text.trim();
    final phone = _phoneController.text.trim();

    // Clear cart
    ref.read(cartProvider.notifier).clearCart();

    // Show Success Dialog with personalized WhatsApp tracking action
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(28)),
        contentPadding: const EdgeInsets.all(28),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 80,
              height: 80,
              decoration: BoxDecoration(
                color: const Color(0xFF059669).withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.check_circle, color: Color(0xFF059669), size: 52),
            ),
            const SizedBox(height: 20),
            Text(
              '¡Pedido Confirmado!',
              style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 22, color: Color(0xFF0F172A)),
            ),
            const SizedBox(height: 8),
            Text(
              'Orden #$orderId',
              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF059669)),
            ),
            const SizedBox(height: 16),
            Text(
              'Gracias $name. Hemos registrado tu pedido satisfactoriamente por S/ ${totalAmount.toStringAsFixed(2)}. Nuestro equipo de logística se comunicará al $phone para la coordinación.',
              textAlign: TextAlign.center,
              style: const TextStyle(color: Color(0xFF64748B), fontSize: 13, height: 1.5),
            ),
            const SizedBox(height: 28),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: () {
                  Navigator.pop(ctx);
                  Navigator.pop(context); // Return to cart/home
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text('Abriendo WhatsApp para rastrear orden #$orderId...'),
                      backgroundColor: const Color(0xFF25D366),
                    ),
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF25D366),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                icon: const Icon(Icons.chat_bubble, color: Colors.white, size: 20),
                label: const Text('Rastrear por WhatsApp', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14)),
              ),
            ),
            const SizedBox(height: 12),
            TextButton(
              onPressed: () {
                Navigator.pop(ctx);
                Navigator.pop(context);
              },
              child: const Text('Volver al Inicio', style: TextStyle(color: Color(0xFF64748B), fontWeight: FontWeight.w600)),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final cartItems = ref.watch(cartProvider);
    final subtotal = ref.watch(cartTotalProvider);
    final isFreeShipping = subtotal >= 150.0 || subtotal == 0;
    final shippingCost = isFreeShipping ? 0.0 : 15.0;
    final total = subtotal + shippingCost;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Finalizar Compra', style: TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0F172A))),
        centerTitle: true,
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 0,
      ),
      body: cartItems.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.shopping_cart_checkout, size: 80, color: Color(0xFFCBD5E1)),
                  const SizedBox(height: 16),
                  const Text('No tienes ítems por procesar', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF64748B))),
                  const SizedBox(height: 24),
                  ElevatedButton(
                    onPressed: () => Navigator.pop(context),
                    style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF059669)),
                    child: const Text('Volver al Carrito', style: TextStyle(color: Colors.white)),
                  ),
                ],
              ),
            )
          : Form(
              key: _formKey,
              child: Stack(
                children: [
                  SingleChildScrollView(
                    physics: const BouncingScrollPhysics(),
                    padding: const EdgeInsets.only(left: 20, right: 20, top: 20, bottom: 150),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Section 1: Datos de Envío
                        _buildSectionHeader(Icons.local_shipping_outlined, '1. Dirección de Entrega'),
                        const SizedBox(height: 16),
                        Container(
                          padding: const EdgeInsets.all(20),
                          decoration: _cardDecoration(),
                          child: Column(
                            children: [
                              _buildTextField(controller: _nameController, hint: 'Nombre y Apellido *', icon: Icons.person_outline, isRequired: true),
                              const SizedBox(height: 14),
                              Row(
                                children: [
                                  Expanded(child: _buildTextField(controller: _dniController, hint: 'DNI / CE *', icon: Icons.badge_outlined, isRequired: true, keyboardType: TextInputType.number)),
                                  const SizedBox(width: 14),
                                  Expanded(child: _buildTextField(controller: _phoneController, hint: 'Celular *', icon: Icons.phone_android, isRequired: true, keyboardType: TextInputType.phone)),
                                ],
                              ),
                              const SizedBox(height: 14),
                              _buildTextField(controller: _addressController, hint: 'Dirección exacta (Av, Calle, N°, Dpto) *', icon: Icons.location_on_outlined, isRequired: true),
                              const SizedBox(height: 14),
                              _buildTextField(controller: _cityController, hint: 'Ciudad / Distrito *', icon: Icons.map_outlined, isRequired: true),
                            ],
                          ),
                        ),

                        const SizedBox(height: 28),

                        // Section 2: Método de Pago
                        _buildSectionHeader(Icons.payment_outlined, '2. Método de Pago'),
                        const SizedBox(height: 16),
                        _buildPaymentOption(
                          index: 0,
                          title: 'Yape / Plin (Pago Rápido QR)',
                          subtitle: 'Transferencia directa con aprobación instantánea',
                          icon: Icons.qr_code_2,
                          badgeText: 'POPULAR',
                        ),
                        if (_selectedPaymentMethod == 0) _buildYapeDetails(),
                        const SizedBox(height: 12),
                        _buildPaymentOption(
                          index: 1,
                          title: 'Tarjeta de Crédito / Débito',
                          subtitle: 'Visa, Mastercard, American Express',
                          icon: Icons.credit_card,
                        ),
                        if (_selectedPaymentMethod == 1) _buildCardDetails(),
                        const SizedBox(height: 12),
                        _buildPaymentOption(
                          index: 2,
                          title: 'Contraentrega (Efectivo o POS)',
                          subtitle: 'Pagas directamente al motorista al recibir tu pedido',
                          icon: Icons.handshake_outlined,
                        ),

                        const SizedBox(height: 28),

                        // Section 3: Resumen del Pedido
                        _buildSectionHeader(Icons.receipt_long_outlined, '3. Resumen de Orden'),
                        const SizedBox(height: 16),
                        Container(
                          padding: const EdgeInsets.all(20),
                          decoration: _cardDecoration(),
                          child: Column(
                            children: [
                              ...cartItems.map((item) => Padding(
                                    padding: const EdgeInsets.only(bottom: 12),
                                    child: Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Expanded(
                                          child: Text(
                                            '${item.quantity}x ${item.product['name'] ?? 'Producto'}',
                                            style: const TextStyle(fontSize: 13, color: Color(0xFF334155), fontWeight: FontWeight.w600),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                        Text(
                                          'S/ ${item.totalPrice.toStringAsFixed(2)}',
                                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                                        ),
                                      ],
                                    ),
                                  )),
                              const Divider(color: Color(0xFFF1F5F9), height: 24),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  const Text('Subtotal:', style: TextStyle(color: Color(0xFF64748B), fontSize: 14)),
                                  Text('S/ ${subtotal.toStringAsFixed(2)}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A))),
                                ],
                              ),
                              const SizedBox(height: 8),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  const Text('Envío a domicilio:', style: TextStyle(color: Color(0xFF64748B), fontSize: 14)),
                                  Text(
                                    isFreeShipping ? 'GRATIS 🎉' : 'S/ ${shippingCost.toStringAsFixed(2)}',
                                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: isFreeShipping ? const Color(0xFF059669) : const Color(0xFF0F172A)),
                                  ),
                                ],
                              ),
                              const Divider(color: Color(0xFFE2E8F0), height: 28),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  const Text('Total a Pagar:', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 18, color: Color(0xFF0F172A))),
                                  Text('S/ ${total.toStringAsFixed(2)}', style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 24, color: Color(0xFF059669))),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Bottom Sticky CTA
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
                      child: ElevatedButton(
                        onPressed: _isProcessing ? null : () => _confirmOrder(total),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF059669),
                          padding: const EdgeInsets.symmetric(vertical: 18),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                        ),
                        child: _isProcessing
                            ? const SizedBox(height: 22, width: 22, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5))
                            : Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  const Icon(Icons.lock_outline, color: Colors.white, size: 20),
                                  const SizedBox(width: 8),
                                  Text('Confirmar Pedido - S/ ${total.toStringAsFixed(2)}', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white)),
                                ],
                              ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
    );
  }

  Widget _buildSectionHeader(IconData icon, String title) {
    return Row(
      children: [
        Icon(icon, color: const Color(0xFF059669), size: 22),
        const SizedBox(width: 8),
        Text(title, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w900, color: Color(0xFF0F172A))),
      ],
    );
  }

  BoxDecoration _cardDecoration() {
    return BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(24),
      border: Border.all(color: const Color(0xFFE2E8F0)),
      boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.03), blurRadius: 15, offset: const Offset(0, 5))],
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    bool isRequired = false,
    TextInputType? keyboardType,
  }) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      validator: isRequired
          ? (val) {
              if (val == null || val.trim().isEmpty) return 'Campo requerido';
              return null;
            }
          : null,
      style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14, color: Color(0xFF0F172A)),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 13),
        prefixIcon: Icon(icon, size: 18, color: const Color(0xFF94A3B8)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        filled: true,
        fillColor: const Color(0xFFF8FAFC),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: BorderSide.none),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFF059669), width: 1.5)),
        errorBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFFEF4444), width: 1)),
      ),
    );
  }

  Widget _buildPaymentOption({required int index, required String title, required String subtitle, required IconData icon, String? badgeText}) {
    final isSelected = _selectedPaymentMethod == index;
    return GestureDetector(
      onTap: () => setState(() => _selectedPaymentMethod = index),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 250),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF059669).withOpacity(0.06) : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: isSelected ? const Color(0xFF059669) : const Color(0xFFE2E8F0), width: isSelected ? 2 : 1),
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(color: isSelected ? const Color(0xFF059669) : const Color(0xFFF1F5F9), shape: BoxShape.circle),
              child: Icon(icon, color: isSelected ? Colors.white : const Color(0xFF64748B), size: 22),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Text(title, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: isSelected ? const Color(0xFF064E3B) : const Color(0xFF0F172A))),
                      if (badgeText != null) ...[
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(color: const Color(0xFF10B981), borderRadius: BorderRadius.circular(6)),
                          child: Text(badgeText, style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold)),
                        ),
                      ]
                    ],
                  ),
                  const SizedBox(height: 2),
                  Text(subtitle, style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                ],
              ),
            ),
            Icon(isSelected ? Icons.radio_button_checked : Icons.radio_button_off, color: isSelected ? const Color(0xFF059669) : const Color(0xFFCBD5E1)),
          ],
        ),
      ),
    );
  }

  Widget _buildYapeDetails() {
    return Container(
      margin: const EdgeInsets.only(top: 8),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFF0FDF4),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFBBF7D0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.phone_android, color: Color(0xFF059669), size: 20),
              const SizedBox(width: 8),
              const Text('Celular Oficial: ', style: TextStyle(fontSize: 13, color: Color(0xFF1E293B))),
              const Text('928 586 883', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: Color(0xFF059669))),
            ],
          ),
          const SizedBox(height: 4),
          const Text('Titular: COMPRA SALUDABLE S.A.C.', style: TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          _buildTextField(
            controller: _operationController,
            hint: 'Ingresa el Código de Operación o N° de Confirmación *',
            icon: Icons.numbers,
            isRequired: false,
          ),
        ],
      ),
    );
  }

  Widget _buildCardDetails() {
    return Container(
      margin: const EdgeInsets.only(top: 8),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: const Color(0xFFE2E8F0))),
      child: const Center(
        child: Text('🔒 Procesamiento 100% encriptado SSL vía Culqi/MercadoPago al confirmar tu pedido.', textAlign: TextAlign.center, style: TextStyle(color: Color(0xFF64748B), fontSize: 12)),
      ),
    );
  }
}
