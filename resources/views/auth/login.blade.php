@extends('layouts.auth')

@section('content')
<h2 class="text-2xl font-semibold text-gray-800 mb-6">Sign In</h2>

@if ($errors->any())
    <div class="mb-4 bg-[#fde9e9] border-l-4 border-[#E24B4A] p-4 rounded-r-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-[#E24B4A]" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-[#b03333]">
                    {{ $errors->first() }}
                </p>
            </div>
        </div>
    </div>
@endif

<form method="POST" action="{{ route('login') }}" class="space-y-5">
    @csrf

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#185FA5]/20 focus:border-[#185FA5] transition-colors"
            placeholder="masukkan email anda">
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input id="password" type="password" name="password" required autocomplete="current-password"
            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#185FA5]/20 focus:border-[#185FA5] transition-colors"
            placeholder="masukkan password anda">
    </div>

    <div class="flex items-center justify-between">
        <label for="remember" class="flex items-center">
            <input id="remember" type="checkbox" name="remember" class="w-4 h-4 text-[#185FA5] border-gray-300 rounded focus:ring-[#185FA5]">
            <span class="ml-2 text-sm text-gray-600">Remember me</span>
        </label>
    </div>

    <button type="submit" class="w-full bg-[#185FA5] hover:bg-[#145294] text-white font-medium py-2.5 rounded-lg transition-colors shadow-sm shadow-[#185FA5]/30">
        Sign In
    </button>
</form>

<div class="mt-6 text-center">
    <p class="text-sm text-gray-600">
        Don't have an account? 
        <a href="{{ route('register') }}" class="font-medium text-[#185FA5] hover:text-[#145294] transition-colors">Create account</a>
    </p>
</div>
@endsection
