import 'package:dio/dio.dart';

class ApiClient {
  static const String baseUrl = 'https://backend-production-1b91.up.railway.app/api';

  static final Dio _dio = Dio(
    BaseOptions(
      baseUrl: baseUrl,
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 15),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ),
  );

  static Dio get instance => _dio;

  // Add interceptors later for auth tokens
  static void setupInterceptors() {
    _dio.interceptors.add(LogInterceptor(responseBody: true, requestBody: true));
  }
}
