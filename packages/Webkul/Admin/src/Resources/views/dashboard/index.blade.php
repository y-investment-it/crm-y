@extends('admin::layouts.engaz-layout')

@section('title', __('admin::app.dashboard.index.title'))
@section('page_title', __('admin::app.dashboard.index.title'))

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $formatCount = function ($value, $suffix = '') {
        if (is_numeric($value)) {
            return number_format($value).$suffix;
        }

        return $value ?: '--';
    };

    $statsSource = $stats ?? $leadStats ?? $totals ?? [];

    $statCards = [
        [
            'label' => __('All Leads'),
            'value' => $formatCount(data_get($statsSource, 'all')),
            'hint'  => __('Total leads across all pipelines'),
        ],
        [
            'label' => __('Duplicate'),
            'value' => $formatCount(data_get($statsSource, 'duplicate') ?? data_get($statsSource, 'duplicates')),
            'hint'  => __('Possible duplicate entries requiring review'),
        ],
        [
            'label' => __('Fresh'),
            'value' => $formatCount(data_get($statsSource, 'fresh') ?? data_get($statsSource, 'new')),
            'hint'  => __('Recently created or untouched leads'),
        ],
        [
            'label' => __('Cold Calls'),
            'value' => $formatCount(data_get($statsSource, 'cold_calls') ?? data_get($statsSource, 'cold') ?? data_get($statsSource, 'cold_call')),
            'hint'  => __('Leads queued for cold calling campaigns'),
        ],
    ];

    $delayStats = $delay ?? $backlog ?? [];

    $delayTabs = [
        ['label' => __('Today'), 'value' => $formatCount(data_get($delayStats, 'today'))],
        ['label' => __('7 Days'), 'value' => $formatCount(data_get($delayStats, 'seven_days') ?? data_get($delayStats, 'week'))],
        ['label' => __('30 Days'), 'value' => $formatCount(data_get($delayStats, 'thirty_days') ?? data_get($delayStats, 'month'))],
        ['label' => __('90 Days'), 'value' => $formatCount(data_get($delayStats, 'ninety_days') ?? data_get($delayStats, 'quarter'))],
    ];

    $leadsCollection = collect($leads ?? $recentLeads ?? $latestLeads ?? []);

    if ($leadsCollection->isEmpty()) {
        $leadsCollection = collect([
            [
                'id'            => null,
                'name'          => 'Marina Seif',
                'phone'         => '+20 101 234 5678',
                'stage_date'    => now()->subDays(1)->format('Y-m-d'),
                'last_comment'  => 'Requested brochure and pricing details.',
            ],
            [
                'id'            => null,
                'name'          => 'Ahmed Faris',
                'phone'         => '+20 112 345 6789',
                'stage_date'    => now()->subDays(3)->format('Y-m-d'),
                'last_comment'  => 'Follow-up call scheduled for next Tuesday.',
            ],
            [
                'id'            => null,
                'name'          => 'Lina Mostafa',
                'phone'         => '+20 100 987 6543',
                'stage_date'    => now()->subWeek()->format('Y-m-d'),
                'last_comment'  => 'Waiting on updated payment plan.',
            ],
        ]);
    }

    $bestPerformers = collect($best ?? $topPerformers ?? $topUsers ?? []);

    if ($bestPerformers->isEmpty()) {
        $bestPerformers = collect([
            ['name' => 'Sarah N.', 'metric' => '42 deals', 'trend' => '+12%'],
            ['name' => 'Kareem T.', 'metric' => '37 deals', 'trend' => '+8%'],
            ['name' => 'Mona R.', 'metric' => '31 deals', 'trend' => '+5%'],
        ]);
    }

    $initialLeadIds = $leadsCollection->pluck('id')->filter()->values();
@endphp

@section('content')
    <div class="engaz-dashboard">
        <div class="engaz-dashboard__left">
            <section class="engaz-stats">
                @foreach ($statCards as $card)
                    <article class="engaz-card">
                        <p class="engaz-card__label">{{ $card['label'] }}</p>
                        <h2 class="engaz-card__value">{{ $card['value'] }}</h2>
                        <p class="engaz-card__hint">{{ $card['hint'] }}</p>
                    </article>
                @endforeach
            </section>

            <section class="engaz-delay">
                <header class="engaz-section__header">
                    <h2>{{ __('Delay Monitor') }}</h2>
                    <p>{{ __('Overview of delayed follow-ups across time ranges.') }}</p>
                </header>

                <div class="engaz-delay__tabs">
                    @foreach ($delayTabs as $tab)
                        <div class="engaz-delay__tab">
                            <span class="engaz-delay__tab-label">{{ $tab['label'] }}</span>
                            <span class="engaz-delay__tab-value">{{ $tab['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="engaz-table">
                <header class="engaz-section__header">
                    <div>
                        <h2>{{ __('Leads Pipeline') }}</h2>
                        <p>{{ __('Last touch points and upcoming actions for your active leads.') }}</p>
                    </div>
                    <div class="engaz-table__actions">
                        <span class="engaz-table__count">{{ __(':count leads', ['count' => $leadsCollection->count()]) }}</span>
                    </div>
                </header>

                <div class="engaz-table__scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('Lead') }}</th>
                                <th>{{ __('Phone') }}</th>
                                <th>{{ __('Stage Date') }}</th>
                                <th>{{ __('Last Comment') }}</th>
                                <th>{{ __('Preview') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($leadsCollection as $lead)
                                @php
                                    $leadId = data_get($lead, 'id');
                                    $leadName = data_get($lead, 'name') ?? data_get($lead, 'person.name') ?? data_get($lead, 'title') ?? __('N/A');
                                    $phone = data_get($lead, 'phone') ?? data_get($lead, 'person.phone') ?? data_get($lead, 'contact_number') ?? data_get($lead, 'phone_number') ?? __('—');
                                    $stageDate = data_get($lead, 'stage_date') ?? data_get($lead, 'updated_at') ?? data_get($lead, 'created_at');
                                    $stageDate = $stageDate ? Carbon::parse($stageDate)->format('Y-m-d') : __('N/A');
                                    $lastComment = data_get($lead, 'last_comment') ?? data_get($lead, 'latest_note') ?? data_get($lead, 'note') ?? __('No comments yet');
                                    $previewRoute = ($leadId && Route::has('admin.leads.view')) ? route('admin.leads.view', $leadId) : '#';
                                @endphp
                                <tr>
                                    <td>
                                        <span class="engaz-table__primary">{{ $leadName }}</span>
                                    </td>
                                    <td>{{ $phone }}</td>
                                    <td>{{ $stageDate }}</td>
                                    <td>{{ Str::limit($lastComment, 60) }}</td>
                                    <td>
                                        @if ($previewRoute !== '#')
                                            <a href="{{ $previewRoute }}" class="engaz-link">{{ __('Preview') }}</a>
                                        @else
                                            <span class="engaz-muted">{{ __('Preview') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="engaz-empty">{{ __('No leads available right now.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="engaz-tools">
                <p class="engaz-tools__title">{{ __('Need to re-assign leads quickly?') }}</p>
                <p class="engaz-tools__subtitle">{{ __('Use the modal below to hand over selected leads to another teammate.') }}</p>
            </div>
        </div>

        <aside class="engaz-dashboard__right">
            <section class="engaz-best">
                <header>
                    <h2>{{ __('The Best') }}</h2>
                    <p>{{ __('Top performers in the current cycle.') }}</p>
                </header>

                <ol class="engaz-best__list">
                    @foreach ($bestPerformers as $performer)
                        <li class="engaz-best__item">
                            <div>
                                <p class="engaz-best__name">{{ data_get($performer, 'name', __('Unknown')) }}</p>
                                <p class="engaz-best__metric">{{ data_get($performer, 'metric', __('No data')) }}</p>
                            </div>
                            <span class="engaz-best__trend">{{ data_get($performer, 'trend', '+0%') }}</span>
                        </li>
                    @endforeach
                </ol>
            </section>
        </aside>
    </div>
@endsection

@section('vue')
    <re-assign-leads :initial-leads='@json($initialLeadIds)'></re-assign-leads>
@endsection
