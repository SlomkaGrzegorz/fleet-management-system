@extends('layouts.app')

@section('title', 'Zgłoszenie #' . $event->id)

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-semibold">Zgłoszenie #{{ $event->id }}</h1>
    @if (auth()->user()->isAdmin())
        <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
              onsubmit="return confirm('Usunąć zgłoszenie?');">
            @csrf @method('DELETE')
            <button class="bg-red-700 text-white px-3 py-1 rounded">Usuń (admin)</button>
        </form>
    @endif
</div>

<div class="bg-white p-6 rounded shadow space-y-2 mb-6">
    <div><strong>Typ:</strong> {{ $event->type }}</div>
    <div><strong>Data:</strong> {{ $event->event_date->format('Y-m-d') }}</div>
    <div><strong>Pojazd:</strong> {{ $event->vehicle?->plate_number }}</div>
    <div><strong>Zgłosił:</strong> {{ $event->reporter?->name }}</div>
    <div class="pt-2 border-t">{{ $event->notes }}</div>
</div>

<form method="POST" action="{{ route('manager.events.status', $event) }}" class="bg-white p-4 rounded shadow mb-6 flex items-end gap-3">
    @csrf @method('PATCH')
    <div>
        <label class="block text-sm mb-1">Zmień status</label>
        <select name="status" class="border rounded px-3 py-2">
            @foreach (['open', 'in_progress', 'closed'] as $s)
                <option value="{{ $s }}" @selected($event->status === $s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <button class="bg-blue-600 text-white px-4 py-2 rounded">Zapisz</button>
</form>

<div class="bg-white p-6 rounded shadow">
    <h2 class="font-semibold mb-2">Koszty powiązane</h2>
    @forelse ($event->costs as $c)
        <div class="py-1 border-b last:border-0 flex justify-between text-sm">
            <span>{{ $c->incurred_at->format('Y-m-d') }} - {{ $c->category }} - {{ $c->enteredBy?->name }}</span>
            <span class="font-mono">{{ number_format((float) $c->amount, 2, ',', ' ') }} zł</span>
        </div>
    @empty
        <p class="text-gray-500 text-sm">Brak.</p>
    @endforelse
</div>
@endsection
