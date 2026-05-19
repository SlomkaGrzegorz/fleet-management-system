@extends('layouts.app')

@section('title', 'Dodaj pojazd')

@section('content')
<h1 class="text-2xl font-semibold mb-4">Dodaj pojazd</h1>

<form method="POST" action="{{ route('admin.vehicles.store') }}"
      class="bg-white p-6 rounded shadow space-y-4 max-w-2xl">
    @csrf
    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm mb-1">Nr rejestracyjny</label>
            <input name="plate_number" required value="{{ old('plate_number') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm mb-1">VIN</label>
            <input name="vin" value="{{ old('vin') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm mb-1">Marka</label>
            <input name="make" required value="{{ old('make') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm mb-1">Model</label>
            <input name="model" required value="{{ old('model') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm mb-1">Rok</label>
            <input type="number" name="year" required value="{{ old('year', date('Y')) }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm mb-1">Status</label>
            <select name="status" class="w-full border rounded px-3 py-2">
                @foreach ($statuses as $v => $l)
                    <option value="{{ $v }}" @selected(old('status') === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <button class="bg-green-600 text-white px-4 py-2 rounded">Dodaj pojazd</button>
</form>
@endsection
