@extends('layouts.auth')

@section('content')
<h2 class="text-2xl font-semibold text-gray-800 mb-6">Create Account</h2>

@if ($errors->any())
    <div class="mb-4 bg-[#fde9e9] border-l-4 border-[#E24B4A] p-4 rounded-r-md">
        <ul class="list-disc list-inside text-sm text-[#b03333]">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('register') }}" class="space-y-4">
    @csrf

    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#185FA5]/20 focus:border-[#185FA5] transition-colors"
            placeholder="masukkan nama anda">
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#185FA5]/20 focus:border-[#185FA5] transition-colors"
            placeholder="nama@obatku.com">
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input id="password" type="password" name="password" required autocomplete="new-password"
            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#185FA5]/20 focus:border-[#185FA5] transition-colors"
            placeholder="masukkan password anda">
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#185FA5]/20 focus:border-[#185FA5] transition-colors"
            placeholder="masukkan ulang password anda">
    </div>

    <button type="submit" class="w-full mt-2 bg-[#185FA5] hover:bg-[#145294] text-white font-medium py-2.5 rounded-lg transition-colors shadow-sm shadow-[#185FA5]/30">
        Register
    </button>
</form>

<div class="mt-6 text-center">
    <p class="text-sm text-gray-600">
        Already have an account? 
        <a href="{{ route('login') }}" class="font-medium text-[#185FA5] hover:text-[#145294] transition-colors">Sign in here</a>
    </p>
</div>
@endsection
