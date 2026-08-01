<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Projeto Lâmpada | Toda a Escritura para Toda a Vida')]
class Landing extends Component
{
    public function mount()
    {
        if (Auth::check() && ! request()->has('sobre')) {
            return redirect()->route('app');
        }
    }

    public function render()
    {
        return view('livewire.landing');
    }
}
