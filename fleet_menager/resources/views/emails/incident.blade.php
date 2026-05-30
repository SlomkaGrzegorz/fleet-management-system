<x-mail::message>
# Nowe zgłoszenie #{{ $event->id }}

W systemie pojawiło się nowe zgłoszenie:

- **Typ:** {{ $event->type }}
- **Data zdarzenia:** {{ $event->event_date->format('Y-m-d') }}
- **Pojazd:** {{ $vehicle?->plate_number }} ({{ $vehicle?->make }} {{ $vehicle?->model }})
- **Zgłosił:** {{ $driver?->name }} ({{ $driver?->email }})

**Opis:**

> {{ $event->notes }}

<x-mail::button :url="url(route('manager.events.show', $event))">
Otwórz w panelu
</x-mail::button>

Pozdrawiamy,
zespół {{ config('app.name') }}
</x-mail::message>
