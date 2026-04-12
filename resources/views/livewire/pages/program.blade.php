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

    #[Validate('required|string')]
    public string $category = '';

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
                'category' => $this->category,
                'duration' => $this->duration,
                'status' => $this->status,
            ]);
        } else {
            Program::create([
                'name' => $this->name,
                'code' => $this->code,
                'description' => $this->description,
                'category' => $this->category,
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
        $this->category = $program->category;
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
        $this->category = '';
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

    public function with()
    {
        return [
            'programs' => $this->getPrograms(),
            'totalPrograms' => Program::count(),
            'activePrograms' => Program::where('status', 'active')->count(),
            'categories' => ['Development', 'Management', 'Design', 'Marketing', 'Others'],
        ];
    }
}; ?>

<div class="min-h-screen bg-slate-900 text-white">
    <!-- Top Bar -->
    <div class="bg-slate-800 shadow-lg border-b border-slate-700 px-8 py-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-blue-400">Program Management</h1>
            <button 
                wire:click="create"
                class="bg-blue-600 hover:bg-blue-700 px-6 py-2 rounded-lg font-medium transition"
            >
                + Tambah Program
            </button>
        </div>
    </div>

    <!-- Statistics -->
    <div class="px-8 py-6 grid grid-cols-2 gap-6">
        <div class="bg-slate-800 border border-slate-700 rounded-lg p-6">
            <p class="text-slate-400 text-sm">Total Program</p>
            <p class="text-4xl font-bold text-blue-400 mt-2">{{ $totalPrograms }}</p>
        </div>
        <div class="bg-slate-800 border border-slate-700 rounded-lg p-6">
            <p class="text-slate-400 text-sm">Program Aktif</p>
            <p class="text-4xl font-bold text-green-400 mt-2">{{ $activePrograms }}</p>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="px-8 py-4">
        <input 
            type="text"
            wire:model.live="search"
            placeholder="Cari program..."
            class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
    </div>

    <!-- Form Modal -->
    @if ($showForm)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-slate-800 rounded-lg p-8 w-full max-w-md shadow-2xl border border-slate-700">
            <h2 class="text-2xl font-bold mb-6">{{ $editingId ? 'Edit Program' : 'Tambah Program Baru' }}</h2>

            <form wire:submit="save" class="space-y-4">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium mb-2">Nama Program</label>
                    <input 
                        type="text"
                        wire:model="name"
                        class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    @error('name') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Code -->
                <div>
                    <label class="block text-sm font-medium mb-2">Kode Program</label>
                    <input 
                        type="text"
                        wire:model="code"
                        class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    @error('code') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium mb-2">Deskripsi</label>
                    <textarea 
                        wire:model="description"
                        rows="3"
                        class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    ></textarea>
                    @error('description') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium mb-2">Kategori</label>
                    <select 
                        wire:model="category"
                        class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                    @error('category') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Duration -->
                <div>
                    <label class="block text-sm font-medium mb-2">Durasi (bulan)</label>
                    <input 
                        type="number"
                        wire:model="duration"
                        class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    @error('duration') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium mb-2">Status</label>
                    <select 
                        wire:model="status"
                        class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="active">Aktif</option>
                        <option value="inactive">Tidak Aktif</option>
                        <option value="completed">Selesai</option>
                    </select>
                    @error('status') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 pt-4">
                    <button 
                        type="button"
                        wire:click="resetForm"
                        class="flex-1 px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg transition"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg transition"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Programs Table -->
    <div class="px-8 py-6">
        <div class="overflow-x-auto bg-slate-800 border border-slate-700 rounded-lg">
            <table class="w-full">
                <thead class="bg-slate-700 border-b border-slate-600">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold">Nama Program</th>
                        <th class="px-6 py-3 text-left font-semibold">Kode</th>
                        <th class="px-6 py-3 text-left font-semibold">Kategori</th>
                        <th class="px-6 py-3 text-left font-semibold">Durasi</th>
                        <th class="px-6 py-3 text-left font-semibold">Status</th>
                        <th class="px-6 py-3 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($programs as $program)
                    <tr class="hover:bg-slate-700 transition">
                        <td class="px-6 py-4">{{ $program->name }}</td>
                        <td class="px-6 py-4">
                            <span class="bg-slate-700 px-3 py-1 rounded-full text-sm">{{ $program->code }}</span>
                        </td>
                        <td class="px-6 py-4">{{ $program->category ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $program->duration }} bulan</td>
                        <td class="px-6 py-4">
                            @if($program->status === 'active')
                                <span class="bg-green-900 text-green-300 px-3 py-1 rounded-full text-sm">Aktif</span>
                            @elseif($program->status === 'inactive')
                                <span class="bg-red-900 text-red-300 px-3 py-1 rounded-full text-sm">Tidak Aktif</span>
                            @else
                                <span class="bg-blue-900 text-blue-300 px-3 py-1 rounded-full text-sm">Selesai</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex gap-2 justify-center">
                                <button 
                                    wire:click="edit({{ $program->id }})"
                                    class="px-3 py-1 bg-blue-600 hover:bg-blue-700 rounded transition text-sm"
                                >
                                    Edit
                                </button>
                                <button 
                                    wire:click="delete({{ $program->id }})"
                                    onclick="confirm('Yakin hapus program ini?') || event.stopImmediatePropagation()"
                                    class="px-3 py-1 bg-red-600 hover:bg-red-700 rounded transition text-sm"
                                >
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                            Tidak ada program ditemukan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $programs->links() }}
        </div>
    </div>
</div>
