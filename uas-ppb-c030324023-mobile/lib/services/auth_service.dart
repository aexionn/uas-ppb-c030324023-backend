import 'package:dio/dio.dart';
import '../models/account.dart';

class AuthService {
  final Dio _dio;

  AuthService(this._dio);

  Future<String> login(String identifier, String password) async {
    final res = await _dio.post('/login', data: {
      'identifier': identifier,
      'password': password,
    });
    return res.data['token'] as String;
  }

  Future<String> register({
    required String nisn,
    required String username,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    final res = await _dio.post('/register', data: {
      'nisn': nisn,
      'username': username,
      'email': email,
      'password': password,
      'password_confirmation': passwordConfirmation,
    });
    return res.data['token'] as String;
  }

  Future<Account> getAccount() async {
    final res = await _dio.get('/me');
    return Account.fromJson(res.data);
  }

  Future<void> changePassword({
    required String currentPassword,
    required String newPassword,
    required String newPasswordConfirmation,
  }) async {
    await _dio.put('/me/password', data: {
      'current_password': currentPassword,
      'new_password': newPassword,
      'new_password_confirmation': newPasswordConfirmation,
    });
  }
}
