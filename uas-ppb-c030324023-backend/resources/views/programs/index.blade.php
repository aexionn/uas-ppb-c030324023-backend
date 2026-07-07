@extends('layouts.app')
@section('title', 'Program Studi')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Program Studi</h1>
        <a href="{{ url('/programs/create') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-md">
            + Tambah
        </a>
    </div>

    <div class="bg-white rounded-lg shadow divide-y divide-gray-100">
        @forelse ($programs as $program)
            <div class="flex items-center justify-between px-4 py-3">
                <span class="text-sm">{{ $program->name }}</span>
                <div class="flex gap-2">
                    <a href="{{ url('/programs/'.$program->id.'/edit') }}"
                        class="text-xs text-blue-600 hover:underline">Ubah</a>
                    <form method="POST" action="{{ url('/programs/'.$program->id) }}"
                        onsubmit="return confirm('Hapus program ini?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="px-4 py-6 text-center text-sm text-gray-400">Belum ada program.</p>
        @endforelse
    </div>
</div>
@endsection
