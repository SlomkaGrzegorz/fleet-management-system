@extends('layouts.app')

@section('title', 'Koszt #' . $cost->id)

@section('content')
<h1 class="text-2xl font-semibold mb-4">Koszt #{{ $cost->id }}</h1>

<div class="bg-white p-6 rounded shadow space-y-2 max-w-2xl">
    <div><strong>Data:</strong> {{ $cost->incurred_at->format('Y-m-d') }}</div>
    <div><strong>Kategoria:</strong> {{ $cost->category }}</div>
    <div><strong>Kwota:</strong> {{ number_format((float) $cost->amount, 2, ',', ' ') }} zł</div>
    <div><strong>Pojazd:</strong> {{ $cost->vehicle?->plate_number }}</div>
    <div><strong>Wprowadził:</strong> {{ $cost->enteredBy?->name }}</div>
    @if ($cost->description)
        <div><strong>Opis:</strong> {{ $cost->description }}</div>
    @endif

    @if ($cost->documents->isNotEmpty())
        <div class="pt-2 border-t">
            <strong>Faktury / załączniki:</strong>
            <ul class="list-disc list-inside">
                @foreach ($cost->documents as $d)
                    <li><a class="text-blue-600 hover:underline" href="{{ asset('storage/' . $d->path) }}" target="_blank">{{ $d->filename }}</a></li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection
