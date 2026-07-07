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

class ApplicationFormScreen extends StatefulWidget {
  const ApplicationFormScreen({super.key});

  @override
  State<ApplicationFormScreen> createState() => _ApplicationFormScreenState();
}

class _ApplicationFormScreenState extends State<ApplicationFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _fullNameCtrl = TextEditingController();
  final _birthPlaceCtrl = TextEditingController();
  final _addressCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _schoolCtrl = TextEditingController();
  final _fatherNameCtrl = TextEditingController();
  final _fatherJobCtrl = TextEditingController();
  final _motherNameCtrl = TextEditingController();
  final _motherJobCtrl = TextEditingController();

  int? _programId;
  DateTime? _birthDate;
  String? _gender;
  String? _parentsIncome;
  XFile? _photo;

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
    if (picked != null) setState(() => _photo = picked);
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime(2005),
      firstDate: DateTime(1980),
      lastDate: DateTime.now(),
    );
    if (picked != null) setState(() => _birthDate = picked);
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_photo == null) {
      ScaffoldMessenger.of(context)
          .showSnackBar(const SnackBar(content: Text('Pilih foto terlebih dahulu.')));
      return;
    }

    final app = context.read<ApplicationProvider>();
    final birthDate =
        '${_birthDate!.year}-${_birthDate!.month.toString().padLeft(2, '0')}-${_birthDate!.day.toString().padLeft(2, '0')}';

    final ok = await app.submit(
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
      photoPath: _photo!.path,
    );

    if (ok && mounted) Navigator.pop(context);
  }

  String? _required(String? v) =>
      v == null || v.trim().isEmpty ? 'Wajib diisi' : null;

  @override
  Widget build(BuildContext context) {
    final app = context.watch<ApplicationProvider>();

    return Scaffold(
      appBar: AppBar(title: const Text('Form Pendaftaran')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              if (app.error != null)
                Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Text(app.error!,
                      style: const TextStyle(color: Colors.red)),
                ),

              // Program
              DropdownButtonFormField<int>(
                decoration: const InputDecoration(labelText: 'Program Studi'),
                initialValue: _programId,
                items: app.programs
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

              // Birth place + date row
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
                        decoration: const InputDecoration(labelText: 'Tanggal Lahir'),
                        child: Text(
                          _birthDate == null
                              ? 'Pilih tanggal'
                              : '${_birthDate!.day}/${_birthDate!.month}/${_birthDate!.year}',
                          style: TextStyle(
                            color: _birthDate == null
                                ? Theme.of(context).hintColor
                                : null,
                          ),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
              if (_birthDate == null)
                Padding(
                  padding: const EdgeInsets.only(top: 4, left: 4),
                  child: Text('Pilih tanggal lahir',
                      style: TextStyle(
                          color: Theme.of(context).colorScheme.error,
                          fontSize: 12)),
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
                decoration: const InputDecoration(labelText: 'Penghasilan Orang Tua'),
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
              const Text('Foto',
                  style: TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),

              if (_photo != null)
                Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: Image.file(File(_photo!.path),
                      height: 160, fit: BoxFit.cover),
                ),
              OutlinedButton.icon(
                onPressed: _pickPhoto,
                icon: const Icon(Icons.photo_library),
                label:
                    Text(_photo == null ? 'Pilih Foto' : 'Ganti Foto'),
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
                      : const Text('Kirim Pendaftaran'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
