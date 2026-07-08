import 'package:flutter/widgets.dart';
import '../models/application.dart';
import '../services/application_service.dart';

class AdminApplicationProvider extends ChangeNotifier {
  final ApplicationService _service;

  List<Application> applications = [];
  bool isLoading = false;
  String? error;

  AdminApplicationProvider(this._service);

  Future<void> load() async {
    isLoading = true;
    error = null;
    notifyListeners();

    try {
      applications = await _service.getAllApplications();
    } catch (e) {
      error = e.toString();
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> updateVerdict(int id, String status) async {
    isLoading = true;
    error = null;
    notifyListeners();

    try {
      final updated = await _service.updateVerdict(id, status);
      final index = applications.indexWhere((a) => a.id == id);
      if (index != -1) {
        applications[index] = updated;
      }
      return true;
    } catch (e) {
      error = e.toString();
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}
