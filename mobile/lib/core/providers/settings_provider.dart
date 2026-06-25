import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../network/api_client.dart';

final settingsProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final response = await ApiClient.instance.get('/storefront/settings');
  return response.data as Map<String, dynamic>;
});
