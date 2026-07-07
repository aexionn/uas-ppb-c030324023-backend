import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../models/application.dart';
import '../models/program.dart';
import '../services/application_service.dart';

class ApplicationProvider extends ChangeNotifier {
  final ApplicationService _service;

  ApplicationProvider(this._service);

  Application? application;
  List<Program> programs = [];
  bool isLoading = false;
  String? error;

  Future<void> load() async {
    isLoading = true;
    error = null;
    notifyListeners();
    try {
      application = await _service.getApplication();
      programs = await _service.getPrograms();
    } on DioException catch (e) {
      error = e.response?.data?['message'] ?? 'Gagal memuat data.';
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> submit({
    required int programId,
    required String fullName,
    required String birthPlace,
    required String birthDate,
    required String gender,
    required String address,
    required String phone,
    required String schoolOrigin,
    required String fatherName,
    required String fatherJob,
    required String motherName,
    required String motherJob,
    required String parentsIncome,
    required String photoPath,
  }) async {
    isLoading = true;
    error = null;
    notifyListeners();
    try {
      application = await _service.submit(
        programId: programId,
        fullName: fullName,
        birthPlace: birthPlace,
        birthDate: birthDate,
        gender: gender,
        address: address,
        phone: phone,
        schoolOrigin: schoolOrigin,
        fatherName: fatherName,
        fatherJob: fatherJob,
        motherName: motherName,
        motherJob: motherJob,
        parentsIncome: parentsIncome,
        photoPath: photoPath,
      );
      return true;
    } on DioException catch (e) {
      error = e.response?.data?['message'] ?? 'Gagal mengirim pendaftaran.';
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> update({
    required int programId,
    required String fullName,
    required String birthPlace,
    required String birthDate,
    required String gender,
    required String address,
    required String phone,
    required String schoolOrigin,
    required String fatherName,
    required String fatherJob,
    required String motherName,
    required String motherJob,
    required String parentsIncome,
    String? photoPath,
  }) async {
    isLoading = true;
    error = null;
    notifyListeners();
    try {
      application = await _service.update(
        programId: programId,
        fullName: fullName,
        birthPlace: birthPlace,
        birthDate: birthDate,
        gender: gender,
        address: address,
        phone: phone,
        schoolOrigin: schoolOrigin,
        fatherName: fatherName,
        fatherJob: fatherJob,
        motherName: motherName,
        motherJob: motherJob,
        parentsIncome: parentsIncome,
        photoPath: photoPath,
      );
      return true;
    } on DioException catch (e) {
      error = e.response?.data?['message'] ?? 'Gagal menyimpan perubahan.';
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}
