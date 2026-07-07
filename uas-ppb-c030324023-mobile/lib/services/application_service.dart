import 'package:dio/dio.dart';
import '../models/application.dart';
import '../models/program.dart';

class ApplicationService {
  final Dio _dio;

  ApplicationService(this._dio);

  Future<Application?> getApplication() async {
    try {
      final res = await _dio.get('/application');
      return Application.fromJson(res.data);
    } on DioException catch (e) {
      if (e.response?.statusCode == 404) return null;
      rethrow;
    }
  }

  Future<List<Program>> getPrograms() async {
    final res = await _dio.get('/programs');
    return (res.data as List)
        .map((e) => Program.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<Application> submit({
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
    final formData = FormData.fromMap({
      'program_id': programId,
      'full_name': fullName,
      'birth_place': birthPlace,
      'birth_date': birthDate,
      'gender': gender,
      'address': address,
      'phone': phone,
      'school_origin': schoolOrigin,
      'father_name': fatherName,
      'father_job': fatherJob,
      'mother_name': motherName,
      'mother_job': motherJob,
      'parents_income': parentsIncome,
      'photo': await MultipartFile.fromFile(photoPath),
    });
    final res = await _dio.post('/application', data: formData);
    return Application.fromJson(res.data);
  }

  Future<Application> update({
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
    final map = <String, dynamic>{
      'program_id': programId,
      'full_name': fullName,
      'birth_place': birthPlace,
      'birth_date': birthDate,
      'gender': gender,
      'address': address,
      'phone': phone,
      'school_origin': schoolOrigin,
      'father_name': fatherName,
      'father_job': fatherJob,
      'mother_name': motherName,
      'mother_job': motherJob,
      'parents_income': parentsIncome,
    };
    if (photoPath != null) {
      map['photo'] = await MultipartFile.fromFile(photoPath);
    }
    final res = await _dio.put('/application', data: FormData.fromMap(map));
    return Application.fromJson(res.data);
  }
}
