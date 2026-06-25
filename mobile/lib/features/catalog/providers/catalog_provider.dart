import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/network/api_client.dart';

final productsProvider = FutureProvider.autoDispose<List<dynamic>>((ref) async {
  final response = await ApiClient.instance.get('/catalog/products');
  // La respuesta es paginada: response.data['data'] contiene la lista
  return response.data['data'] as List<dynamic>;
});
