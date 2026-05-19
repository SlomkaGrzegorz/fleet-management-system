@extends('layouts.app')

@section('title', 'Alerty')

@section('content')
<h1 class="text-2xl font-semibold mb-4">Statusy terminów</h1>

<div class="grid md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2 text-red-600">Przeterminowane</h2>
        @forelse ($overdue as $e)
            <div class="text-sm py-1 border-b last:border-0 flex justify-between">
                <span><a href="{{ route('manager.events.show', $e) }}" class="hover:underline">{{ $e->type }} - {{ $e->vehicle?->plate_number }}</a></span>
                <span class="font-mono text-red-600">{{ $e->expiry_date->format('Y-m-d') }}</span>
            </div>
        @empty
            <p class="text-gray-500 text-sm">Brak.</p>
        @endforelse
    </div>
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Wkrótce wygasają</h2>
        @forelse ($expiring as $e)
            <div class="text-sm py-1 border-b last:border-0 flex justify-between">
                <span><a href="{{ route('manager.events.show', $e) }}" class="hover:underline">{{ $e->type }} - {{ $e->vehicle?->plate_number }}</a></span>
                <span class="font-mono">{{ $e->expiry_date->format('Y-m-d') }}</span>
            </div>
        @empty
            <p class="text-gray-500 text-sm">Brak.</p>
        @endforelse
    </div>
</div>

@if ($stored->isNotEmpty())
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Zaplanowane alerty</h2>
        @foreach ($stored as $a)
            <div class="flex justify-between items-center py-1 border-b last:border-0 text-sm">
                <span>{{ $a->type }} - {{ $a->vehicle?->plate_number }} - {{ $a->trigger_date->format('Y-m-d') }}</span>
                <form method="POST" action="{{ route('manager.alerts.dismiss', $a) }}">
                    @csrf @method('PATCH')
                    <button class="text-blue-600 hover:underline">Odznacz</button>
                </form>
            </div>
        @endforeach
    </div>
@endif
@endsection
