@extends('default.layouts.app')

@section('content')

<div style="display:flex; flex-wrap:wrap; gap:12px; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#1e293b;margin:0;">Analytics Overview</h1>
        <p style="color:#64748b;font-size:0.875rem;margin:4px 0 0;">Live stats from all connected sites</p>
    </div>
    @can('create sites')
    <a href="{{ route('sites.create') }}" class="btn btn-primary">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Site
    </a>
    @endcan
</div>

@if($data['cards']->isEmpty())
    <div class="empty-state">
        <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" style="margin:0 auto 16px;display:block;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
        <h3>No sites connected</h3>
        <p style="margin-bottom:20px;">Add your first site to start tracking analytics.</p>
        @can('create sites')
        <a href="{{ route('sites.create') }}" class="btn btn-primary">Add Site</a>
        @endcan
    </div>
@else
    <div class="site-grid">
        @foreach($data['cards'] as $card)
        @php
            $site   = $card['site'];
            $last7  = $card['last7'];
            $labels = collect($last7)->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('D'))->toJson();
            $values = collect($last7)->pluck('visitor_count')->toJson();
            $chartId = 'chart-' . $site->id;
        @endphp
        <div class="site-card">
            <div class="site-card-header">
                <div>
                    <div class="site-card-name">{{ $site->name }}</div>
                    <div class="site-card-url">{{ $site->url }}</div>
                </div>
                <div style="display:flex;gap:6px;">
                    @can('edit sites')
                    <a href="{{ route('sites.edit', $site) }}" class="btn btn-secondary btn-sm">Edit</a>
                    @endcan
                </div>
            </div>

            @if($card['error'])
                <div class="site-card-error">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:inline;vertical-align:-2px;margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    {{ $card['error'] }}
                </div>
            @else
                <div class="site-card-stat">
                    <span class="site-card-stat-value">{{ number_format($card['user_count']) }}</span>
                    <span class="site-card-stat-label">registered users</span>
                </div>

                <div>
                    <div style="font-size:0.75rem;font-weight:600;color:#64748b;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Visitors — Last 7 Days</div>
                    <div class="site-card-chart">
                        <canvas id="{{ $chartId }}"></canvas>
                    </div>
                </div>
            @endif
        </div>
        @endforeach
    </div>
@endif

@endsection

@push('scripts')
@php
$chartData = $data['cards']->filter(fn($c) => !$c['error'])->map(fn($c) => [
    'id'     => 'chart-' . $c['site']->id,
    'labels' => $c['chart_labels'],
    'values' => $c['chart_values'],
])->values();
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    const charts = {!! json_encode($chartData) !!};

    charts.forEach(function (cfg) {
        const ctx = document.getElementById(cfg.id);
        if (!ctx) return;
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: cfg.labels,
                datasets: [{
                    data: cfg.values,
                    backgroundColor: 'rgba(59,130,246,0.2)',
                    borderColor: 'rgba(59,130,246,0.8)',
                    borderWidth: 1.5,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#94a3b8' } },
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 }, color: '#94a3b8', precision: 0 } }
                }
            }
        });
    });
});
</script>
@endpush
