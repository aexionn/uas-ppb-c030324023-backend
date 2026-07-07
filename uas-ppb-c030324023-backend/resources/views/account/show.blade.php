@extends('layouts.app')
@section('title', 'Akun Saya')

@section('content')
<div class="max-w-lg mx-auto space-y-6">
    <h1 class="text-2xl font-bold">Akun Saya</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-xs font-semibold uppercase text-gray-400 mb-3">Informasi Akun</p>
        <dl class="space-y-2 text-sm">
            @if ($account->nisn)
                <div class="flex gap-2"><dt class="w-32 text-gray-500">NISN</dt><dd>{{ $account->nisn }}</dd></div>
            @endif
            <div class="flex gap-2"><dt class="w-32 text-gray-500">Username</dt><dd>{{ $account->username }}</dd></div>
            <div class="flex gap-2"><dt class="w-32 text-gray-500">Email</dt><dd>{{ $account->email }}</dd></div>
        </dl>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm font-semibold text-gray-700 mb-4">Ubah Kata Sandi</p>
        <form method="POST" action="{{ url('/account/password') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi Saat Ini</label>
                <input type="password" name="current_password" required
                    class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi Baru</label>
                <input type="password" name="new_password" required
                    class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md text-sm">
                Ubah Kata Sandi
            </button>
        </form>
    </div>

    <form method="POST" action="{{ url('/logout') }}">
        @csrf
        <button type="submit"
            class="w-full border border-red-300 text-red-600 hover:bg-red-50 font-medium py-2 px-4 rounded-md text-sm">
            Keluar
        </button>
    </form>
</div>
@endsection
