<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|min:6')]
    public string $password = '';

    public function authenticate()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->regenerate();
            return redirect('/dashboard');
        }

        $this->addError('email', 'Email atau password salah.');
    }
}; ?>

<div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-slate-900 to-slate-800">
    <div class="w-full max-w-sm">
        <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-8 text-center">
                <h1 class="text-4xl font-bold text-white">Login</h1>
                <p class="text-blue-100 text-sm mt-2">Masuk ke akun Anda</p>
            </div>

            <!-- Form -->
            <div class="p-8">
                <form wire:submit="authenticate" class="space-y-5">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                        <input 
                            type="email" 
                            id="email"
                            wire:model.lazy="email"
                            class="w-full px-4 py-3 bg-yellow-600 text-white placeholder-yellow-100 border-0 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 transition"
                            placeholder="Masukkan email Anda"
                        />
                        @error('email')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                        <input 
                            type="password" 
                            id="password"
                            wire:model.lazy="password"
                            class="w-full px-4 py-3 bg-yellow-600 text-white placeholder-yellow-100 border-0 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 transition"
                            placeholder="Masukkan password Anda"
                        />
                        @error('password')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-3 rounded-lg transition duration-200 cursor-pointer mt-6 shadow-md hover:shadow-lg"
                    >
                        Login
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>