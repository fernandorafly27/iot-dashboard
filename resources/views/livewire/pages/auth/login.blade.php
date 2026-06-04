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

<div style="display: flex; align-items: center; justify-content: center; min-height: 100vh; background: linear-gradient(to bottom right, #0f172a, #1e293b);">
    <div style="width: 100%; max-width: 28rem;">
        <div style="background: white; border-radius: 0.75rem; box-shadow: 0 20px 25px rgba(0,0,0,0.15); overflow: hidden;">
            <!-- Header -->
            <div style="background: linear-gradient(to right, #2563eb, #1d4ed8); padding: 2rem; text-align: center;">
                <h1 style="font-size: 2.25rem; font-weight: 700; color: white;">🔐 Login</h1>
                <p style="color: #dbeafe; font-size: 0.875rem; margin-top: 0.5rem;">Masuk ke akun Anda</p>
            </div>

            <!-- Form -->
            <div style="padding: 2rem;">
                <form wire:submit="authenticate" style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <!-- Email -->
                    <div>
                        <label for="email" style="display: block; font-size: 0.875rem; font-weight: 600; color: #0f172a; margin-bottom: 0.5rem;">Email</label>
                        <input 
                            type="email" 
                            id="email"
                            wire:model.lazy="email"
                            style="width: 100%; padding: 0.75rem 1rem; border: 2px solid #e2e8f0; color: #0f172a; background: white; border-radius: 0.5rem; font-size: 0.875rem; transition: border 0.2s;"
                            placeholder="Masukkan email Anda"
                            onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)'"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'"
                        />
                        @error('email')
                            <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" style="display: block; font-size: 0.875rem; font-weight: 600; color: #0f172a; margin-bottom: 0.5rem;">Password</label>
                        <input 
                            type="password" 
                            id="password"
                            wire:model.lazy="password"
                            style="width: 100%; padding: 0.75rem 1rem; border: 2px solid #e2e8f0; color: #0f172a; background: white; border-radius: 0.5rem; font-size: 0.875rem; transition: border 0.2s;"
                            placeholder="Masukkan password Anda"
                            onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)'"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'"
                        />
                        @error('password')
                            <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        style="width: 100%; background: #2563eb; color: white; font-weight: 700; padding: 0.75rem; border-radius: 0.5rem; border: none; cursor: pointer; margin-top: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: background 0.2s;"
                        onmouseover="this.style.background='#1d4ed8'; this.style.boxShadow='0 10px 15px rgba(0,0,0,0.2)'"
                        onmouseout="this.style.background='#2563eb'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)'"
                    >
                        Login
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>