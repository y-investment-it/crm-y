<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['fa', 'ar']) ? 'rtl' : 'ltr' }}" class="engaz-html">
<head>
    {!! view_render_event('admin.layout.head.before') !!}

    <title>@yield('title', config('app.name'))</title>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/engaz.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('meta')

    @stack('styles')

    {!! view_render_event('admin.layout.head.after') !!}
</head>
<body class="engaz-body">
    {!! view_render_event('admin.layout.body.before') !!}

    <x-admin::flash-group />
    <x-admin::modal.confirm />

    <div class="engaz-layout">
        <aside class="engaz-sidebar">
            <div class="engaz-brand">
                <span class="engaz-brand__title">Engaz CRM</span>
            </div>

            @php
                use Illuminate\Support\Facades\Route;

                $sidebarItems = [
                    ['label' => 'Dashboard', 'name' => 'admin.dashboard.index'],
                    ['label' => 'Leads', 'name' => 'admin.leads.index'],
                    ['label' => 'TeleLeads', 'name' => 'admin.leads.index', 'params' => ['view' => 'tele']],
                    ['label' => 'Referrals', 'name' => 'admin.leads.index', 'params' => ['view' => 'referrals']],
                    ['label' => 'Owners', 'name' => 'admin.contacts.persons.index'],
                    ['label' => 'Inventory', 'name' => 'admin.products.index'],
                    ['label' => 'Collections', 'name' => 'admin.activities.index'],
                    ['label' => 'Reports', 'name' => 'admin.contacts.organizations.index'],
                    ['label' => 'Users', 'name' => 'admin.settings.users.index'],
                    ['label' => 'Exports', 'name' => 'admin.settings.export.index'],
                    ['label' => 'Imports', 'name' => 'admin.settings.import.index'],
                ];

                $sidebarItems = collect($sidebarItems)
                    ->map(function ($item) {
                        if (isset($item['name']) && Route::has($item['name'])) {
                            $item['route'] = route($item['name'], $item['params'] ?? []);
                        } else {
                            $item['route'] = $item['route'] ?? '#';
                        }

                        return $item;
                    })
                    ->all();
            @endphp

            <nav class="engaz-sidebar__nav">
                @foreach ($sidebarItems as $item)
                    <a
                        href="{{ $item['route'] ?? '#' }}"
                        class="engaz-sidebar__link {{ request()->fullUrlIs(($item['route'] ?? '') . '*') ? 'is-active' : '' }}"
                    >
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="engaz-main">
            <header class="engaz-header">
                <div class="engaz-search">
                    <input
                        type="search"
                        placeholder="Lead info"
                        class="engaz-search__input"
                        name="search"
                    >
                </div>

                <div class="engaz-header__actions">
                    <button type="button" class="engaz-header__explore">Explore New NAVI</button>
                    <button type="button" class="engaz-header__icon" aria-label="Create">+</button>
                    <button type="button" class="engaz-header__icon" aria-label="Notifications">
                        <span class="engaz-dot"></span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7v3.764l-.895 2.684A1 1 0 0 0 5.053 17H18.95a1 1 0 0 0 .948-1.552L19 12.764V9a7 7 0 0 0-7-7Zm0 20a3 3 0 0 0 3-3H9a3 3 0 0 0 3 3Z"/></svg>
                    </button>
                    <button type="button" class="engaz-header__icon" aria-label="Messages">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16a1 1 0 0 1 1 1v14.382a1 1 0 0 1-1.447.894L12 17.618l-7.553 2.658A1 1 0 0 1 3 19.382V5a1 1 0 0 1 1-1Z"/></svg>
                    </button>
                </div>
            </header>

            <main class="engaz-content">
                <div class="engaz-page-header">
                    <div>
                        <p class="engaz-page-subtitle">{{ now()->format('l, d F Y') }}</p>
                        <h1 class="engaz-page-title">@yield('page_title')</h1>
                    </div>
                </div>

                @yield('content')
            </main>
        </div>
    </div>

    @hasSection('vue')
        <div id="admin-app" class="engaz-vue-root">
            @yield('vue')
        </div>
    @else
        <div id="admin-app" class="engaz-vue-root"></div>
    @endif

    @stack('scripts')

    {!! view_render_event('admin.layout.body.after') !!}
</body>
</html>
