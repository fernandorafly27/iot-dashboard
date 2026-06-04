<?php

use Livewire\Volt\Component;
use App\Models\Program;
use App\Models\SensorReading;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public function getLatestReading()
    {
        return SensorReading::where('user_id', Auth::id())->latest()->first();
    }

    public function getSensorHistory()
    {
        return SensorReading::where('user_id', Auth::id())
            ->latest()
            ->take(24)
            ->get()
            ->reverse()
            ->values();
    }

    public function with()
    {
        return [
            'latest' => $this->getLatestReading(),
            'sensorHistory' => $this->getSensorHistory(),
            'totalReadings' => SensorReading::where('user_id', Auth::id())->count(),
            'activePrograms' => Program::where('user_id', Auth::id())->where('status', 'active')->count(),
            'programs' => Program::where('user_id', Auth::id())->latest()->take(5)->get(),
        ];
    }
}; ?>

@php
    $latestPh = $latest?->ph ?? 0;
    $latestEc = $latest?->nutrient ?? 0;
    $latestTurbidity = $latest?->turbidity ?? 0;
    $latestPump = $latest?->pump_pwm ?? 0;
    $latestHumidity = $latest?->humidity ?? 0;

    $avg = fn ($field) => $sensorHistory->count()
        ? $sensorHistory->avg($field)
        : 0;

    $metricCards = [
        ['title' => 'pH', 'value' => number_format($latestPh, 2), 'unit' => '', 'label' => 'Keasaman Larutan - Latest'],
        ['title' => 'EC', 'value' => number_format($latestEc, 2), 'unit' => 'mS/cm', 'label' => 'Nutrisi - Latest'],
        ['title' => 'Turbidity', 'value' => number_format($latestTurbidity, 2), 'unit' => 'NTU', 'label' => 'Kekeruhan - Latest'],
        ['title' => 'Pump PWM', 'value' => number_format($latestPump, 1), 'unit' => '%', 'label' => 'Aktivasi Pompa - Latest'],
        ['title' => 'Humidity', 'value' => number_format($latestHumidity, 1), 'unit' => '%', 'label' => 'Kelembaban - Latest'],
        ['title' => 'Readings', 'value' => number_format($totalReadings), 'unit' => '', 'label' => 'Total Data Sensor'],
    ];

    $barGroups = [
        'pH by Zone' => [
            ['label' => 'Reservoir', 'value' => $latestPh, 'max' => 14, 'caption' => 'pH - Latest'],
            ['label' => 'Selada L-1', 'value' => max(0, $latestPh - 0.18), 'max' => 14, 'caption' => 'pH - Estimate'],
            ['label' => 'Selada L-2', 'value' => max(0, $latestPh - 0.08), 'max' => 14, 'caption' => 'pH - Estimate'],
            ['label' => 'Kangkung K-1', 'value' => min(14, $latestPh + 0.12), 'max' => 14, 'caption' => 'pH - Estimate'],
            ['label' => 'Kangkung K-2', 'value' => min(14, $latestPh + 0.22), 'max' => 14, 'caption' => 'pH - Estimate'],
        ],
        'EC by Zone' => [
            ['label' => 'Reservoir', 'value' => $latestEc, 'max' => 3, 'caption' => 'EC - Latest'],
            ['label' => 'Selada L-1', 'value' => max(0, $latestEc - 0.14), 'max' => 3, 'caption' => 'EC - Estimate'],
            ['label' => 'Selada L-2', 'value' => max(0, $latestEc - 0.07), 'max' => 3, 'caption' => 'EC - Estimate'],
            ['label' => 'Kangkung K-1', 'value' => min(3, $latestEc + 0.09), 'max' => 3, 'caption' => 'EC - Estimate'],
            ['label' => 'Kangkung K-2', 'value' => min(3, $latestEc + 0.12), 'max' => 3, 'caption' => 'EC - Estimate'],
        ],
        'Flow by Zone' => [
            ['label' => 'Pump PWM', 'value' => $latestPump, 'max' => 100, 'caption' => 'PWM - Latest'],
            ['label' => 'Water Level', 'value' => min(100, 80 + ($latestPump * 0.08)), 'max' => 100, 'caption' => 'Level - Estimate'],
            ['label' => 'Airstone', 'value' => min(100, 55 + ($latestPump * 0.25)), 'max' => 100, 'caption' => 'Aeration - Estimate'],
            ['label' => 'Humidity', 'value' => $latestHumidity, 'max' => 100, 'caption' => 'Humidity - Latest'],
            ['label' => 'Water Clarity', 'value' => max(0, 100 - $latestTurbidity), 'max' => 100, 'caption' => 'Clarity - Estimate'],
        ],
    ];

    $trendPoints = function ($field, $height = 126, $width = 420) use ($sensorHistory) {
        if ($sensorHistory->count() < 2) {
            return '';
        }

        $values = $sensorHistory->pluck($field)->map(fn ($value) => (float) $value)->values();
        $min = $values->min();
        $max = $values->max();
        $span = max(0.01, $max - $min);
        $step = $width / max(1, $values->count() - 1);

        return $values->map(function ($value, $index) use ($min, $span, $height, $step) {
            $x = round($index * $step, 2);
            $y = round($height - ((($value - $min) / $span) * ($height - 18)) - 9, 2);
            return "{$x},{$y}";
        })->join(' ');
    };

    $trendPanels = [
        ['title' => 'pH Trending', 'field' => 'ph', 'color' => '#2f7d32', 'legend' => ['Reservoir', 'Selada', 'Kangkung']],
        ['title' => 'EC Nutrient Trending', 'field' => 'nutrient', 'color' => '#7a9f22', 'legend' => ['AB Mix', 'Reservoir', 'Drain']],
        ['title' => 'Humidity Trending', 'field' => 'humidity', 'color' => '#3f6f55', 'legend' => ['Greenhouse', 'Area Tanam', 'Ambient']],
    ];

    $statusFor = function ($value, $min, $max) {
        if (!$value) {
            return ['text' => 'No Data', 'class' => 'muted'];
        }

        if ($value < $min) {
            return ['text' => 'Low', 'class' => 'warning'];
        }

        if ($value > $max) {
            return ['text' => 'High', 'class' => 'danger'];
        }

        return ['text' => 'Normal', 'class' => 'ok'];
    };

    $nodeRows = [
        ['name' => 'Reservoir Utama', 'sensor' => 'pH / EC / Turbidity', 'value' => number_format($latestPh, 2).' pH', 'status' => $statusFor($latestPh, 5.5, 6.8)],
        ['name' => 'Pompa Nutrisi', 'sensor' => 'PWM Driver', 'value' => number_format($latestPump, 1).' %', 'status' => $statusFor($latestPump, 20, 90)],
        ['name' => 'Area Tanam L', 'sensor' => 'Humidity', 'value' => number_format($latestHumidity, 1).' %', 'status' => $statusFor($latestHumidity, 55, 85)],
        ['name' => 'Drain Tube', 'sensor' => 'Turbidity', 'value' => number_format($latestTurbidity, 2).' NTU', 'status' => $statusFor($latestTurbidity, 0.01, 25)],
    ];
@endphp

<div class="ci-shell">
    <aside class="ci-sidebar">
        <div class="ci-brand">
            <div class="ci-brand-mark">H</div>
            <div>
                <strong>HYDROGENESIS</strong>
                <span>DFT</span>
            </div>
        </div>

        <nav class="ci-nav">
            <a href="{{ route('dashboard') }}" class="active"><span>⌂</span> Home</a>
            <a href="{{ route('monitor') }}"><span>◉</span> Monitoring</a>
            <a href="{{ route('monitor') }}"><span>＋</span> Add Data</a>
            <a href="#"><span>⚙</span> Admin <b>{{ $activePrograms }}</b></a>
            <a href="#"><span>?</span> Help</a>
        </nav>

        <div class="ci-user">
            <div class="ci-avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
            <div>
                <strong>{{ Auth::user()->name }}</strong>
                <span>{{ Auth::user()->email }}</span>
            </div>
        </div>
    </aside>

    <section class="ci-main" wire:poll.5s>
        <header class="ci-topbar">
            <div class="ci-breadcrumb">
                <span>HydroGenesis DFT</span>
                <em>/</em>
                <span>All Dashboards</span>
                <em>/</em>
                <strong>Water Quality Lab Analysis</strong>
            </div>
            <div class="ci-actions">
                <span>⌕</span>
                <span>◷ Last 24 Hours</span>
                <a href="{{ route('monitor') }}">Edit</a>
                <span class="ci-grid-icon">▦</span>
            </div>
        </header>

        <main class="ci-content">
            <section class="ci-metrics">
                @foreach($metricCards as $card)
                    <article class="ci-metric">
                        <h2>{{ $card['title'] }}</h2>
                        <div>
                            <strong>{{ $card['value'] }}</strong>
                            @if($card['unit'])
                                <span>{{ $card['unit'] }}</span>
                            @endif
                        </div>
                        <p>{{ $card['label'] }}</p>
                    </article>
                @endforeach
            </section>

            @if(!$latest)
                <section class="ci-empty">
                    <h2>Belum ada data sensor</h2>
                    <p>Tambahkan pembacaan pertama agar dashboard analitik terisi.</p>
                    <a href="{{ route('monitor') }}">Tambah Data Sensor</a>
                </section>
            @endif

            <section class="ci-dashboard-grid">
                @foreach($barGroups as $title => $rows)
                    <article class="ci-panel ci-chart-panel">
                        <div class="ci-panel-head">
                            <h3>{{ $title }}</h3>
                        </div>
                        <div class="ci-bars">
                            @foreach($rows as $row)
                                @php
                                    $width = $row['max'] > 0 ? max(3, min(100, ($row['value'] / $row['max']) * 100)) : 3;
                                @endphp
                                <div class="ci-bar-row">
                                    <span>{{ $row['label'] }}</span>
                                    <div class="ci-bar-track"><i style="width: {{ $width }}%;"></i></div>
                                    <b>{{ number_format($row['value'], 2) }}</b>
                                </div>
                            @endforeach
                        </div>
                        <p class="ci-axis">{{ $rows[0]['caption'] }}</p>
                    </article>

                    <article class="ci-panel ci-table-panel">
                        <div class="ci-panel-head">
                            <h3>{{ str_replace(' by Zone', ' Nodes', $title) }}</h3>
                            <span>{{ $sensorHistory->count() }} items found in {{ max(1, $activePrograms) }} groups</span>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Node Type</th>
                                    <th>Sensor</th>
                                    <th>Value</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nodeRows as $row)
                                    <tr>
                                        <td><span class="ci-expand">+</span>{{ $row['name'] }}</td>
                                        <td>{{ $row['sensor'] }}</td>
                                        <td>{{ $row['value'] }}</td>
                                        <td><span class="ci-status {{ $row['status']['class'] }}">{{ $row['status']['text'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </article>

                    @php
                        $panel = $trendPanels[$loop->index] ?? $trendPanels[0];
                        $points = $trendPoints($panel['field']);
                    @endphp
                    <article class="ci-panel ci-trend-panel">
                        <div class="ci-panel-head">
                            <h3>{{ $panel['title'] }}</h3>
                            <span>⋮</span>
                        </div>
                        <svg class="ci-trend" viewBox="0 0 420 150" preserveAspectRatio="none">
                            <line x1="0" y1="126" x2="420" y2="126"></line>
                            <line x1="0" y1="58" x2="420" y2="58"></line>
                            @if($points)
                                <polyline points="{{ $points }}" style="stroke: {{ $panel['color'] }}"></polyline>
                                <polyline points="0,118 48,115 96,116 144,111 192,114 240,104 288,106 336,98 420,101" class="soft orange"></polyline>
                                <polyline points="0,96 48,105 96,91 144,99 192,87 240,95 288,79 336,87 420,76" class="soft dark"></polyline>
                            @endif
                        </svg>
                        <div class="ci-legend">
                            @foreach($panel['legend'] as $legend)
                                <span>{{ $legend }}</span>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </section>
        </main>
    </section>
</div>

<style>
    .ci-shell {
        --ci-navy: #102417;
        --ci-panel: #ffffff;
        --ci-line: #cbdccf;
        --ci-text: #263829;
        --ci-muted: #778b7d;
        --ci-blue: #2f7d32;
        min-height: 100vh;
        margin: -1rem;
        background: #eef6ed;
        color: var(--ci-text);
        display: grid;
        grid-template-columns: 16.5rem minmax(0, 1fr);
        font-family: Arial, Helvetica, sans-serif;
    }

    .ci-sidebar {
        background: #102417;
        color: #fff;
        min-height: 100vh;
        position: sticky;
        top: 0;
        display: flex;
        flex-direction: column;
    }

    .ci-brand {
        height: 4rem;
        background: #2f7d32;
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 0 1rem;
        min-width: 0;
    }

    .ci-brand-mark {
        width: 2.1rem;
        height: 2.1rem;
        background: #fff;
        color: #2f7d32;
        display: grid;
        place-items: center;
        font-weight: 900;
    }

    .ci-brand strong,
    .ci-brand span {
        display: block;
        line-height: 1;
        font-size: 1rem;
        letter-spacing: 0;
        white-space: nowrap;
    }

    .ci-nav {
        display: flex;
        flex-direction: column;
        padding-top: 0.9rem;
    }

    .ci-nav a {
        height: 3.5rem;
        color: #fff;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0 1.1rem;
        border-left: 3px solid transparent;
        font-size: 0.9rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .ci-nav a.active,
    .ci-nav a:hover {
        background: #24402c;
        border-left-color: #8bc34a;
    }

    .ci-nav span {
        width: 1.3rem;
        text-align: center;
        font-size: 1.25rem;
        font-weight: 700;
    }

    .ci-nav b {
        margin-left: auto;
        background: #6aa12d;
        padding: 0.1rem 0.35rem;
        border-radius: 0.2rem;
        font-size: 0.78rem;
    }

    .ci-user {
        margin-top: auto;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 0.7rem;
        border-top: 1px solid #284431;
    }

    .ci-avatar {
        width: 2.1rem;
        height: 2.1rem;
        border-radius: 50%;
        background: #fff;
        color: #102417;
        display: grid;
        place-items: center;
        font-weight: 900;
    }

    .ci-user strong,
    .ci-user span {
        display: block;
        max-width: 11rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ci-user span {
        color: #b8c9bb;
        font-size: 0.76rem;
    }

    .ci-main {
        min-width: 0;
    }

    .ci-topbar {
        min-height: 4rem;
        background: #fff;
        border-bottom: 1px solid var(--ci-line);
        box-shadow: 0 1px 4px rgba(30, 41, 59, 0.12);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 0 1.1rem;
    }

    .ci-breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        min-width: 0;
        font-size: 0.98rem;
        color: #3e5a45;
        overflow: hidden;
    }

    .ci-breadcrumb span,
    .ci-breadcrumb strong {
        white-space: nowrap;
    }

    .ci-breadcrumb strong {
        color: #243b29;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ci-breadcrumb em {
        color: #8c9b8f;
        font-style: normal;
    }

    .ci-actions {
        display: flex;
        align-items: center;
        gap: 1.1rem;
        color: #3d5742;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .ci-actions a {
        color: #2f7d32;
        font-weight: 800;
        text-decoration: none;
    }

    .ci-grid-icon {
        background: #4f9d45;
        color: #fff;
        align-self: stretch;
        display: grid;
        place-items: center;
        width: 3rem;
        margin-right: -1.1rem;
    }

    .ci-content {
        padding: 0.9rem 1.1rem 1.1rem;
    }

    .ci-metrics {
        display: grid;
        grid-template-columns: repeat(6, minmax(9rem, 1fr));
        gap: 0.55rem;
        margin-bottom: 0.55rem;
    }

    .ci-metric,
    .ci-panel,
    .ci-empty {
        background: var(--ci-panel);
        border: 1px solid var(--ci-line);
        box-shadow: 0 2px 0 #d4e2d5;
    }

    .ci-metric {
        min-height: 9.7rem;
        padding: 1.2rem 1.1rem 0.8rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .ci-metric h2 {
        margin: 0;
        font-size: 1rem;
        color: #243b29;
        font-weight: 800;
    }

    .ci-metric div {
        text-align: center;
        color: #132719;
    }

    .ci-metric strong {
        font-size: 2.9rem;
        line-height: 1;
        font-weight: 500;
    }

    .ci-metric span {
        font-size: 1.35rem;
        margin-left: 0.25rem;
    }

    .ci-metric p {
        margin: 0;
        text-align: center;
        color: var(--ci-muted);
        font-size: 0.92rem;
    }

    .ci-empty {
        padding: 1.4rem;
        margin-bottom: 0.6rem;
        text-align: center;
    }

    .ci-empty h2 {
        margin: 0 0 0.4rem;
        font-size: 1.2rem;
    }

    .ci-empty p {
        margin: 0 0 0.8rem;
        color: var(--ci-muted);
    }

    .ci-empty a {
        color: #2f7d32;
        font-weight: 800;
        text-decoration: none;
    }

    .ci-dashboard-grid {
        display: grid;
        grid-template-columns: 1.05fr 1.75fr 1.4fr;
        gap: 0.55rem;
    }

    .ci-panel {
        min-height: 17rem;
        overflow: hidden;
    }

    .ci-panel-head {
        min-height: 3.15rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0 1.1rem;
        border-bottom: 1px solid var(--ci-line);
    }

    .ci-panel-head h3 {
        margin: 0;
        font-size: 1rem;
        color: #243b29;
        font-weight: 900;
    }

    .ci-panel-head span {
        color: var(--ci-muted);
        font-size: 0.9rem;
    }

    .ci-bars {
        padding: 1rem 0.75rem 0.2rem;
    }

    .ci-bar-row {
        display: grid;
        grid-template-columns: minmax(5.3rem, 6.2rem) minmax(0, 1fr) minmax(2.9rem, auto);
        gap: 0.55rem;
        align-items: center;
        height: 1.45rem;
        color: #2f7d32;
        font-size: 0.8rem;
    }

    .ci-bar-row span {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ci-bar-track {
        height: 0.8rem;
        background: repeating-linear-gradient(90deg, #fff 0, #fff 24%, #e4efe5 24%, #e4efe5 24.7%);
        border-left: 1px solid #cbdccf;
        min-width: 0;
    }

    .ci-bar-track i {
        display: block;
        height: 100%;
        background: #5c9f43;
    }

    .ci-bar-row b {
        color: #617268;
        font-weight: 700;
        text-align: right;
    }

    .ci-axis {
        margin: 0.6rem 0 0;
        text-align: center;
        color: #647464;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .ci-table-panel table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 0.86rem;
    }

    .ci-table-panel th,
    .ci-table-panel td {
        padding: 0.62rem 1rem;
        border-bottom: 1px solid var(--ci-line);
        text-align: left;
        color: #526554;
        overflow-wrap: anywhere;
        vertical-align: middle;
    }

    .ci-table-panel th:first-child,
    .ci-table-panel td:first-child {
        width: 34%;
    }

    .ci-table-panel th:nth-child(2),
    .ci-table-panel td:nth-child(2) {
        width: 31%;
    }

    .ci-table-panel th:nth-child(3),
    .ci-table-panel td:nth-child(3) {
        width: 18%;
    }

    .ci-table-panel th:nth-child(4),
    .ci-table-panel td:nth-child(4) {
        width: 17%;
    }

    .ci-table-panel th {
        color: #536a56;
        font-weight: 900;
        background: #fbfdf9;
    }

    .ci-table-panel tr:nth-child(even) td {
        background: #f5faf5;
    }

    .ci-expand {
        display: inline-grid;
        place-items: center;
        width: 1rem;
        height: 1rem;
        border: 1px solid #849b86;
        margin-right: 0.7rem;
        color: #657865;
        font-weight: 900;
        line-height: 1;
    }

    .ci-status {
        display: inline-block;
        min-width: 4.2rem;
        text-align: center;
        border-radius: 999px;
        padding: 0.16rem 0.5rem;
        font-size: 0.74rem;
        font-weight: 900;
    }

    .ci-status.ok {
        background: #e4f2ea;
        color: #2b7650;
    }

    .ci-status.warning {
        background: #fff0d4;
        color: #9a641c;
    }

    .ci-status.danger {
        background: #fee2e2;
        color: #b32626;
    }

    .ci-status.muted {
        background: #edf1f6;
        color: #64748b;
    }

    .ci-trend {
        display: block;
        width: calc(100% - 2rem);
        height: 9.4rem;
        margin: 0.8rem 1rem 0.35rem;
    }

    .ci-trend line {
        stroke: #d9e5da;
        stroke-width: 1;
    }

    .ci-trend polyline {
        fill: none;
        stroke-width: 2.2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .ci-trend .soft {
        stroke-width: 1.7;
        opacity: 0.85;
    }

    .ci-trend .orange {
        stroke: #c7922d;
    }

    .ci-trend .dark {
        stroke: #223228;
    }

    .ci-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem 1.8rem;
        padding: 0.3rem 1.4rem 1rem;
        color: #2f7d32;
        font-size: 0.82rem;
    }

    .ci-legend span::before {
        content: "";
        display: inline-block;
        width: 0.55rem;
        height: 0.55rem;
        background: #5c9f43;
        border-radius: 0.12rem;
        margin-right: 0.35rem;
    }

    .ci-legend span:nth-child(2)::before {
        background: #c7922d;
    }

    .ci-legend span:nth-child(3)::before {
        background: #223228;
    }

    @media (max-width: 1280px) {
        .ci-metrics {
            grid-template-columns: repeat(3, minmax(12rem, 1fr));
        }

        .ci-dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 820px) {
        .ci-shell {
            grid-template-columns: 1fr;
            margin: -0.5rem;
        }

        .ci-sidebar {
            position: static;
            min-height: auto;
        }

        .ci-nav {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            padding: 0;
        }

        .ci-user {
            display: none;
        }

        .ci-topbar,
        .ci-breadcrumb,
        .ci-actions {
            flex-wrap: wrap;
        }

        .ci-topbar {
            padding: 0.8rem 1rem;
        }

        .ci-breadcrumb {
            row-gap: 0.35rem;
        }

        .ci-actions {
            gap: 0.8rem;
        }

        .ci-grid-icon {
            align-self: center;
            height: 2.5rem;
            margin-right: 0;
        }

        .ci-metrics {
            grid-template-columns: 1fr;
        }

        .ci-metric strong {
            font-size: 2.25rem;
        }
    }
</style>
