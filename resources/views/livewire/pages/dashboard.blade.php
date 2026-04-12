<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    // Component for dashboard - no methods needed
}; ?>

<div class="min-h-screen bg-slate-900 text-white">
    <!-- Top Navbar -->
    <nav class="bg-slate-800 shadow-lg sticky top-0 z-40 border-b border-slate-700">
        <div class="px-6 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-blue-400">Dashboard</h1>
            </div>
            <div class="flex items-center space-x-6">
                <div class="text-right">
                    <p class="text-sm text-slate-400">Selamat datang</p>
                    <p class="text-lg font-semibold text-white">{{ Auth::user()->name }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-slate-800 text-white min-h-screen border-r border-slate-700">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-12 h-12 rounded-lg bg-blue-600 flex items-center justify-center text-lg font-bold">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="font-semibold">{{ Auth::user()->name }}</p>
                        <p class="text-slate-400 text-sm">{{ Auth::user()->email }}</p>
                    </div>
                </div>

                <!-- Menu Items -->
                <div class="space-y-2 mb-8">
                    <a href="#" class="block px-4 py-3 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition">
                        <span>🏠 Dashboard</span>
                    </a>
                    <a href="{{ route('program') }}" class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                        <span>📋 Program</span>
                    </a>
                    <a href="#" class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                        <span>👥 Users</span>
                    </a>
                    <a href="#" class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                        <span>⚙️ Settings</span>
                    </a>
                </div>

                <!-- Divider -->
                <div class="border-t border-slate-700 pt-4">
                    <a 
                        href="{{ route('logout') }}"
                        class="w-full px-4 py-3 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium transition flex items-center justify-center space-x-2"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    >
                        <span>🚪</span>
                        <span>Logout</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Welcome Card -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg p-8 text-white mb-8">
                <h2 class="text-4xl font-bold mb-2">Selamat Datang, {{ Auth::user()->name }}! 👋</h2>
                <p class="text-blue-100">Anda telah berhasil login ke aplikasi. Nikmati pengalaman terbaik bersama kami.</p>
            </div>

            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- User Info Card -->
                <div class="bg-slate-800 rounded-lg shadow-md p-6 hover:shadow-lg transition border border-slate-700">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-blue-200 text-xl">
                            👤
                        </div>
                        <h3 class="text-lg font-semibold text-white">Informasi Profil</h3>
                    </div>
                    <div class="space-y-3 text-slate-300">
                        <div>
                            <p class="text-sm text-slate-400">Nama</p>
                            <p class="font-medium text-white">{{ Auth::user()->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-400">Email</p>
                            <p class="font-medium truncate text-white">{{ Auth::user()->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-400">Role</p>
                            <div class="flex space-x-2">
                                @if(Auth::user()->roles->count())
                                    @foreach(Auth::user()->roles as $role)
                                        <span class="px-3 py-1 bg-blue-600 text-blue-100 text-sm rounded-full font-medium">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="px-3 py-1 bg-slate-700 text-slate-300 text-sm rounded-full">User</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Card -->
                <div class="bg-slate-800 rounded-lg shadow-md p-6 hover:shadow-lg transition border border-slate-700">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-purple-600 flex items-center justify-center text-purple-200 text-xl">
                            📊
                        </div>
                        <h3 class="text-lg font-semibold text-white">Statistik</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-300">Total Users</span>
                            <span class="text-2xl font-bold text-purple-400">{{ \App\Models\User::count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-300">Your Role</span>
                            <span class="text-lg font-semibold text-purple-400">{{ Auth::user()->roles->first()?->name ?? 'User' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Action Card -->
                <div class="bg-slate-800 rounded-lg shadow-md p-6 hover:shadow-lg transition border border-slate-700">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-amber-600 flex items-center justify-center text-amber-200 text-xl">
                            ⚡
                        </div>
                        <h3 class="text-lg font-semibold text-white">Aksi Cepat</h3>
                    </div>
                    <div class="space-y-2">
                        <button class="w-full px-4 py-2 bg-blue-600 text-blue-100 rounded-lg hover:bg-blue-700 transition font-medium text-sm">
                            Edit Profil
                        </button>
                        <button class="w-full px-4 py-2 bg-slate-700 text-slate-200 rounded-lg hover:bg-slate-600 transition font-medium text-sm">
                            Ganti Password
                        </button>
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="bg-slate-800 rounded-lg shadow-md p-6 border border-slate-700">
                <h3 class="text-xl font-semibold text-white mb-4">📝 Ringkasan Aktivitas</h3>
                <div class="space-y-3 text-slate-300">
                    <p>✅ Anda telah berhasil login ke sistem</p>
                    <p>✅ Akun Anda aktif dan terverifikasi</p>
                    <p>✅ Semua perizinan telah ditetapkan sesuai role Anda</p>
                </div>
            </div>
        </div>
    </div>
</div>
