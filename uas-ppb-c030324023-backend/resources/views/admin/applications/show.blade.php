@extends('layouts.app')
@section('title', 'Detail Pendaftaran')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Detail Pendaftaran</h1>
        <a href="{{ url('/admin/applications') }}" class="text-sm text-gray-500 hover:text-blue-600">&larr; Kembali</a>
    </div>

    @php
        $statusColor = match($application->status) {
            'accepted' => 'bg-green-50 border-green-200 text-green-700',
            'rejected' => 'bg-red-50 border-red-200 text-red-700',
            default => 'bg-blue-50 border-blue-200 text-blue-700',
        };
    @endphp
    <div class="mb-4 border rounded-md px-4 py-3 text-sm font-medium {{ $statusColor }}">
        Status: {{ $application->status }}
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden mb-4">
        <div class="px-6 py-4">
            <p class="text-xs font-semibold uppercase text-gray-400 mb-3">Data Pribadi</p>
            <dl class="space-y-2 text-sm">
                <div class="flex gap-2"><dt class="w-44 text-gray-500">NISN</dt><dd>{{ $application->account->nisn }}</dd></div>
                <div class="flex gap-2"><dt class="w-44 text-gray-500">Program Studi</dt><dd>{{ $application->program->name }}</dd></div>
                <div class="flex gap-2"><dt class="w-44 text-gray-500">Nama Lengkap</dt><dd>{{ $application->full_name }}</dd></div>
                <div class="flex gap-2"><dt class="w-44 text-gray-500">Tempat, Tgl Lahir</dt><dd>{{ $application->birth_place }}, {{ $application->birth_date->format('d-m-Y') }}</dd></div>
                <div class="flex gap-2"><dt class="w-44 text-gray-500">Jenis Kelamin</dt><dd>{{ $application->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd></div>
                <div class="flex gap-2"><dt class="w-44 text-gray-500">Alamat</dt><dd>{{ $application->address }}</dd></div>
                <div class="flex gap-2"><dt class="w-44 text-gray-500">No. Telepon</dt><dd>{{ $application->phone }}</dd></div>
                <div class="flex gap-2"><dt class="w-44 text-gray-500">Asal Sekolah</dt><dd>{{ $application->school_origin }}</dd></div>
            </dl>
        </div>
        <div class="border-t border-gray-100 px-6 py-4">
            <p class="text-xs font-semibold uppercase text-gray-400 mb-3">Data Orang Tua</p>
            <dl class="space-y-2 text-sm">
                <div class="flex gap-2"><dt class="w-44 text-gray-500">Nama Ayah</dt><dd>{{ $application->father_name }}</dd></div>
                <div class="flex gap-2"><dt class="w-44 text-gray-500">Pekerjaan Ayah</dt><dd>{{ $application->father_job }}</dd></div>
                <div class="flex gap-2"><dt class="w-44 text-gray-500">Nama Ibu</dt><dd>{{ $application->mother_name }}</dd></div>
                <div class="flex gap-2"><dt class="w-44 text-gray-500">Pekerjaan Ibu</dt><dd>{{ $application->mother_job }}</dd></div>
                <div class="flex gap-2"><dt class="w-44 text-gray-500">Penghasilan</dt><dd>{{ $application->parents_income }}</dd></div>
            </dl>
        </div>
        <div class="border-t border-gray-100 px-6 py-4">
            <p class="text-xs font-semibold uppercase text-gray-400 mb-3">Foto</p>
            <img src="{{ asset('storage/'.$application->photo_path) }}" alt="Foto"
                class="rounded-md w-40 h-40 object-cover">
        </div>
    </div>

    <div class="flex flex-wrap gap-3">
        <form method="POST" action="{{ url('/admin/applications/'.$application->id.'/verdict') }}" class="flex gap-2">
            @csrf
            <button type="submit" name="status" value="accepted"
                class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md text-sm">
                Terima
            </button>
            <button type="submit" name="status" value="rejected"
                class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md text-sm">
                Tolak
            </button>
        </form>
        <a href="{{ url('/admin/applications/'.$application->id.'/edit') }}"
            class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-md text-sm">
            Edit
        </a>
        <form method="POST" action="{{ url('/admin/applications/'.$application->id) }}"
            onsubmit="return confirm('Hapus pendaftaran ini?');">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="border border-red-300 text-red-600 hover:bg-red-50 font-medium py-2 px-4 rounded-md text-sm">
                Hapus
            </button>
        </form>
    </div>
</div>
@endsection
