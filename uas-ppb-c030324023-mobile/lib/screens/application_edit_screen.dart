import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../providers/application_provider.dart';

const _incomeOptions = {
  '<1jt': 'Kurang dari 1 Juta',
  '1-3jt': '1 - 3 Juta',
  '3-5jt': '3 - 5 Juta',
  '>5jt': 'Lebih dari 5 Juta',
};

class ApplicationEditScreen extends StatefulWidget {
  const ApplicationEditScreen({super.key});

  @override
  State<ApplicationEditScreen> createState() => _ApplicationEditScreenState();
}

class _ApplicationEditScreenState extends State<ApplicationEditScreen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _fullNameCtrl;
  late final TextEditingController _birthPlaceCtrl;
  late final TextEditingController _addressCtrl;
  late final TextEditingController _phoneCtrl;
  late final TextEditingController _schoolCtrl;
  late final TextEditingController _fatherNameCtrl;
  late final TextEditingController _fatherJobCtrl;
  late final TextEditingController _motherNameCtrl;
  late final TextEditingController _motherJobCtrl;

  int? _programId;
  DateTime? _birthDate;
  String? _gender;
  String? _parentsIncome;
  XFile? _newPhoto;

  @override
  void initState() {
    super.initState();
    final a = context.read<ApplicationProvider>().application!;
    _fullNameCtrl = TextEditingController(text: a.fullName);
    _birthPlaceCtrl = TextEditingController(text: a.birthPlace);
    _addressCtrl = TextEditingController(text: a.address);
    _phoneCtrl = TextEditingController(text: a.phone);
    _schoolCtrl = TextEditingController(text: a.schoolOrigin);
    _fatherNameCtrl = TextEditingController(text: a.fatherName);
    _fatherJobCtrl = TextEditingController(text: a.fatherJob);
    _motherNameCtrl = TextEditingController(text: a.motherName);
    _motherJobCtrl = TextEditingController(text: a.motherJob);
    _programId = a.programId;
    _gender = a.gender;
    _parentsIncome = a.parentsIncome;
    _birthDate = DateTime.tryParse(a.birthDate);
  }

  @override
  void dispose() {
    _fullNameCtrl.dispose();
    _birthPlaceCtrl.dispose();
    _addressCtrl.dispose();
    _phoneCtrl.dispose();
    _schoolCtrl.dispose();
    _fatherNameCtrl.dispose();
    _fatherJobCtrl.dispose();
    _motherNameCtrl.dispose();
    _motherJobCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickPhoto() async {
    final picked =
        await ImagePicker().pickImage(source: ImageSource.gallery, imageQuality: 80);
    if (picked != null) setState(() => _newPhoto = picked);
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _birthDate ?? DateTime(2005),
      firstDate: DateTime(1980),
      lastDate: DateTime.now(),
    );
    if (picked != null) setState(() => _birthDate = picked);
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_birthDate == null) return;

    final app = context.read<ApplicationProvider>();
    final birthDate =
        '${_birthDate!.year}-${_birthDate!.month.toString().padLeft(2, '0')}-${_birthDate!.day.toString().padLeft(2, '0')}';

    final ok = await app.update(
      programId: _programId!,
      fullName: _fullNameCtrl.text.trim(),
      birthPlace: _birthPlaceCtrl.text.trim(),
      birthDate: birthDate,
      gender: _gender!,
      address: _addressCtrl.text.trim(),
      phone: _phoneCtrl.text.trim(),
      schoolOrigin: _schoolCtrl.text.trim(),
      fatherName: _fatherNameCtrl.text.trim(),
      fatherJob: _fatherJobCtrl.text.trim(),
      motherName: _motherNameCtrl.text.trim(),
      motherJob: _motherJobCtrl.text.trim(),
      parentsIncome: _parentsIncome!,
      photoPath: _newPhoto?.path,
    );

    if (ok && mounted) Navigator.pop(context);
  }

  String? _required(String? v) =>
      v == null || v.trim().isEmpty ? 'Wajib diisi' : null;

  @override
  Widget build(BuildContext context) {
    final appProvider = context.watch<ApplicationProvider>();
    final currentPhotoUrl = appProvider.application?.photoUrl;

    return Scaffold(
      appBar: AppBar(title: const Text('Edit Pendaftaran')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              if (appProvider.error != null)
                Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Text(appProvider.error!,
                      style: const TextStyle(color: Colors.red)),
                ),

              DropdownButtonFormField<int>(
                decoration: const InputDecoration(labelText: 'Program Studi'),
                initialValue: _programId,
                items: appProvider.programs
                    .map((p) => DropdownMenuItem(value: p.id, child: Text(p.name)))
                    .toList(),
                onChanged: (v) => setState(() => _programId = v),
                validator: (v) => v == null ? 'Pilih program studi' : null,
              ),
              const SizedBox(height: 8),

              TextFormField(
                controller: _fullNameCtrl,
                decoration: const InputDecoration(labelText: 'Nama Lengkap'),
                validator: _required,
              ),

              Row(
                children: [
                  Expanded(
                    child: TextFormField(
                      controller: _birthPlaceCtrl,
                      decoration:
                          const InputDecoration(labelText: 'Tempat Lahir'),
                      validator: _required,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: GestureDetector(
                      onTap: _pickDate,
                      child: InputDecorator(
                        decoration:
                            const InputDecoration(labelText: 'Tanggal Lahir'),
                        child: Text(
                          _birthDate == null
                              ? 'Pilih tanggal'
                              : '${_birthDate!.day}/${_birthDate!.month}/${_birthDate!.year}',
                        ),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),

              DropdownButtonFormField<String>(
                decoration: const InputDecoration(labelText: 'Jenis Kelamin'),
                initialValue: _gender,
                items: const [
                  DropdownMenuItem(value: 'L', child: Text('Laki-laki')),
                  DropdownMenuItem(value: 'P', child: Text('Perempuan')),
                ],
                onChanged: (v) => setState(() => _gender = v),
                validator: (v) => v == null ? 'Pilih jenis kelamin' : null,
              ),
              const SizedBox(height: 8),

              TextFormField(
                controller: _addressCtrl,
                decoration: const InputDecoration(labelText: 'Alamat'),
                maxLines: 2,
                validator: _required,
              ),
              TextFormField(
                controller: _phoneCtrl,
                decoration: const InputDecoration(labelText: 'No. HP'),
                keyboardType: TextInputType.phone,
                validator: _required,
              ),
              TextFormField(
                controller: _schoolCtrl,
                decoration: const InputDecoration(labelText: 'Asal Sekolah'),
                validator: _required,
              ),

              const Padding(
                padding: EdgeInsets.symmetric(vertical: 12),
                child: Divider(),
              ),
              const Text('Data Orang Tua',
                  style: TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),

              TextFormField(
                controller: _fatherNameCtrl,
                decoration: const InputDecoration(labelText: 'Nama Ayah'),
                validator: _required,
              ),
              TextFormField(
                controller: _fatherJobCtrl,
                decoration: const InputDecoration(labelText: 'Pekerjaan Ayah'),
                validator: _required,
              ),
              TextFormField(
                controller: _motherNameCtrl,
                decoration: const InputDecoration(labelText: 'Nama Ibu'),
                validator: _required,
              ),
              TextFormField(
                controller: _motherJobCtrl,
                decoration: const InputDecoration(labelText: 'Pekerjaan Ibu'),
                validator: _required,
              ),
              const SizedBox(height: 8),

              DropdownButtonFormField<String>(
                decoration:
                    const InputDecoration(labelText: 'Penghasilan Orang Tua'),
                initialValue: _parentsIncome,
                items: _incomeOptions.entries
                    .map((e) =>
                        DropdownMenuItem(value: e.key, child: Text(e.value)))
                    .toList(),
                onChanged: (v) => setState(() => _parentsIncome = v),
                validator: (v) => v == null ? 'Pilih rentang penghasilan' : null,
              ),

              const Padding(
                padding: EdgeInsets.symmetric(vertical: 12),
                child: Divider(),
              ),
              const Text('Foto', style: TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),

              if (_newPhoto != null)
                Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: Image.file(File(_newPhoto!.path),
                      height: 160, fit: BoxFit.cover),
                )
              else if (currentPhotoUrl != null)
                Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: Image.network(currentPhotoUrl,
                      height: 160, fit: BoxFit.cover),
                ),

              OutlinedButton.icon(
                onPressed: _pickPhoto,
                icon: const Icon(Icons.photo_library),
                label: Text(_newPhoto == null ? 'Ganti Foto (opsional)' : 'Ganti Lagi'),
              ),

              const SizedBox(height: 24),
              Consumer<ApplicationProvider>(
                builder: (context, app, _) => ElevatedButton(
                  onPressed: app.isLoading ? null : _submit,
                  child: app.isLoading
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(strokeWidth: 2))
                      : const Text('Simpan Perubahan'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
