@extends('layouts.app')
@section('title', 'Daftar Pendaftaran')

@section('content')
<div>
    <h1 class="text-2xl font-bold mb-6">Daftar Pendaftaran</h1>

    <form method="GET" action="{{ url('/admin/applications') }}" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" placeholder="Cari nama atau NISN" value="{{ $filters['search'] ?? '' }}"
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="status"
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="submitted" @selected(($filters['status'] ?? '') === 'submitted')>Submitted</option>
            <option value="accepted" @selected(($filters['status'] ?? '') === 'accepted')>Accepted</option>
            <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Rejected</option>
        </select>
        <select name="program_id"
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Program</option>
            @foreach ($programs as $program)
                <option value="{{ $program->id }}" @selected(($filters['program_id'] ?? '') == $program->id)>{{ $program->name }}</option>
            @endforeach
        </select>
        <button type="submit"
            class="bg-gray-700 hover:bg-gray-800 text-white font-medium py-2 px-4 rounded-md text-sm">
            Filter
        </button>
    </form>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Nama</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">NISN</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Program</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($applications as $application)
                    @php
                        $badge = match($application->status) {
                            'accepted' => 'bg-green-100 text-green-700',
                            'rejected' => 'bg-red-100 text-red-700',
                            default => 'bg-blue-100 text-blue-700',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $application->full_name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $application->account->nisn }}</td>
                        <td class="px-4 py-3">{{ $application->program->name }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                {{ $application->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ url('/admin/applications/'.$application->id) }}"
                                class="text-blue-600 hover:underline text-xs">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
