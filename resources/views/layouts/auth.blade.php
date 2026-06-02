<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ObatKu') }} - Authentication</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts / Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-[#F8FAFF] min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        <!-- Logo Header -->
        <div class="flex flex-col items-center mb-8">
            <div class="w-16 h-16 bg-[#185FA5] rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-[#185FA5]/20">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-[#042C53]">ObatKu</h1>
            <p class="text-gray-500 mt-1">Healthcare Management System</p>
        </div>

        <!-- Auth Card -->
        <div class="bg-white rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 border border-gray-100">
            @yield('content')
        </div>

        <div class="mt-8 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} ObatKu Healthcare. All rights reserved.
        </div>
    </div>

</body>
</html>
