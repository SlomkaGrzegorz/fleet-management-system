<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Fleet Manager')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">
    @auth
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('home') }}" class="font-semibold text-lg">Fleet Manager</a>

                <div class="flex flex-wrap items-center gap-4 text-sm">
                    @if (auth()->user()->isDriver() || auth()->user()->isAdmin())
                        <a href="{{ route('driver.dashboard') }}" class="hover:underline">Mój pulpit</a>
                        <a href="{{ route('driver.incidents.index') }}" class="hover:underline">Moje zgłoszenia</a>
                        <a href="{{ route('driver.costs.index') }}" class="hover:underline">Moje koszty</a>
                    @endif

                    @if (auth()->user()->isManager() || auth()->user()->isAdmin())
                        <span class="text-gray-300">|</span>
                        <a href="{{ route('manager.dashboard') }}" class="hover:underline">Flota</a>
                        <a href="{{ route('manager.vehicles.index') }}" class="hover:underline">Pojazdy</a>
                        <a href="{{ route('manager.events.index') }}" class="hover:underline">Zgłoszenia</a>
                        <a href="{{ route('manager.costs.index') }}" class="hover:underline">Koszty</a>
                        <a href="{{ route('manager.alerts.index') }}" class="hover:underline">Alerty</a>
                    @endif

                    @if (auth()->user()->isAdmin())
                        <span class="text-gray-300">|</span>
                        <a href="{{ route('admin.users.index') }}" class="hover:underline">Użytkownicy</a>
                        <a href="{{ route('admin.reports.index') }}" class="hover:underline">Raporty</a>
                        <a href="{{ route('admin.vehicles.create') }}" class="hover:underline">+ Pojazd</a>
                    @endif
                </div>

                <div class="flex items-center gap-3 text-sm">
                    <span class="text-gray-600">{{ auth()->user()->name }} ({{ auth()->user()->role->label() }})</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="px-3 py-1 rounded bg-gray-100 hover:bg-gray-200">Wyloguj</button>
                    </form>
                </div>
            </div>
        </nav>
    @endauth

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 py-6">
        @if (session('status'))
            <div class="mb-4 rounded border border-green-300 bg-green-50 text-green-800 px-4 py-2">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded border border-red-300 bg-red-50 text-red-800 px-4 py-2">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
