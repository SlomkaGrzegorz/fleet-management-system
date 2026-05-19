@extends('layouts.app')

@section('title', 'Nowe zgłoszenie')

@section('content')
<h1 class="text-2xl font-semibold mb-4">Nowe zgłoszenie</h1>

<form method="POST" action="{{ route('driver.incidents.store') }}" class="bg-white p-6 rounded shadow space-y-4 max-w-2xl">
    @csrf

    <div>
        <label class="block text-sm mb-1">Pojazd</label>
        <select name="vehicle_id" required class="w-full border rounded px-3 py-2">
            @foreach ($vehicles as $v)
                <option value="{{ $v->id }}" @selected(old('vehicle_id') == $v->id)>
                    {{ $v->plate_number }} - {{ $v->make }} {{ $v->model }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm mb-1">Typ zgłoszenia</label>
            <select name="type" class="w-full border rounded px-3 py-2">
                <option value="incident" @selected(old('type') === 'incident')>Wypadek / incydent</option>
                <option value="repair"   @selected(old('type') === 'repair')>Naprawa</option>
                <option value="service"  @selected(old('type') === 'service')>Serwis</option>
                <option value="other"    @selected(old('type') === 'other')>Inne</option>
            </select>
        </div>
        <div>
            <label class="block text-sm mb-1">Data zdarzenia</label>
            <input type="date" name="event_date" value="{{ old('event_date', now()->toDateString()) }}" required class="w-full border rounded px-3 py-2">
        </div>
    </div>

    <div>
        <label class="block text-sm mb-1">Opis</label>
        <textarea name="notes" rows="5" required class="w-full border rounded px-3 py-2">{{ old('notes') }}</textarea>
    </div>

    <button class="bg-red-600 text-white px-4 py-2 rounded">Zapisz zgłoszenie</button>
</form>
@endsection
