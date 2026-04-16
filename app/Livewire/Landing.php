<?php

namespace App\Livewire;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Projeto Lâmpada | Toda a Escritura para Toda a Vida')]
class Landing extends Component
{
    public function render()
    {
        return view('livewire.landing');
    }
}
