@extends('layouts.app')
@section('title', 'Ubah Pendaftaran')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-2">Ubah Pendaftaran</h1>

    <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-700 rounded-md px-4 py-3 text-sm flex items-center gap-2">
        <span>Sisa Perubahan: <strong>{{ $application->editsRemaining() }}x</strong></span>
        <span>&nbsp;·&nbsp; Waktu tersisa: <strong id="countdown">--:--</strong></span>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ url('/application') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Program Studi</label>
                <select name="program_id" required
                    class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}" @selected(old('program_id', $application->program_id) == $program->id)>{{ $program->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="full_name" value="{{ old('full_name', $application->full_name) }}" required
                    class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                    <input type="text" name="birth_place" value="{{ old('birth_place', $application->birth_place) }}" required
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', $application->birth_date->format('Y-m-d')) }}" required
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                <select name="gender" required
                    class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="L" @selected(old('gender', $application->gender) == 'L')>Laki-laki</option>
                    <option value="P" @selected(old('gender', $application->gender) == 'P')>Perempuan</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                <textarea name="address" rows="2" required
                    class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('address', $application->address) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $application->phone) }}" required
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Asal Sekolah</label>
                    <input type="text" name="school_origin" value="{{ old('school_origin', $application->school_origin) }}" required
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <hr class="border-gray-200">
            <p class="text-sm font-semibold text-gray-700">Data Orang Tua</p>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ayah</label>
                    <input type="text" name="father_name" value="{{ old('father_name', $application->father_name) }}" required
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan Ayah</label>
                    <input type="text" name="father_job" value="{{ old('father_job', $application->father_job) }}" required
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ibu</label>
                    <input type="text" name="mother_name" value="{{ old('mother_name', $application->mother_name) }}" required
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan Ibu</label>
                    <input type="text" name="mother_job" value="{{ old('mother_job', $application->mother_job) }}" required
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Penghasilan Gabungan Orang Tua</label>
                <select name="parents_income" required
                    class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="<1jt" @selected(old('parents_income', $application->parents_income) == '<1jt')>&lt; 1 juta</option>
                    <option value="1-3jt" @selected(old('parents_income', $application->parents_income) == '1-3jt')>1 - 3 juta</option>
                    <option value="3-5jt" @selected(old('parents_income', $application->parents_income) == '3-5jt')>3 - 5 juta</option>
                    <option value=">5jt" @selected(old('parents_income', $application->parents_income) == '>5jt')>&gt; 5 juta</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Foto <span class="text-gray-400">(kosongkan jika tidak diubah)</span></label>
                <input type="file" name="photo" accept="image/jpeg,image/png"
                    class="block w-full text-sm text-gray-600">
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md text-sm">
                Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<script>
    const deadline = new Date("{{ $application->editableUntil()->toIso8601String() }}").getTime();
    const el = document.getElementById('countdown');
    function tick() {
        const ms = deadline - Date.now();
        if (ms <= 0) { el.textContent = 'Waktu habis'; clearInterval(timer); return; }
        const m = Math.floor(ms / 60000);
        const s = Math.floor((ms % 60000) / 1000);
        el.textContent = m + ':' + String(s).padStart(2, '0');
    }
    tick();
    const timer = setInterval(tick, 1000);
</script>
@endsection
