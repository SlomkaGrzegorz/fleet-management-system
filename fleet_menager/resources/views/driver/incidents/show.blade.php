@extends('layouts.app')

@section('title', 'Zgłoszenie #' . $event->id)

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-semibold">Zgłoszenie #{{ $event->id }}</h1>
    @if (auth()->user()->isAdmin())
        <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
              onsubmit="return confirm('Usunąć to zgłoszenie?');">
            @csrf @method('DELETE')
            <button class="bg-red-700 text-white px-3 py-1 rounded">Usuń (admin)</button>
        </form>
    @endif
</div>

<div class="bg-white p-6 rounded shadow space-y-2 mb-6">
    <div><strong>Typ:</strong> {{ $event->type }}</div>
    <div><strong>Data:</strong> {{ $event->event_date->format('Y-m-d') }}</div>
    <div><strong>Status:</strong> {{ $event->status }}</div>
    <div><strong>Pojazd:</strong> {{ $event->vehicle?->plate_number }} ({{ $event->vehicle?->make }} {{ $event->vehicle?->model }})</div>
    <div><strong>Zgłosił:</strong> {{ $event->reporter?->name }}</div>
    @if ($event->expiry_date)
        <div><strong>Termin:</strong> {{ $event->expiry_date->format('Y-m-d') }}</div>
    @endif
    <div class="pt-2 border-t"><strong>Opis:</strong><br>{{ $event->notes }}</div>
</div>

<div class="bg-white p-6 rounded shadow">
    <h2 class="font-semibold mb-2">Powiązane koszty</h2>
    @forelse ($event->costs as $c)
        <div class="py-1 border-b last:border-0 flex justify-between">
            <span>{{ $c->incurred_at->format('Y-m-d') }} - {{ $c->category }} - {{ $c->description }}</span>
            <span class="font-mono">{{ number_format((float) $c->amount, 2, ',', ' ') }} zł</span>
        </div>
    @empty
        <p class="text-gray-500">Brak kosztów powiązanych z tym zgłoszeniem.</p>
    @endforelse
</div>
@endsection
