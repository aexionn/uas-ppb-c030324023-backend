class Application {
  final int id;
  final int programId;
  final String fullName;
  final String birthPlace;
  final String birthDate;
  final String gender;
  final String address;
  final String phone;
  final String schoolOrigin;
  final String fatherName;
  final String fatherJob;
  final String motherName;
  final String motherJob;
  final String parentsIncome;
  final String photoUrl;
  final String status;
  final int editsRemaining;
  final bool locked;
  final String? editableUntil;

  Application({
    required this.id,
    required this.programId,
    required this.fullName,
    required this.birthPlace,
    required this.birthDate,
    required this.gender,
    required this.address,
    required this.phone,
    required this.schoolOrigin,
    required this.fatherName,
    required this.fatherJob,
    required this.motherName,
    required this.motherJob,
    required this.parentsIncome,
    required this.photoUrl,
    required this.status,
    required this.editsRemaining,
    required this.locked,
    this.editableUntil,
  });

  factory Application.fromJson(Map<String, dynamic> json) => Application(
        id: json['id'],
        programId: json['program_id'],
        fullName: json['full_name'],
        birthPlace: json['birth_place'],
        birthDate: json['birth_date'],
        gender: json['gender'],
        address: json['address'],
        phone: json['phone'],
        schoolOrigin: json['school_origin'],
        fatherName: json['father_name'],
        fatherJob: json['father_job'],
        motherName: json['mother_name'],
        motherJob: json['mother_job'],
        parentsIncome: json['parents_income'],
        photoUrl: json['photo_url'],
        status: json['status'],
        editsRemaining: json['edits_remaining'],
        locked: json['locked'],
        editableUntil: json['editable_until'],
      );
}
