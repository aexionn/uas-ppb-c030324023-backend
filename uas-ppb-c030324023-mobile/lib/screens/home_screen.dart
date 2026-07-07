import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../providers/application_provider.dart';
import 'account_screen.dart';
import 'application_detail_screen.dart';
import 'application_form_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance
        .addPostFrameCallback((_) => context.read<ApplicationProvider>().load());
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    return Scaffold(
      appBar: AppBar(
        title: Text('Halo, ${auth.account?.username ?? ''}'),
        actions: [
          IconButton(
            icon: const Icon(Icons.person_outline),
            onPressed: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const AccountScreen()),
            ),
          ),
        ],
      ),
      body: Consumer<ApplicationProvider>(
        builder: (context, app, _) {
          if (app.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          if (app.error != null && app.application == null) {
            return Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(app.error!, style: const TextStyle(color: Colors.red)),
                  const SizedBox(height: 12),
                  ElevatedButton(
                    onPressed: app.load,
                    child: const Text('Coba Lagi'),
                  ),
                ],
              ),
            );
          }

          if (app.application == null) {
            return Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Text('Anda belum mendaftar.'),
                  const SizedBox(height: 12),
                  ElevatedButton(
                    onPressed: () => Navigator.push(
                      context,
                      MaterialPageRoute(
                          builder: (_) => const ApplicationFormScreen()),
                    ),
                    child: const Text('Daftar Sekarang'),
                  ),
                ],
              ),
            );
          }

          final a = app.application!;
          return Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              mainAxisSize: MainAxisSize.min,
              children: [
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(a.fullName,
                            style: Theme.of(context).textTheme.titleLarge),
                        const SizedBox(height: 8),
                        _row('Status', _statusLabel(a.status)),
                        _row('Sisa Edit', '${a.editsRemaining}x'),
                        _row('Terkunci', a.locked ? 'Ya' : 'Tidak'),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                ElevatedButton(
                  onPressed: () => Navigator.push(
                    context,
                    MaterialPageRoute(
                        builder: (_) => const ApplicationDetailScreen()),
                  ),
                  child: const Text('Lihat Detail'),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _row(String label, String value) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 2),
        child: Row(
          children: [
            SizedBox(
                width: 100,
                child: Text(label,
                    style: const TextStyle(fontWeight: FontWeight.w500))),
            Text(value),
          ],
        ),
      );

  String _statusLabel(String status) => switch (status) {
        'submitted' => 'Terkirim',
        'accepted' => 'Diterima',
        'rejected' => 'Ditolak',
        _ => status,
      };
}
