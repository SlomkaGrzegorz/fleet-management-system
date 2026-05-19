@extends('layouts.app')

@section('title', 'Logowanie')

@section('content')
<div class="max-w-md mx-auto mt-12 bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-semibold mb-4">Logowanie</h1>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm mb-1">Hasło</label>
            <input type="password" name="password" required class="w-full border rounded px-3 py-2">
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" value="1"> Zapamiętaj mnie
        </label>
        <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Zaloguj</button>
    </form>
</div>
@endsection
