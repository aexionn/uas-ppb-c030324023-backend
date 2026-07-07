class Account {
  final int id;
  final String role;
  final String? nisn;
  final String username;
  final String email;

  Account({
    required this.id,
    required this.role,
    this.nisn,
    required this.username,
    required this.email,
  });

  factory Account.fromJson(Map<String, dynamic> json) => Account(
        id: json['id'],
        role: json['role'],
        nisn: json['nisn'],
        username: json['username'],
        email: json['email'],
      );
}
