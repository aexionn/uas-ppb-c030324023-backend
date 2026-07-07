import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/application_provider.dart';
import 'application_edit_screen.dart';

class ApplicationDetailScreen extends StatefulWidget {
  const ApplicationDetailScreen({super.key});

  @override
  State<ApplicationDetailScreen> createState() =>
      _ApplicationDetailScreenState();
}

class _ApplicationDetailScreenState extends State<ApplicationDetailScreen> {
  Timer? _timer;
  Duration _remaining = Duration.zero;

  @override
  void initState() {
    super.initState();
    _startTimer();
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  void _startTimer() {
    _timer = Timer.periodic(const Duration(seconds: 1), (_) {
      if (!mounted) return;
      final app = context.read<ApplicationProvider>().application;
      if (app?.editableUntil == null) return;
      final until = DateTime.parse(app!.editableUntil!);
      final diff = until.difference(DateTime.now());
      setState(() => _remaining = diff.isNegative ? Duration.zero : diff);
    });
  }

  String _formatCountdown(Duration d) {
    final m = d.inMinutes.remainder(60).toString().padLeft(2, '0');
    final s = d.inSeconds.remainder(60).toString().padLeft(2, '0');
    return '$m:$s';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Detail Pendaftaran')),
      body: Consumer<ApplicationProvider>(
        builder: (context, appProvider, _) {
          final a = appProvider.application;
          if (a == null) {
            return const Center(child: Text('Data tidak ditemukan.'));
          }

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Status banner
                _StatusBanner(status: a.status),
                const SizedBox(height: 16),

                // Countdown / lock notice
                if (!a.locked && a.editableUntil != null)
                  Card(
                    color: Theme.of(context).colorScheme.primaryContainer,
                    child: Padding(
                      padding: const EdgeInsets.all(12),
                      child: Row(
                        children: [
                          const Icon(Icons.timer_outlined),
                          const SizedBox(width: 8),
                          Text(
                            'Bisa diedit: ${_formatCountdown(_remaining)} '
                            '(${a.editsRemaining}x tersisa)',
                          ),
                        ],
                      ),
                    ),
                  )
                else if (a.locked)
                  Card(
                    color: Theme.of(context).colorScheme.errorContainer,
                    child: const Padding(
                      padding: EdgeInsets.all(12),
                      child: Row(
                        children: [
                          Icon(Icons.lock_outline),
                          SizedBox(width: 8),
                          Text('Pendaftaran terkunci'),
                        ],
                      ),
                    ),
                  ),
                const SizedBox(height: 16),

                // Photo
                ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: Image.network(
                    a.photoUrl,
                    height: 180,
                    fit: BoxFit.cover,
                    errorBuilder: (context, error, _) =>
                        const SizedBox(height: 180, child: Icon(Icons.broken_image)),
                  ),
                ),
                const SizedBox(height: 16),

                _Section(title: 'Data Pribadi', rows: [
                  _Field('Nama Lengkap', a.fullName),
                  _Field('Tempat Lahir', a.birthPlace),
                  _Field('Tanggal Lahir', a.birthDate),
                  _Field('Jenis Kelamin', a.gender == 'L' ? 'Laki-laki' : 'Perempuan'),
                  _Field('Alamat', a.address),
                  _Field('No. HP', a.phone),
                  _Field('Asal Sekolah', a.schoolOrigin),
                ]),
                const SizedBox(height: 12),

                _Section(title: 'Data Orang Tua', rows: [
                  _Field('Nama Ayah', a.fatherName),
                  _Field('Pekerjaan Ayah', a.fatherJob),
                  _Field('Nama Ibu', a.motherName),
                  _Field('Pekerjaan Ibu', a.motherJob),
                  _Field('Penghasilan', a.parentsIncome),
                ]),
                const SizedBox(height: 24),

                if (!a.locked)
                  ElevatedButton.icon(
                    icon: const Icon(Icons.edit),
                    label: const Text('Edit Pendaftaran'),
                    onPressed: () => Navigator.push(
                      context,
                      MaterialPageRoute(
                          builder: (_) => const ApplicationEditScreen()),
                    ),
                  ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _StatusBanner extends StatelessWidget {
  final String status;
  const _StatusBanner({required this.status});

  @override
  Widget build(BuildContext context) {
    final (label, color) = switch (status) {
      'accepted' => ('Diterima', Colors.green),
      'rejected' => ('Ditolak', Colors.red),
      _ => ('Terkirim', Colors.blue),
    };
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 12),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color),
      ),
      child: Text('Status: $label',
          style: TextStyle(color: color, fontWeight: FontWeight.bold)),
    );
  }
}

class _Section extends StatelessWidget {
  final String title;
  final List<_Field> rows;
  const _Section({required this.title, required this.rows});

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: const TextStyle(fontWeight: FontWeight.bold)),
            const Divider(),
            ...rows,
          ],
        ),
      ),
    );
  }
}

class _Field extends StatelessWidget {
  final String label;
  final String value;
  const _Field(this.label, this.value);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(label,
                style: const TextStyle(
                    fontWeight: FontWeight.w500, color: Colors.black54)),
          ),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }
}
