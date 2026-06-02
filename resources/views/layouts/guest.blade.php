<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="ObatKu - Platform manajemen obat keluarga Indonesia.">

    <title>@yield('title', 'ObatKu')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="min-h-screen bg-[#F8FAFF]">

    {{-- ─── Guest Layout Content ─── --}}
    @yield('content')

    <script>
        lucide.createIcons();
    </script>

    @stack('scripts')
</body>
</html>
