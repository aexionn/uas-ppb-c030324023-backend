@extends('layouts.app')
@section('title', ($program->exists ? 'Ubah' : 'Tambah') . ' Program')

@section('content')
<div class="max-w-sm mx-auto">
    <h1 class="text-2xl font-bold mb-6">{{ $program->exists ? 'Ubah' : 'Tambah' }} Program</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ $program->exists ? url('/programs/'.$program->id) : url('/programs') }}"
            class="space-y-4">
            @csrf
            @if ($program->exists) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Program</label>
                <input type="text" name="name" value="{{ old('name', $program->name) }}" required
                    class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md text-sm">
                    Simpan
                </button>
                <a href="{{ url('/programs') }}"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-md text-sm">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
