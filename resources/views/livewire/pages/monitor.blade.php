<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use App\Models\SensorReading;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    #[Validate('required|numeric|between:0,14')]
    public string $ph = '';

    #[Validate('required|numeric|min:0')]
    public string $nutrient = '';

    #[Validate('required|numeric|min:0')]
    public string $turbidity = '';

    #[Validate('required|numeric|between:0,100')]
    public string $pump_pwm = '';

    #[Validate('required|numeric|between:0,100')]
    public string $humidity = '';

    public bool $showForm = false;

    public function addReading()
    {
        $this->validate();

        SensorReading::create([
            'user_id' => Auth::id(),
            'ph' => $this->ph,
            'nutrient' => $this->nutrient,
            'turbidity' => $this->turbidity,
            'pump_pwm' => $this->pump_pwm,
            'humidity' => $this->humidity,
            'status' => 'normal',
        ]);

        $this->resetForm();
        $this->dispatch('readingAdded');
    }

    public function resetForm()
    {
        $this->ph = '';
        $this->nutrient = '';
        $this->turbidity = '';
        $this->pump_pwm = '';
        $this->humidity = '';
        $this->showForm = false;
    }

    public function deleteReading($id)
    {
        SensorReading::find($id)?->delete();
    }

    public function getLatestReading()
    {
        return SensorReading::where('user_id', Auth::id())
            ->latest()
            ->first();
    }

    public function with()
    {
        return [
            'latest' => $this->getLatestReading(),
            'readings' => SensorReading::where('user_id', Auth::id())
                ->latest()
                ->paginate(10),
            'totalReadings' => SensorReading::where('user_id', Auth::id())->count(),
        ];
    }
}; ?>

<div class="hm-shell">
    <aside class="hm-sidebar">
        <div class="hm-brand">
            <div class="hm-brand-mark">H</div>
            <div>
                <strong>HYDROGENESIS</strong>
                <span>DFT</span>
            </div>
        </div>

        <nav class="hm-nav">
            <a href="{{ route('dashboard') }}"><span>⌂</span> Home</a>
            <a href="{{ route('monitor') }}" class="active"><span>◉</span> Monitoring</a>
            <button wire:click="$set('showForm', true)"><span>＋</span> Add Data</button>
        </nav>

        <div class="hm-user">
            <div class="hm-avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
            <div>
                <strong>{{ Auth::user()->name }}</strong>
                <span>{{ Auth::user()->email }}</span>
            </div>
        </div>
    </aside>

    <section class="hm-main" wire:poll.5s>
        <header class="hm-topbar">
            <div class="hm-breadcrumb">
                <span>HydroGenesis DFT</span>
                <em>/</em>
                <span>Sensor Readings</span>
                <em>/</em>
                <strong>Water Quality Monitoring</strong>
            </div>
            <div class="hm-actions">
                <span>{{ $totalReadings }} Readings</span>
                <button wire:click="$set('showForm', true)">Tambah Data</button>
                <span class="hm-grid-icon">▦</span>
            </div>
        </header>

        <main class="hm-content">
            @if($latest)
                <section class="hm-metrics">
                    <article class="hm-metric">
                        <h2>pH</h2>
                        <div><strong>{{ number_format($latest->ph, 2) }}</strong></div>
                        <p>Keasaman Larutan - Latest</p>
                    </article>
                    <article class="hm-metric">
                        <h2>EC</h2>
                        <div><strong>{{ number_format($latest->nutrient, 2) }}</strong><span>mS/cm</span></div>
                        <p>Nutrisi - Latest</p>
                    </article>
                    <article class="hm-metric">
                        <h2>Turbidity</h2>
                        <div><strong>{{ number_format($latest->turbidity, 2) }}</strong><span>NTU</span></div>
                        <p>Kekeruhan - Latest</p>
                    </article>
                    <article class="hm-metric">
                        <h2>Pump PWM</h2>
                        <div><strong>{{ number_format($latest->pump_pwm, 1) }}</strong><span>%</span></div>
                        <p>Aktivasi Pompa - Latest</p>
                    </article>
                    <article class="hm-metric">
                        <h2>Humidity</h2>
                        <div><strong>{{ number_format($latest->humidity, 1) }}</strong><span>%</span></div>
                        <p>Kelembaban - Latest</p>
                    </article>
                </section>
            @else
                <section class="hm-empty">
                    <h2>Belum ada data sensor</h2>
                    <p>Tambahkan pembacaan pertama agar dashboard analitik terisi.</p>
                    <button wire:click="$set('showForm', true)">Tambah Data Sensor</button>
                </section>
            @endif

            @if($showForm)
                <section class="hm-form-panel">
                    <div class="hm-panel-head">
                        <h3>Tambah Data Sensor</h3>
                        <button type="button" wire:click="resetForm">×</button>
                    </div>

                    <form wire:submit="addReading" class="hm-form">
                        <label>
                            <span>pH Level (0-14)</span>
                            <input type="number" wire:model="ph" step="0.01" min="0" max="14" />
                            @error('ph') <b>{{ $message }}</b> @enderror
                        </label>

                        <label>
                            <span>Nutrisi (EC) mS/cm</span>
                            <input type="number" wire:model="nutrient" step="0.01" min="0" />
                            @error('nutrient') <b>{{ $message }}</b> @enderror
                        </label>

                        <label>
                            <span>Kekeruhan (NTU)</span>
                            <input type="number" wire:model="turbidity" step="0.01" min="0" />
                            @error('turbidity') <b>{{ $message }}</b> @enderror
                        </label>

                        <label>
                            <span>PWM Pompa (0-100%)</span>
                            <input type="number" wire:model="pump_pwm" step="0.1" min="0" max="100" />
                            @error('pump_pwm') <b>{{ $message }}</b> @enderror
                        </label>

                        <label>
                            <span>Kelembaban (0-100%)</span>
                            <input type="number" wire:model="humidity" step="0.1" min="0" max="100" />
                            @error('humidity') <b>{{ $message }}</b> @enderror
                        </label>

                        <div class="hm-form-actions">
                            <button type="button" wire:click="resetForm" class="hm-secondary">Batal</button>
                            <button type="submit" class="hm-primary">Simpan Data</button>
                        </div>
                    </form>
                </section>
            @endif

            <section class="hm-panel">
                <div class="hm-panel-head">
                    <h3>Riwayat Pembacaan</h3>
                    <span>{{ $totalReadings }} data sensor</span>
                </div>
                <div class="hm-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>pH</th>
                                <th>Nutrisi</th>
                                <th>Kekeruhan</th>
                                <th>PWM</th>
                                <th>Kelembaban</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($readings as $reading)
                                <tr>
                                    <td>{{ $reading->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ number_format($reading->ph, 2) }}</td>
                                    <td>{{ number_format($reading->nutrient, 2) }}</td>
                                    <td>{{ number_format($reading->turbidity, 2) }}</td>
                                    <td>{{ number_format($reading->pump_pwm, 1) }}%</td>
                                    <td>{{ number_format($reading->humidity, 1) }}%</td>
                                    <td>
                                        <button
                                            wire:click="deleteReading({{ $reading->id }})"
                                            onclick="confirm('Hapus data ini?') || event.stopImmediatePropagation()"
                                            class="hm-delete"
                                        >
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="hm-empty-row">Belum ada riwayat pembacaan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="hm-pagination">
                {{ $readings->links() }}
            </div>
        </main>
    </section>
</div>

<style>
    .hm-shell {
        --hm-panel: #ffffff;
        --hm-line: #cbdccf;
        --hm-text: #263829;
        --hm-muted: #778b7d;
        --hm-green: #2f7d32;
        min-height: 100vh;
        margin: -1rem;
        background: #eef6ed;
        color: var(--hm-text);
        display: grid;
        grid-template-columns: 16.5rem minmax(0, 1fr);
        font-family: Arial, Helvetica, sans-serif;
    }

    .hm-sidebar {
        background: #102417;
        color: #fff;
        min-height: 100vh;
        position: sticky;
        top: 0;
        display: flex;
        flex-direction: column;
    }

    .hm-brand {
        height: 4rem;
        background: #2f7d32;
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 0 1rem;
        min-width: 0;
    }

    .hm-brand-mark {
        width: 2.1rem;
        height: 2.1rem;
        background: #fff;
        color: #2f7d32;
        display: grid;
        place-items: center;
        font-weight: 900;
    }

    .hm-brand strong,
    .hm-brand span {
        display: block;
        line-height: 1;
        font-size: 1rem;
        letter-spacing: 0;
        white-space: nowrap;
    }

    .hm-nav {
        display: flex;
        flex-direction: column;
        padding-top: 0.9rem;
    }

    .hm-nav a,
    .hm-nav button {
        height: 3.5rem;
        color: #fff;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0 1.1rem;
        border: 0;
        border-left: 3px solid transparent;
        background: transparent;
        font-size: 0.9rem;
        font-weight: 800;
        text-transform: uppercase;
        cursor: pointer;
        font-family: inherit;
    }

    .hm-nav a.active,
    .hm-nav a:hover,
    .hm-nav button:hover {
        background: #24402c;
        border-left-color: #8bc34a;
    }

    .hm-nav span {
        width: 1.3rem;
        text-align: center;
        font-size: 1.25rem;
        font-weight: 700;
    }

    .hm-user {
        margin-top: auto;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 0.7rem;
        border-top: 1px solid #284431;
    }

    .hm-avatar {
        width: 2.1rem;
        height: 2.1rem;
        border-radius: 50%;
        background: #fff;
        color: #102417;
        display: grid;
        place-items: center;
        font-weight: 900;
    }

    .hm-user strong,
    .hm-user span {
        display: block;
        max-width: 11rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .hm-user span {
        color: #b8c9bb;
        font-size: 0.76rem;
    }

    .hm-main {
        min-width: 0;
    }

    .hm-topbar {
        min-height: 4rem;
        background: #fff;
        border-bottom: 1px solid var(--hm-line);
        box-shadow: 0 1px 4px rgba(30, 41, 59, 0.12);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 0 1.1rem;
    }

    .hm-breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        min-width: 0;
        overflow: hidden;
        font-size: 0.98rem;
        color: #3e5a45;
    }

    .hm-breadcrumb span,
    .hm-breadcrumb strong {
        white-space: nowrap;
    }

    .hm-breadcrumb strong {
        color: #243b29;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .hm-breadcrumb em {
        color: #8c9b8f;
        font-style: normal;
    }

    .hm-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
        color: #3d5742;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .hm-actions button,
    .hm-primary,
    .hm-empty button {
        background: #2f7d32;
        color: #fff;
        border: 0;
        border-radius: 0.4rem;
        padding: 0.62rem 1rem;
        font-weight: 800;
        cursor: pointer;
    }

    .hm-actions button:hover,
    .hm-primary:hover,
    .hm-empty button:hover {
        background: #256b29;
    }

    .hm-grid-icon {
        background: #4f9d45;
        color: #fff;
        align-self: stretch;
        display: grid;
        place-items: center;
        width: 3rem;
        margin-right: -1.1rem;
    }

    .hm-content {
        padding: 0.9rem 1.1rem 1.1rem;
    }

    .hm-metrics {
        display: grid;
        grid-template-columns: repeat(5, minmax(10rem, 1fr));
        gap: 0.55rem;
        margin-bottom: 0.55rem;
    }

    .hm-metric,
    .hm-panel,
    .hm-form-panel,
    .hm-empty {
        background: var(--hm-panel);
        border: 1px solid var(--hm-line);
        box-shadow: 0 2px 0 #d4e2d5;
    }

    .hm-metric {
        min-height: 9.7rem;
        padding: 1.2rem 1.1rem 0.8rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .hm-metric h2,
    .hm-panel-head h3 {
        margin: 0;
        color: #243b29;
        font-weight: 900;
    }

    .hm-metric h2 {
        font-size: 1rem;
    }

    .hm-metric div {
        text-align: center;
        color: #132719;
    }

    .hm-metric strong {
        font-size: 2.9rem;
        line-height: 1;
        font-weight: 500;
    }

    .hm-metric span {
        font-size: 1.2rem;
        margin-left: 0.25rem;
    }

    .hm-metric p {
        margin: 0;
        text-align: center;
        color: var(--hm-muted);
        font-size: 0.92rem;
    }

    .hm-empty {
        padding: 3rem 1.4rem;
        margin-bottom: 0.6rem;
        text-align: center;
    }

    .hm-empty h2 {
        margin: 0 0 0.4rem;
        font-size: 1.25rem;
    }

    .hm-empty p {
        margin: 0 0 1rem;
        color: var(--hm-muted);
    }

    .hm-form-panel,
    .hm-panel {
        margin-top: 0.6rem;
    }

    .hm-panel-head {
        min-height: 3.15rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0 1.1rem;
        border-bottom: 1px solid var(--hm-line);
    }

    .hm-panel-head span {
        color: var(--hm-muted);
        font-size: 0.9rem;
    }

    .hm-panel-head button {
        border: 0;
        background: transparent;
        color: #657865;
        cursor: pointer;
        font-size: 1.4rem;
        line-height: 1;
    }

    .hm-form {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        padding: 1.1rem;
    }

    .hm-form label {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
        color: #536a56;
        font-size: 0.86rem;
        font-weight: 800;
    }

    .hm-form input {
        width: 100%;
        border: 1px solid #cbdccf;
        background: #fbfdf9;
        color: #132719;
        border-radius: 0.4rem;
        padding: 0.72rem 0.8rem;
        outline: none;
    }

    .hm-form input:focus {
        border-color: #4f9d45;
        box-shadow: 0 0 0 3px rgba(79, 157, 69, 0.16);
    }

    .hm-form b {
        color: #b32626;
        font-size: 0.76rem;
    }

    .hm-form-actions {
        grid-column: 1 / -1;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        padding-top: 0.3rem;
    }

    .hm-secondary {
        background: #e8efe7;
        color: #3d5742;
        border: 0;
        border-radius: 0.4rem;
        padding: 0.62rem 1rem;
        font-weight: 800;
        cursor: pointer;
    }

    .hm-table-wrap {
        overflow-x: auto;
    }

    .hm-panel table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.86rem;
    }

    .hm-panel th,
    .hm-panel td {
        padding: 0.72rem 1rem;
        border-bottom: 1px solid var(--hm-line);
        text-align: left;
        color: #526554;
        white-space: nowrap;
    }

    .hm-panel th {
        color: #536a56;
        font-weight: 900;
        background: #fbfdf9;
    }

    .hm-panel tr:nth-child(even) td {
        background: #f5faf5;
    }

    .hm-delete {
        background: #fee2e2;
        color: #b32626;
        border: 0;
        border-radius: 0.35rem;
        padding: 0.42rem 0.7rem;
        font-weight: 800;
        cursor: pointer;
    }

    .hm-empty-row {
        text-align: center !important;
        color: var(--hm-muted) !important;
        padding: 3rem 1rem !important;
    }

    .hm-pagination {
        margin-top: 1.2rem;
    }

    @media (max-width: 1280px) {
        .hm-metrics {
            grid-template-columns: repeat(2, minmax(12rem, 1fr));
        }
    }

    @media (max-width: 820px) {
        .hm-shell {
            grid-template-columns: 1fr;
            margin: -0.5rem;
        }

        .hm-sidebar {
            position: static;
            min-height: auto;
        }

        .hm-nav {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            padding: 0;
        }

        .hm-user {
            display: none;
        }

        .hm-topbar,
        .hm-breadcrumb,
        .hm-actions {
            flex-wrap: wrap;
        }

        .hm-topbar {
            padding: 0.8rem 1rem;
        }

        .hm-grid-icon {
            align-self: center;
            height: 2.5rem;
            margin-right: 0;
        }

        .hm-metrics,
        .hm-form {
            grid-template-columns: 1fr;
        }

        .hm-metric strong {
            font-size: 2.25rem;
        }
    }
</style>
