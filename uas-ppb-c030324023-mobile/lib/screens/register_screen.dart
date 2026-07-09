import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import 'home_screen.dart';
import 'admin_home_screen.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _nisnCtrl = TextEditingController();
  final _usernameCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  final _passConfCtrl = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  @override
  void dispose() {
    _nisnCtrl.dispose();
    _usernameCtrl.dispose();
    _emailCtrl.dispose();
    _passCtrl.dispose();
    _passConfCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    final auth = context.read<AuthProvider>();
    final ok = await auth.register(
      nisn: _nisnCtrl.text.trim(),
      username: _usernameCtrl.text.trim(),
      email: _emailCtrl.text.trim(),
      password: _passCtrl.text,
      passwordConfirmation: _passConfCtrl.text,
    );
    if (ok && mounted) {
      if (auth.account?.role == 'admin') {
        Navigator.pushAndRemoveUntil(
          context,
          MaterialPageRoute(builder: (_) => const AdminHomeScreen()),
          (_) => false,
        );
      } else {
        Navigator.pushAndRemoveUntil(
          context,
          MaterialPageRoute(builder: (_) => const HomeScreen()),
          (_) => false,
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Daftar Akun')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              Consumer<AuthProvider>(
                builder: (context, auth, _) => auth.error != null
                    ? Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: Text(auth.error!, style: const TextStyle(color: Colors.red)),
                      )
                    : const SizedBox.shrink(),
              ),
              TextFormField(
                controller: _nisnCtrl,
                decoration: const InputDecoration(labelText: 'NISN'),
                keyboardType: TextInputType.number,
                validator: (v) => v == null || v.isEmpty ? 'Wajib diisi' : null,
              ),
              TextFormField(
                controller: _usernameCtrl,
                decoration: const InputDecoration(labelText: 'Username'),
                validator: (v) => v == null || v.isEmpty ? 'Wajib diisi' : null,
              ),
              TextFormField(
                controller: _emailCtrl,
                decoration: const InputDecoration(labelText: 'Email'),
                keyboardType: TextInputType.emailAddress,
                validator: (v) => v == null || v.isEmpty ? 'Wajib diisi' : null,
              ),
              TextFormField(
                controller: _passCtrl,
                decoration: const InputDecoration(labelText: 'Password'),
                obscureText: true,
                validator: (v) => v == null || v.isEmpty ? 'Wajib diisi' : null,
              ),
              TextFormField(
                controller: _passConfCtrl,
                decoration: const InputDecoration(labelText: 'Konfirmasi Password'),
                obscureText: true,
                validator: (v) => v == null || v.isEmpty ? 'Wajib diisi' : null,
              ),
              const SizedBox(height: 16),
              Consumer<AuthProvider>(
                builder: (context, auth, _) => ElevatedButton(
                  onPressed: auth.isLoading ? null : _submit,
                  child: auth.isLoading
                      ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))
                      : const Text('Daftar'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
