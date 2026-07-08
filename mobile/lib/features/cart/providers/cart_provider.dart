import 'package:flutter_riverpod/flutter_riverpod.dart';

class CartItem {
  final Map<String, dynamic> product;
  int quantity;

  CartItem({required this.product, this.quantity = 1});

  double get totalPrice {
    final priceStr = product['price'].toString();
    final price = double.tryParse(priceStr) ?? 0.0;
    return price * quantity;
  }
}

class CartNotifier extends Notifier<List<CartItem>> {
  @override
  List<CartItem> build() {
    return [];
  }

  void addItem(Map<String, dynamic> product, {int quantity = 1}) {
    final existingIndex = state.indexWhere((item) => item.product['id'] == product['id']);
    
    if (existingIndex >= 0) {
      final newState = [...state];
      newState[existingIndex] = CartItem(
        product: product, 
        quantity: newState[existingIndex].quantity + quantity,
      );
      state = newState;
    } else {
      state = [...state, CartItem(product: product, quantity: quantity)];
    }
  }

  void removeItem(int productId) {
    state = state.where((item) => item.product['id'] != productId).toList();
  }

  void updateQuantity(int productId, int quantity) {
    if (quantity <= 0) {
      removeItem(productId);
      return;
    }
    
    final newState = [...state];
    final index = newState.indexWhere((item) => item.product['id'] == productId);
    
    if (index >= 0) {
      newState[index] = CartItem(product: newState[index].product, quantity: quantity);
      state = newState;
    }
  }
  
  void clearCart() {
    state = [];
  }
}

final cartProvider = NotifierProvider<CartNotifier, List<CartItem>>(() {
  return CartNotifier();
});

final cartTotalProvider = Provider<double>((ref) {
  final cartItems = ref.watch(cartProvider);
  return cartItems.fold(0.0, (sum, item) => sum + item.totalPrice);
});

final cartCountProvider = Provider<int>((ref) {
  final cartItems = ref.watch(cartProvider);
  return cartItems.fold(0, (sum, item) => sum + item.quantity);
});
