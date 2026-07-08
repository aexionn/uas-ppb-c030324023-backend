import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../providers/admin_application_provider.dart';
import '../models/application.dart';
import 'account_screen.dart';

class AdminHomeScreen extends StatefulWidget {
  const AdminHomeScreen({super.key});

  @override
  State<AdminHomeScreen> createState() => _AdminHomeScreenState();
}

class _AdminHomeScreenState extends State<AdminHomeScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance
        .addPostFrameCallback((_) => context.read<AdminApplicationProvider>().load());
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    
    return Scaffold(
      appBar: AppBar(
        title: Text('Admin Panel - ${auth.account?.username ?? ''}'),
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
      body: Consumer<AdminApplicationProvider>(
        builder: (context, adminProvider, _) {
          if (adminProvider.isLoading && adminProvider.applications.isEmpty) {
            return const Center(child: CircularProgressIndicator());
          }

          if (adminProvider.error != null && adminProvider.applications.isEmpty) {
            return Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(adminProvider.error!, style: const TextStyle(color: Colors.red)),
                  const SizedBox(height: 12),
                  ElevatedButton(
                    onPressed: adminProvider.load,
                    child: const Text('Coba Lagi'),
                  ),
                ],
              ),
            );
          }

          if (adminProvider.applications.isEmpty) {
            return const Center(child: Text('Belum ada pendaftaran.'));
          }

          return RefreshIndicator(
            onRefresh: adminProvider.load,
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: adminProvider.applications.length,
              itemBuilder: (context, index) {
                final app = adminProvider.applications[index];
                return Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  child: ListTile(
                    title: Text(app.fullName, style: const TextStyle(fontWeight: FontWeight.bold)),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Program: ${app.program?.name ?? '-'}'),
                        Text('NISN: ${app.account?.nisn ?? '-'}'),
                      ],
                    ),
                    trailing: _statusChip(app.status),
                    onTap: () {
                      _showVerdictDialog(context, app, adminProvider);
                    },
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }

  Widget _statusChip(String status) {
    Color color;
    String label;
    switch (status) {
      case 'accepted':
        color = Colors.green;
        label = 'Diterima';
        break;
      case 'rejected':
        color = Colors.red;
        label = 'Ditolak';
        break;
      default:
        color = Colors.orange;
        label = 'Terkirim';
    }
    return Chip(
      label: Text(label, style: const TextStyle(color: Colors.white, fontSize: 12)),
      backgroundColor: color,
    );
  }

  void _showVerdictDialog(BuildContext context, Application application, AdminApplicationProvider provider) {
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: Text('Aksi untuk ${application.fullName}'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Program: ${application.program?.name}'),
              Text('Asal Sekolah: ${application.schoolOrigin}'),
              Text('Status Saat Ini: ${application.status}'),
              const SizedBox(height: 16),
              const Text('Apakah Anda ingin mengubah status pendaftaran ini?'),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Batal'),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
              onPressed: () async {
                final ok = await provider.updateVerdict(application.id, 'rejected');
                if (ok && context.mounted) Navigator.pop(context);
              },
              child: const Text('Tolak', style: TextStyle(color: Colors.white)),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
              onPressed: () async {
                final ok = await provider.updateVerdict(application.id, 'accepted');
                if (ok && context.mounted) Navigator.pop(context);
              },
              child: const Text('Terima', style: TextStyle(color: Colors.white)),
            ),
          ],
        );
      },
    );
  }
}
