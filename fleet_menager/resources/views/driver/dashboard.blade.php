@extends('layouts.app')

@section('title', __('Mój pulpit'))

@section('content')
<div class="grid md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-4 rounded shadow">
        <div class="text-sm text-gray-500">{{ __('Przypisane pojazdy') }}</div>
        <div class="text-3xl font-semibold">{{ $vehicles->count() }}</div>
    </div>
    <div class="bg-white p-4 rounded shadow">
        <div class="text-sm text-gray-500">{{ __('Koszty w tym miesiącu') }}</div>
        <div class="text-3xl font-semibold">{{ number_format((float) $monthlyCosts, 2, ',', ' ') }} zł</div>
    </div>
    <div class="bg-white p-4 rounded shadow flex items-center justify-between">
        <span>{{ __('Szybkie akcje') }}</span>
        <div class="space-x-2">
            <a href="{{ route('driver.incidents.create') }}" class="px-3 py-1 bg-red-600 text-white rounded">{{ __('+ Incydent') }}</a>
            <a href="{{ route('driver.costs.create') }}" class="px-3 py-1 bg-blue-600 text-white rounded">{{ __('+ Koszt') }}</a>
        </div>
    </div>
</div>

<div class="bg-white p-4 rounded shadow mb-6">
    <h2 class="font-semibold mb-2">{{ __('Przypisane pojazdy') }}</h2>
    @forelse ($vehicles as $v)
        <div class="py-1 border-b last:border-0 flex justify-between">
            <span>{{ $v->plate_number }} - {{ $v->make }} {{ $v->model }} ({{ $v->year }})</span>
            <span class="text-sm text-gray-500">{{ $v->status }}</span>
        </div>
    @empty
        <p class="text-gray-500">{{ __('Brak przypisanych pojazdów. Skontaktuj się z managerem.') }}</p>
    @endforelse
</div>

<div class="bg-white p-4 rounded shadow">
    <h2 class="font-semibold mb-2">{{ __('Ostatnie zgłoszenia') }}</h2>
    @forelse ($recentEvents as $e)
        <a href="{{ route('driver.incidents.show', $e) }}" class="block py-1 border-b last:border-0 hover:bg-gray-50">
            <span class="font-mono text-sm">{{ $e->event_date->format('Y-m-d') }}</span>
            - <span class="uppercase text-xs bg-gray-200 px-1 rounded">{{ $e->type }}</span>
            - {{ \Illuminate\Support\Str::limit($e->notes, 80) }}
        </a>
    @empty
        <p class="text-gray-500">{{ __('Brak zgłoszeń') }}.</p>
    @endforelse
</div>
@endsection
