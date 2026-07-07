<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PPDB')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">

<nav class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="{{ url('/') }}" class="font-bold text-blue-600 text-lg">PPDB</a>
        <div class="flex items-center gap-4 text-sm">
            @auth
                @if(auth()->user()->role === 'admin')
                    <a href="{{ url('/admin/applications') }}" class="text-gray-600 hover:text-blue-600">Pendaftaran</a>
                    <a href="{{ url('/programs') }}" class="text-gray-600 hover:text-blue-600">Program Studi</a>
                @else
                    <a href="{{ url('/application') }}" class="text-gray-600 hover:text-blue-600">Pendaftaran Saya</a>
                @endif
                <a href="{{ url('/account') }}" class="text-gray-600 hover:text-blue-600">Akun</a>
                <form method="POST" action="{{ url('/logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-gray-600 hover:text-red-600">Keluar</button>
                </form>
            @else
                <a href="{{ url('/login') }}" class="text-gray-600 hover:text-blue-600">Masuk</a>
                <a href="{{ url('/register') }}" class="bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700">Daftar</a>
            @endauth
        </div>
    </div>
</nav>

<main class="max-w-4xl mx-auto px-4 py-8">
    @if(session('status'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 rounded-md px-4 py-3 text-sm">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-md px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

</body>
</html>
