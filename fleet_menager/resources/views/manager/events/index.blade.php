@extends('layouts.app')

@section('title', 'Zgłoszenia')

@section('content')
<h1 class="text-2xl font-semibold mb-4">Zgłoszenia kierowców</h1>

<form method="GET" class="mb-4 flex gap-2">
    <select name="type" class="border rounded px-3 py-2">
        <option value="">-- typ --</option>
        @foreach (['incident', 'repair', 'service', 'inspection', 'insurance', 'other'] as $t)
            <option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>
        @endforeach
    </select>
    <select name="status" class="border rounded px-3 py-2">
        <option value="">-- status --</option>
        @foreach (['open', 'in_progress', 'closed'] as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
        @endforeach
    </select>
    <button class="bg-gray-200 px-4 py-2 rounded">Filtruj</button>
</form>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-100 text-left">
            <tr>
                <th class="px-3 py-2">Data</th>
                <th class="px-3 py-2">Typ</th>
                <th class="px-3 py-2">Pojazd</th>
                <th class="px-3 py-2">Kierowca</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($events as $e)
                <tr class="border-t">
                    <td class="px-3 py-2 font-mono">{{ $e->event_date->format('Y-m-d') }}</td>
                    <td class="px-3 py-2">{{ $e->type }}</td>
                    <td class="px-3 py-2">{{ $e->vehicle?->plate_number }}</td>
                    <td class="px-3 py-2">{{ $e->reporter?->name }}</td>
                    <td class="px-3 py-2">{{ $e->status }}</td>
                    <td class="px-3 py-2 text-right">
                        <a href="{{ route('manager.events.show', $e) }}" class="text-blue-600 hover:underline">Pokaż</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $events->links() }}</div>
@endsection
