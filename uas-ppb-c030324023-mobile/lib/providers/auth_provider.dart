import 'package:dio/dio.dart';
import 'package:flutter/widgets.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/account.dart';
import '../services/auth_service.dart';

class AuthProvider extends ChangeNotifier {
  final AuthService _service;
  final SharedPreferences _prefs;

  Account? account;
  bool isLoading = false;
  String? error;

  AuthProvider(this._service, this._prefs);

  bool get isLoggedIn => _prefs.getString('token') != null && account != null;

  Future<void> restore() async {
    final token = _prefs.getString('token');
    if (token == null) return;
    try {
      account = await _service.getAccount();
    } catch (_) {
      await _prefs.remove('token');
    }
    notifyListeners();
  }

  Future<bool> login(String identifier, String password) async {
    isLoading = true;
    error = null;
    notifyListeners();
    try {
      final token = await _service.login(identifier, password);
      await _prefs.setString('token', token);
      account = await _service.getAccount();
      return true;
    } on DioException catch (e) {
      error = e.response?.data?['message'] as String? ?? 'Gagal terhubung ke server';
      return false;
    } catch (e) {
      error = 'Terjadi kesalahan: $e';
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> register({
    required String nisn,
    required String username,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    isLoading = true;
    error = null;
    notifyListeners();
    try {
      final token = await _service.register(
        nisn: nisn,
        username: username,
        email: email,
        password: password,
        passwordConfirmation: passwordConfirmation,
      );
      await _prefs.setString('token', token);
      account = await _service.getAccount();
      return true;
    } on DioException catch (e) {
      error = e.response?.data?['message'] as String? ?? 'Gagal terhubung ke server';
      return false;
    } catch (e) {
      error = 'Terjadi kesalahan: $e';
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> changePassword({
    required String currentPassword,
    required String newPassword,
    required String newPasswordConfirmation,
  }) async {
    isLoading = true;
    error = null;
    notifyListeners();
    try {
      await _service.changePassword(
        currentPassword: currentPassword,
        newPassword: newPassword,
        newPasswordConfirmation: newPasswordConfirmation,
      );
      return true;
    } on DioException catch (e) {
      error = e.response?.data?['message'] as String? ?? 'Gagal mengubah password';
      return false;
    } catch (e) {
      error = 'Terjadi kesalahan: $e';
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    await _prefs.remove('token');
    account = null;
    notifyListeners();
  }
}
