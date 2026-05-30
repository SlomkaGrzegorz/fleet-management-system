@extends('layouts.app')

@section('title', __('Pulpit floty'))

@section('content')
<div class="grid md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white p-4 rounded shadow"><div class="text-sm text-gray-500">{{ __('Pojazdy') }}</div><div class="text-2xl font-semibold">{{ $vehiclesTotal }}</div></div>
    <div class="bg-white p-4 rounded shadow"><div class="text-sm text-gray-500">{{ __('Dostępne') }}</div><div class="text-2xl font-semibold">{{ $vehiclesAvailable }}</div></div>
    <div class="bg-white p-4 rounded shadow"><div class="text-sm text-gray-500">{{ __('Kierowcy') }}</div><div class="text-2xl font-semibold">{{ $driversTotal }}</div></div>
    <div class="bg-white p-4 rounded shadow"><div class="text-sm text-gray-500">{{ __('Otwarte incydenty') }}</div><div class="text-2xl font-semibold">{{ $openIncidents }}</div></div>
    <div class="bg-white p-4 rounded shadow"><div class="text-sm text-gray-500">{{ __('Koszty m-ca') }}</div><div class="text-2xl font-semibold">{{ number_format($monthlyCostsTotal, 2, ',', ' ') }} zł</div></div>
</div>

<div class="grid md:grid-cols-2 gap-6">
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">{{ __('Wkrótce wygasają (30 dni)') }}</h2>
        @forelse ($expiringEvents as $e)
            <div class="py-1 border-b last:border-0 text-sm flex justify-between">
                <span>{{ $e->type }} - {{ $e->vehicle?->plate_number }}</span>
                <span class="font-mono">{{ $e->expiry_date->format('Y-m-d') }}</span>
            </div>
        @empty
            <p class="text-gray-500 text-sm">{{ __('Brak.') }}</p>
        @endforelse
    </div>
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2 text-red-600">{{ __('Przeterminowane') }}</h2>
        @forelse ($overdueEvents as $e)
            <div class="py-1 border-b last:border-0 text-sm flex justify-between">
                <span>{{ $e->type }} - {{ $e->vehicle?->plate_number }}</span>
                <span class="font-mono text-red-600">{{ $e->expiry_date->format('Y-m-d') }}</span>
            </div>
        @empty
            <p class="text-gray-500 text-sm">{{ __('Brak.') }}</p>
        @endforelse
    </div>
</div>
@endsection
