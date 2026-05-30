@extends('layouts.app')

@section('title', __('Alerty'))

@section('content')
<h1 class="text-2xl font-semibold mb-4">{{ __('Statusy terminów') }}</h1>

<div class="grid md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2 text-red-600">{{ __('Przeterminowane') }}</h2>
        @forelse ($overdue as $e)
            <div class="text-sm py-1 border-b last:border-0 flex justify-between">
                <span><a href="{{ route('manager.events.show', $e) }}" class="hover:underline">{{ $e->type }} - {{ $e->vehicle?->plate_number }}</a></span>
                <span class="font-mono text-red-600">{{ $e->expiry_date->format('Y-m-d') }}</span>
            </div>
        @empty
            <p class="text-gray-500 text-sm">{{ __('Brak.') }}</p>
        @endforelse
    </div>
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">{{ __('Wkrótce wygasają') }}</h2>
        @forelse ($expiring as $e)
            <div class="text-sm py-1 border-b last:border-0 flex justify-between">
                <span><a href="{{ route('manager.events.show', $e) }}" class="hover:underline">{{ $e->type }} - {{ $e->vehicle?->plate_number }}</a></span>
                <span class="font-mono">{{ $e->expiry_date->format('Y-m-d') }}</span>
            </div>
        @empty
            <p class="text-gray-500 text-sm">{{ __('Brak.') }}</p>
        @endforelse
    </div>
</div>

@if ($stored->isNotEmpty())
    <div id="stored-alerts" class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">{{ __('Zaplanowane alerty') }}</h2>
        @foreach ($stored as $a)
            <div data-alert-id="{{ $a->id }}"
                 class="alert-row flex justify-between items-center py-1 border-b last:border-0 text-sm">
                <span>{{ $a->type }} - {{ $a->vehicle?->plate_number }} - {{ $a->trigger_date->format('Y-m-d') }}</span>
                <button type="button"
                        class="dismiss-alert text-blue-600 hover:underline"
                        data-url="{{ route('manager.alerts.dismiss', $a) }}">
                    {{ __('Odznacz') }}
                </button>
            </div>
        @endforeach
    </div>

    {{-- AJAX: dismiss bez przeładowania strony --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            document.querySelectorAll('.dismiss-alert').forEach((btn) => {
                btn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    const url = btn.dataset.url;
                    const row = btn.closest('.alert-row');
                    btn.disabled = true;
                    btn.textContent = '...';

                    try {
                        const res = await fetch(url, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                        });

                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        const data = await res.json();

                        // Płynne ukrycie wiersza
                        row.style.transition = 'opacity 200ms';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 200);
                    } catch (err) {
                        btn.disabled = false;
                        btn.textContent = '{{ __('Odznacz') }}';
                        alert('Błąd: ' + err.message);
                    }
                });
            });
        });
    </script>
@endif
@endsection
