@extends('layouts.app')

@section('title', 'Nowy użytkownik')

@section('content')
<h1 class="text-2xl font-semibold mb-4">Nowy użytkownik</h1>

<form method="POST" action="{{ route('admin.users.store') }}"
      class="bg-white p-6 rounded shadow space-y-4 max-w-xl">
    @csrf
    <div>
        <label class="block text-sm mb-1">Imię i nazwisko</label>
        <input name="name" required value="{{ old('name') }}" class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm mb-1">Email</label>
        <input type="email" name="email" required value="{{ old('email') }}" class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm mb-1">Hasło (min. 8 znaków)</label>
        <input type="password" name="password" required class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm mb-1">Rola</label>
        <select name="role" class="w-full border rounded px-3 py-2">
            @foreach ($roles as $role)
                <option value="{{ $role->value }}">{{ $role->label() }}</option>
            @endforeach
        </select>
    </div>
    <button class="bg-blue-600 text-white px-4 py-2 rounded">Utwórz</button>
</form>
@endsection
