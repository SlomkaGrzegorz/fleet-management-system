@extends('layouts.app')

@section('title', 'Moje zgłoszenia')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-semibold">Moje zgłoszenia</h1>
    <a href="{{ route('driver.incidents.create') }}" class="bg-red-600 text-white px-4 py-2 rounded">+ Nowe zgłoszenie</a>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-100 text-left">
            <tr>
                <th class="px-3 py-2">Data</th>
                <th class="px-3 py-2">Typ</th>
                <th class="px-3 py-2">Pojazd</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Opis</th>
                <th class="px-3 py-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($events as $e)
                <tr class="border-t">
                    <td class="px-3 py-2 font-mono">{{ $e->event_date->format('Y-m-d') }}</td>
                    <td class="px-3 py-2">{{ $e->type }}</td>
                    <td class="px-3 py-2">{{ $e->vehicle?->plate_number }}</td>
                    <td class="px-3 py-2">{{ $e->status }}</td>
                    <td class="px-3 py-2">{{ \Illuminate\Support\Str::limit($e->notes, 60) }}</td>
                    <td class="px-3 py-2 text-right">
                        <a href="{{ route('driver.incidents.show', $e) }}" class="text-blue-600 hover:underline">Pokaż</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Brak zgłoszeń</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $events->links() }}</div>
@endsection
