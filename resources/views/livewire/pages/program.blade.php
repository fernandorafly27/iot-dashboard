    <?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use App\Models\Program;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    #[Validate('required|string|min:3')]
    public string $name = '';

    #[Validate('required|string|unique:programs,code')]
    public string $code = '';

    #[Validate('required|string|min:5')]
    public string $description = '';


    #[Validate('required|integer|min:1')]
    public int $duration = 12;

    #[Validate('required|in:active,inactive,completed')]
    public string $status = 'active';

    public bool $showForm = false;
    public ?int $editingId = null;
    public string $search = '';

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            $program = Program::find($this->editingId);
            $program->update([
                'name' => $this->name,
                'code' => $this->code,
                'description' => $this->description,
                'duration' => $this->duration,
                'status' => $this->status,
            ]);
        } else {
            Program::create([
                'name' => $this->name,
                'code' => $this->code,
                'description' => $this->description,
                'duration' => $this->duration,
                'status' => $this->status,
                'user_id' => Auth::id(),
            ]);
        }

        $this->resetForm();
        $this->dispatch('programSaved');
    }

    public function edit($id)
    {
        $program = Program::find($id);
        if (!$program) return;

        $this->editingId = $id;
        $this->name = $program->name;
        $this->code = $program->code;
        $this->description = $program->description;
        $this->duration = $program->duration;
        $this->status = $program->status;
        $this->showForm = true;
    }

    public function delete($id)
    {
        Program::find($id)?->delete();
        $this->dispatch('programDeleted');
    }

    public function resetForm()
    {
        $this->name = '';
        $this->code = '';
        $this->description = '';
        $this->duration = 12;
        $this->status = 'active';
        $this->editingId = null;
        $this->showForm = false;
    }

    public function getPrograms()
    {
        $query = Program::query();
        
        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%");
        }

        return $query->paginate(10);
    }

    public function getLatestSensorReading()
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return null;
            }
            return \App\Models\SensorReading::where('user_id', $userId)
                ->latest()
                ->first();
        } catch (\Exception $e) {
            return null;
        }
    }


    public function getSensorHistory()
    {
        try {
            return \App\Models\SensorReading::where('user_id', Auth::id())
                ->latest()
                ->take(20)
                ->get()
                ->reverse()
                ->values();
        } catch (\Exception $e) {
            return collect();
        }
    }

    public function with()
    {
        return [
            'programs' => $this->getPrograms(),
            'totalPrograms' => Program::count(),
            'activePrograms' => Program::where('status', 'active')->count(),
            'latestSensor'  => $this->getLatestSensorReading(),
            'sensorHistory' => $this->getSensorHistory(),
        ];
    }
}; ?>

<div style="background: linear-gradient(to bottom right, #f8fafc, #f1f5f9); min-height: 100vh;">
    <!-- Top Bar -->
    <nav style="background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 40; border-bottom: 1px solid #e2e8f0;">
        <div style="max-width: 80rem; margin: 0 auto; padding: 1.5rem 2rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 1.875rem; font-weight: 700; color: #0f172a;">📋 Program Management</h1>
                <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Kelola semua program pembelajaran Anda</p>
            </div>
            <button 
                wire:click="create"
                style="background: #2563eb; color: white; padding: 0.5rem 1.5rem; border-radius: 0.5rem; font-weight: 600; border: none; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: background 0.2s;"
                onmouseover="this.style.background='#1d4ed8'"
                onmouseout="this.style.background='#2563eb'"
            >
                + Tambah Program
            </button>
        </div>
    </nav>

    <!-- Main Container -->
    <div style="max-width: 80rem; margin: 0 auto; padding: 2rem;">
        <!-- Water Quality Monitoring Section -->
        @if($latestSensor)
        <div style="background: linear-gradient(135deg, #1e3a8a, #0f172a); border-radius: 0.75rem; padding: 2rem; margin-bottom: 2rem; border: 1px solid #2563eb; box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 0.5rem;">
                    📊 Water Quality Monitoring
                </h2>
                <a href="{{ route('monitor') }}" style="background: #2563eb; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; font-weight: 600; font-size: 0.875rem; transition: background 0.2s;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                    📈 Lihat Detail
                </a>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                <!-- pH -->
                <div style="background: rgba(37, 99, 235, 0.1); border-radius: 0.5rem; padding: 1rem; border-left: 4px solid #3b82f6;">
                    <p style="color: #93c5fd; font-size: 0.75rem; font-weight: 600;">pH Level</p>
                    <h3 style="font-size: 1.875rem; font-weight: 700; color: #ffffff; margin-top: 0.5rem;">{{ number_format($latestSensor->ph, 2) }}</h3>
                    <p style="color: #60a5fa; font-size: 0.75rem; margin-top: 0.5rem;">Normal: 6.5-7.5</p>
                </div>

                <!-- Nutrient -->
                <div style="background: rgba(34, 197, 94, 0.1); border-radius: 0.5rem; padding: 1rem; border-left: 4px solid #22c55e;">
                    <p style="color: #86efac; font-size: 0.75rem; font-weight: 600;">Nutrisi (EC)</p>
                    <h3 style="font-size: 1.875rem; font-weight: 700; color: #ffffff; margin-top: 0.5rem;">{{ number_format($latestSensor->nutrient, 2) }}</h3>
                    <p style="color: #86efac; font-size: 0.75rem; margin-top: 0.5rem;">mS/cm</p>
                </div>

                <!-- Turbidity -->
                <div style="background: rgba(180, 83, 9, 0.1); border-radius: 0.5rem; padding: 1rem; border-left: 4px solid #b45309;">
                    <p style="color: #fbbf24; font-size: 0.75rem; font-weight: 600;">Kekeruhan</p>
                    <h3 style="font-size: 1.875rem; font-weight: 700; color: #ffffff; margin-top: 0.5rem;">{{ number_format($latestSensor->turbidity, 2) }}</h3>
                    <p style="color: #fbbf24; font-size: 0.75rem; margin-top: 0.5rem;">NTU</p>
                </div>

                <!-- PWM -->
                <div style="background: rgba(239, 68, 68, 0.1); border-radius: 0.5rem; padding: 1rem; border-left: 4px solid #ef4444;">
                    <p style="color: #fca5a5; font-size: 0.75rem; font-weight: 600;">PWM Pompa</p>
                    <h3 style="font-size: 1.875rem; font-weight: 700; color: #ffffff; margin-top: 0.5rem;">{{ number_format($latestSensor->pump_pwm, 1) }}%</h3>
                    <div style="background: rgba(0,0,0,0.3); height: 0.375rem; border-radius: 9999px; margin-top: 0.5rem; overflow: hidden;">
                        <div style="background: #fca5a5; height: 100%; width: {{ $latestSensor->pump_pwm }}%;"></div>
                    </div>
                </div>

                <!-- Humidity -->
                <div style="background: rgba(6, 182, 212, 0.1); border-radius: 0.5rem; padding: 1rem; border-left: 4px solid #06b6d4;">
                    <p style="color: #a5f3fc; font-size: 0.75rem; font-weight: 600;">Kelembaban</p>
                    <h3 style="font-size: 1.875rem; font-weight: 700; color: #ffffff; margin-top: 0.5rem;">{{ number_format($latestSensor->humidity, 1) }}%</h3>
                    <div style="background: rgba(0,0,0,0.3); height: 0.375rem; border-radius: 9999px; margin-top: 0.5rem; overflow: hidden;">
                        <div style="background: #a5f3fc; height: 100%; width: {{ $latestSensor->humidity }}%;"></div>
                    </div>
                </div>
            </div>
            <p style="color: #60a5fa; font-size: 0.75rem; margin-top: 1rem;">Update terakhir: {{ $latestSensor->created_at->format('d/m/Y H:i') }}</p>
        </div>
        @endif


        {{-- Grafik Sensor --}}
        @if(isset($sensorHistory) && $sensorHistory->count() > 1)
        <div style="background: white; border-radius: 0.75rem; border: 1px solid #e2e8f0; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.07);">
            <h2 style="font-size: 1.125rem; font-weight: 700; color: #0f172a; margin-bottom: 1rem;">📈 Grafik Sensor (20 Data Terakhir)</h2>
            <canvas id="sensorChart" style="width:100%; max-height:240px;"></canvas>
        </div>
        @endif

        {{-- Histori Sensor --}}
        @if(isset($sensorHistory) && $sensorHistory->count() > 0)
        <div style="background: white; border-radius: 0.75rem; border: 1px solid #e2e8f0; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.07);">
            <h2 style="font-size: 1.125rem; font-weight: 700; color: #0f172a; margin-bottom: 1rem;">📋 Histori Sensor ({{ $sensorHistory->count() }} data terakhir)</h2>
            <div style="overflow-x: auto;">
                <table style="width:100%; border-collapse:collapse; font-size:0.8rem;">
                    <thead>
                        <tr style="background:#f1f5f9;">
                            <th style="padding:0.6rem 1rem; text-align:left; color:#475569; font-weight:600; border-bottom:1px solid #e2e8f0;">Waktu</th>
                            <th style="padding:0.6rem 1rem; text-align:center; color:#1e40af; font-weight:600; border-bottom:1px solid #e2e8f0;">pH</th>
                            <th style="padding:0.6rem 1rem; text-align:center; color:#15803d; font-weight:600; border-bottom:1px solid #e2e8f0;">Nutrisi</th>
                            <th style="padding:0.6rem 1rem; text-align:center; color:#92400e; font-weight:600; border-bottom:1px solid #e2e8f0;">Kekeruhan</th>
                            <th style="padding:0.6rem 1rem; text-align:center; color:#9d174d; font-weight:600; border-bottom:1px solid #e2e8f0;">PWM</th>
                            <th style="padding:0.6rem 1rem; text-align:center; color:#0369a1; font-weight:600; border-bottom:1px solid #e2e8f0;">Kelembaban</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sensorHistory->reverse() as $reading)
                        <tr style="border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                            <td style="padding:0.6rem 1rem; color:#64748b;">{{ $reading->created_at->format('d/m H:i:s') }}</td>
                            <td style="padding:0.6rem 1rem; text-align:center; font-weight:600; color:#1e3a8a;">{{ number_format($reading->ph, 2) }}</td>
                            <td style="padding:0.6rem 1rem; text-align:center; font-weight:600; color:#166534;">{{ number_format($reading->nutrient, 2) }}</td>
                            <td style="padding:0.6rem 1rem; text-align:center; font-weight:600; color:#78350f;">{{ number_format($reading->turbidity, 2) }}</td>
                            <td style="padding:0.6rem 1rem; text-align:center; font-weight:600; color:#831843;">{{ number_format($reading->pump_pwm, 1) }}%</td>
                            <td style="padding:0.6rem 1rem; text-align:center; font-weight:600; color:#075985;">{{ number_format($reading->humidity, 1) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div style="background: white; border-left: 4px solid #2563eb; border-radius: 0.5rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;">
                <p style="color: #64748b; font-size: 0.875rem; font-weight: 600;">Total Program</p>
                <p style="font-size: 2.25rem; font-weight: 700; color: #2563eb; margin-top: 0.75rem;">{{ $totalPrograms }}</p>
                <p style="color: #94a3b8; font-size: 0.75rem; margin-top: 0.5rem;">Program telah dibuat</p>
            </div>
            <div style="background: white; border-left: 4px solid #16a34a; border-radius: 0.5rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;">
                <p style="color: #64748b; font-size: 0.875rem; font-weight: 600;">Program Aktif</p>
                <p style="font-size: 2.25rem; font-weight: 700; color: #16a34a; margin-top: 0.75rem;">{{ $activePrograms }}</p>
                <p style="color: #94a3b8; font-size: 0.75rem; margin-top: 0.5rem;">Sedang berjalan</p>
            </div>
        </div>

        <!-- Search Bar -->
        <div style="margin-bottom: 2rem;">
            <input 
                type="text"
                wire:model.live="search"
                placeholder="🔍 Cari program berdasarkan nama atau kode..."
                style="width: 100%; padding: 0.75rem 1rem; background: white; border: 2px solid #e2e8f0; color: #0f172a; border-radius: 0.5rem; font-size: 0.875rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);"
                onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)'"
                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)'"
            />
        </div>

        <!-- Form Modal -->
        @if ($showForm)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; z-index: 50;">
            <div style="background: white; border-radius: 0.75rem; padding: 2rem; width: 100%; max-width: 28rem; box-shadow: 0 20px 25px rgba(0,0,0,0.15); border: 1px solid #e2e8f0;">
                <h2 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-bottom: 1.5rem;">{{ $editingId ? '✏️ Edit Program' : '➕ Tambah Program Baru' }}</h2>

                <form wire:submit="save" style="display: flex; flex-direction: column; gap: 1rem;">
                    <!-- Name -->
                    <div>
                        <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #0f172a; margin-bottom: 0.5rem;">Nama Program</label>
                        <input 
                            type="text"
                            wire:model="name"
                            style="width: 100%; padding: 0.5rem; background: white; border: 2px solid #e2e8f0; color: #0f172a; border-radius: 0.5rem;"
                        />
                        @error('name') <span style="color: #ef4444; font-size: 0.75rem;">{{ $message }}</span> @enderror
                    </div>

                    <!-- Code -->
                    <div>
                        <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #0f172a; margin-bottom: 0.5rem;">Kode Program</label>
                        <input 
                            type="text"
                            wire:model="code"
                            style="width: 100%; padding: 0.5rem; background: white; border: 2px solid #e2e8f0; color: #0f172a; border-radius: 0.5rem;"
                        />
                        @error('code') <span style="color: #ef4444; font-size: 0.75rem;">{{ $message }}</span> @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #0f172a; margin-bottom: 0.5rem;">Deskripsi</label>
                        <textarea 
                            wire:model="description"
                            rows="3"
                            style="width: 100%; padding: 0.5rem; background: white; border: 2px solid #e2e8f0; color: #0f172a; border-radius: 0.5rem;"
                        ></textarea>
                        @error('description') <span style="color: #ef4444; font-size: 0.75rem;">{{ $message }}</span> @enderror
                    </div>

                    <!-- Sensor Status Panel -->
                    <div style="border: 2px solid #e2e8f0; border-radius: 0.5rem; padding: 1rem; background: #f8fafc;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                            <label style="font-size: 0.875rem; font-weight: 600; color: #0f172a;">📡 Status Sensor</label>
                            @if($latestSensor)
                                <span style="display: inline-flex; align-items: center; gap: 4px; background: #dcfce7; color: #166534; font-size: 0.75rem; padding: 2px 10px; border-radius: 9999px; font-weight: 600;">
                                    <span style="width:7px;height:7px;border-radius:50%;background:#16a34a;display:inline-block;"></span> Online
                                </span>
                            @else
                                <span style="display: inline-flex; align-items: center; gap: 4px; background: #fee2e2; color: #991b1b; font-size: 0.75rem; padding: 2px 10px; border-radius: 9999px; font-weight: 600;">
                                    <span style="width:7px;height:7px;border-radius:50%;background:#dc2626;display:inline-block;"></span> Offline
                                </span>
                            @endif
                        </div>

                        @if($latestSensor)
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                            <!-- pH -->
                            <div style="background: #dbeafe; border-radius: 0.375rem; padding: 0.5rem 0.75rem;">
                                <p style="font-size: 0.65rem; color: #1e40af; font-weight: 600; margin-bottom: 2px;">pH Air</p>
                                <p style="font-size: 1.1rem; font-weight: 700; color: #1e3a8a;">{{ number_format($latestSensor->ph, 2) }}</p>
                            </div>
                            <!-- Nutrisi -->
                            <div style="background: #dcfce7; border-radius: 0.375rem; padding: 0.5rem 0.75rem;">
                                <p style="font-size: 0.65rem; color: #15803d; font-weight: 600; margin-bottom: 2px;">Nutrisi (EC)</p>
                                <p style="font-size: 1.1rem; font-weight: 700; color: #166534;">{{ number_format($latestSensor->nutrient, 2) }} <span style="font-size:0.65rem;">mS/cm</span></p>
                            </div>
                            <!-- Kekeruhan -->
                            <div style="background: #fef3c7; border-radius: 0.375rem; padding: 0.5rem 0.75rem;">
                                <p style="font-size: 0.65rem; color: #92400e; font-weight: 600; margin-bottom: 2px;">Kekeruhan</p>
                                <p style="font-size: 1.1rem; font-weight: 700; color: #78350f;">{{ number_format($latestSensor->turbidity, 2) }} <span style="font-size:0.65rem;">NTU</span></p>
                            </div>
                            <!-- Kelembaban -->
                            <div style="background: #e0f2fe; border-radius: 0.375rem; padding: 0.5rem 0.75rem;">
                                <p style="font-size: 0.65rem; color: #0369a1; font-weight: 600; margin-bottom: 2px;">Kelembaban</p>
                                <p style="font-size: 1.1rem; font-weight: 700; color: #075985;">{{ number_format($latestSensor->humidity, 1) }}<span style="font-size:0.65rem;">%</span></p>
                            </div>
                        </div>
                        <!-- PWM Pompa -->
                        <div style="margin-top: 0.5rem; background: #fce7f3; border-radius: 0.375rem; padding: 0.5rem 0.75rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                <p style="font-size: 0.65rem; color: #9d174d; font-weight: 600;">PWM Pompa</p>
                                <p style="font-size: 0.875rem; font-weight: 700; color: #831843;">{{ number_format($latestSensor->pump_pwm, 1) }}%</p>
                            </div>
                            <div style="background: #fbcfe8; height: 6px; border-radius: 9999px; overflow: hidden;">
                                <div style="background: #db2777; height: 100%; width: {{ $latestSensor->pump_pwm }}%;"></div>
                            </div>
                        </div>
                        <p style="font-size: 0.65rem; color: #94a3b8; margin-top: 0.5rem;">Update: {{ $latestSensor->created_at->format('d/m/Y H:i') }}</p>
                        @else
                        <p style="color: #94a3b8; font-size: 0.875rem; text-align: center; padding: 0.5rem 0;">Tidak ada data sensor tersedia</p>
                        @endif
                    </div>

                    <!-- Duration -->
                    <div>
                        <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #0f172a; margin-bottom: 0.5rem;">Durasi (bulan)</label>
                        <input 
                            type="number"
                            wire:model="duration"
                            style="width: 100%; padding: 0.5rem; background: white; border: 2px solid #e2e8f0; color: #0f172a; border-radius: 0.5rem;"
                        />
                        @error('duration') <span style="color: #ef4444; font-size: 0.75rem;">{{ $message }}</span> @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #0f172a; margin-bottom: 0.5rem;">Status</label>
                        <select 
                            wire:model="status"
                            style="width: 100%; padding: 0.5rem; background: white; border: 2px solid #e2e8f0; color: #0f172a; border-radius: 0.5rem;"
                        >
                            <option value="active">✅ Aktif</option>
                            <option value="inactive">⏸️ Tidak Aktif</option>
                            <option value="completed">✔️ Selesai</option>
                        </select>
                        @error('status') <span style="color: #ef4444; font-size: 0.75rem;">{{ $message }}</span> @enderror
                    </div>

                    <!-- Buttons -->
                    <div style="display: flex; gap: 0.75rem; padding-top: 1rem;">
                        <button 
                            type="button"
                            wire:click="resetForm"
                            style="flex: 1; padding: 0.5rem 1rem; background: #e2e8f0; color: #0f172a; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600; transition: background 0.2s;"
                            onmouseover="this.style.background='#cbd5e1'"
                            onmouseout="this.style.background='#e2e8f0'"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit"
                            style="flex: 1; padding: 0.5rem 1rem; background: #2563eb; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600; transition: background 0.2s;"
                            onmouseover="this.style.background='#1d4ed8'"
                            onmouseout="this.style.background='#2563eb'"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif


        <!-- Programs Table -->
        <div style="background: white; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f1f5f9; border-bottom: 1px solid #e2e8f0;">
                    <tr>
                        <th style="padding: 1rem 1.5rem; text-align: left; font-weight: 600; color: #0f172a; font-size: 0.875rem;">Nama Program</th>
                        <th style="padding: 1rem 1.5rem; text-align: left; font-weight: 600; color: #0f172a; font-size: 0.875rem;">Kode</th>
                        <th style="padding: 1rem 1.5rem; text-align: left; font-weight: 600; color: #0f172a; font-size: 0.875rem;">Durasi</th>
                        <th style="padding: 1rem 1.5rem; text-align: left; font-weight: 600; color: #0f172a; font-size: 0.875rem;">Status</th>
                        <th style="padding: 1rem 1.5rem; text-align: center; font-weight: 600; color: #0f172a; font-size: 0.875rem;">Aksi</th>
                    </tr>
                </thead>
                <tbody style="border-collapse: collapse;">
                    @forelse($programs as $program)
                    <tr style="border-bottom: 1px solid #e2e8f0; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                        <td style="padding: 1rem 1.5rem; font-weight: 600; color: #0f172a;">{{ $program->name }}</td>
                        <td style="padding: 1rem 1.5rem;">
                            <span style="background: #dbeafe; color: #1e40af; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600;">{{ $program->code }}</span>
                        </td>
                        <td style="padding: 1rem 1.5rem; color: #475569;">{{ $program->category ?? '-' }}</td>
                        <td style="padding: 1rem 1.5rem; color: #475569;">{{ $program->duration }} bulan</td>
                        <td style="padding: 1rem 1.5rem;">
                            @if($program->status === 'active')
                                <span style="background: #dcfce7; color: #166534; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600;">✅ Aktif</span>
                            @elseif($program->status === 'inactive')
                                <span style="background: #fee2e2; color: #991b1b; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600;">⏸️ Tidak Aktif</span>
                            @else
                                <span style="background: #dbeafe; color: #1e40af; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600;">✔️ Selesai</span>
                            @endif
                        </td>
                        <td style="padding: 1rem 1.5rem; text-align: center;">
                            <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                <button 
                                    wire:click="edit({{ $program->id }})"
                                    style="padding: 0.25rem 0.75rem; background: #2563eb; color: white; border: none; border-radius: 0.25rem; cursor: pointer; font-size: 0.875rem; font-weight: 600; transition: background 0.2s;"
                                    onmouseover="this.style.background='#1d4ed8'"
                                    onmouseout="this.style.background='#2563eb'"
                                >
                                    ✏️ Edit
                                </button>
                                <button 
                                    wire:click="delete({{ $program->id }})"
                                    onclick="confirm('Yakin hapus program ini?') || event.stopImmediatePropagation()"
                                    style="padding: 0.25rem 0.75rem; background: #dc2626; color: white; border: none; border-radius: 0.25rem; cursor: pointer; font-size: 0.875rem; font-weight: 600; transition: background 0.2s;"
                                    onmouseover="this.style.background='#b91c1c'"
                                    onmouseout="this.style.background='#dc2626'"
                                >
                                    🗑️ Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding: 3rem 1.5rem; text-align: center; color: #64748b;">
                            <p style="font-size: 1.125rem;">📭 Belum ada program</p>
                            <p style="font-size: 0.875rem;">Klik "Tambah Program" untuk membuat yang baru</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div style="margin-top: 2rem;">
            {{ $programs->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
(function() {
    @if(isset($sensorHistory) && $sensorHistory->count() > 1)
    const labels   = @json($sensorHistory->pluck('created_at')->map(fn($d) => \Carbon\Carbon::parse($d)->format('H:i')));
    const phData   = @json($sensorHistory->pluck('ph'));
    const nutData  = @json($sensorHistory->pluck('nutrient'));
    const turData  = @json($sensorHistory->pluck('turbidity'));
    const humData  = @json($sensorHistory->pluck('humidity'));

    const ctx = document.getElementById('sensorChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    { label: 'pH', data: phData, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.08)', tension: 0.4, pointRadius: 3 },
                    { label: 'Nutrisi', data: nutData, borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.08)', tension: 0.4, pointRadius: 3 },
                    { label: 'Kekeruhan', data: turData, borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.08)', tension: 0.4, pointRadius: 3 },
                    { label: 'Kelembaban', data: humData, borderColor: '#0284c7', backgroundColor: 'rgba(2,132,199,0.08)', tension: 0.4, pointRadius: 3 },
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
                scales: {
                    x: { ticks: { font: { size: 10 }, maxTicksLimit: 10 } },
                    y: { ticks: { font: { size: 10 } } }
                }
            }
        });
    }
    @endif
})();
</script>
<style>
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.5} }
</style>
@endpush
