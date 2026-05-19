@extends('layouts.app')

@section('title', 'Użytkownicy')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-semibold">Użytkownicy</h1>
    <a href="{{ route('admin.users.create') }}" class="bg-green-600 text-white px-4 py-2 rounded">+ Dodaj</a>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-100 text-left">
            <tr>
                <th class="px-3 py-2">Imię</th>
                <th class="px-3 py-2">Email</th>
                <th class="px-3 py-2">Rola</th>
                <th class="px-3 py-2"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $u)
                <tr class="border-t">
                    <td class="px-3 py-2">{{ $u->name }}</td>
                    <td class="px-3 py-2">{{ $u->email }}</td>
                    <td class="px-3 py-2">{{ $u->role->label() }}</td>
                    <td class="px-3 py-2 text-right">
                        @if ($u->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                                  onsubmit="return confirm('Usunąć użytkownika?');" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Usuń</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $users->links() }}</div>
@endsection
