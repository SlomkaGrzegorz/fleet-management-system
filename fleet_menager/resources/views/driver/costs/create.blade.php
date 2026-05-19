@extends('layouts.app')

@section('title', 'Nowy koszt')

@section('content')
<h1 class="text-2xl font-semibold mb-4">Nowy koszt</h1>

<form method="POST" action="{{ route('driver.costs.store') }}" enctype="multipart/form-data"
      class="bg-white p-6 rounded shadow space-y-4 max-w-2xl">
    @csrf

    <div>
        <label class="block text-sm mb-1">Pojazd</label>
        <select name="vehicle_id" required class="w-full border rounded px-3 py-2">
            @foreach ($vehicles as $v)
                <option value="{{ $v->id }}">{{ $v->plate_number }} - {{ $v->make }} {{ $v->model }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm mb-1">Kategoria</label>
            <select name="category" class="w-full border rounded px-3 py-2">
                @foreach ($categories as $value => $label)
                    <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm mb-1">Kwota (PLN)</label>
            <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm mb-1">Data</label>
            <input type="date" name="incurred_at" value="{{ old('incurred_at', now()->toDateString()) }}" required class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm mb-1">Faktura (PDF / JPG / PNG)</label>
            <input type="file" name="invoice" accept=".pdf,.jpg,.jpeg,.png" class="w-full border rounded px-3 py-2 bg-white">
        </div>
    </div>

    <div>
        <label class="block text-sm mb-1">Opis (opcjonalnie)</label>
        <input type="text" name="description" value="{{ old('description') }}" class="w-full border rounded px-3 py-2">
    </div>

    <button class="bg-blue-600 text-white px-4 py-2 rounded">Zapisz koszt</button>
</form>
@endsection
