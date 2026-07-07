import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

const String baseUrl = 'http://localhost:8000/api/v1'; // ponytail: 10.0.2.2 for Android emulator, LAN IP for physical device

Dio buildDio({
  required SharedPreferences prefs,
  required void Function() onUnauthorized,
}) {
  final dio = Dio(BaseOptions(baseUrl: baseUrl));

  // Attach token on every request
  dio.interceptors.add(InterceptorsWrapper(
    onRequest: (options, handler) {
      final token = prefs.getString('token');
      if (token != null) {
        options.headers['Authorization'] = 'Bearer $token';
      }
      handler.next(options);
    },
    onError: (error, handler) {
      if (error.response?.statusCode == 401) {
        prefs.remove('token');
        onUnauthorized();
      }
      handler.next(error);
    },
  ));

  return dio;
}
