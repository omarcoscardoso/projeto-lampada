<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Devotional;
use Illuminate\Support\Facades\Date;

class Home extends Component
{
    public $devotional;

    public function mount()
    {
        $now = Date::now();
        $this->devotional = Devotional::query()
                            ->where('month', $now->month)
                            ->where('day', $now->day)
                            ->first(); 
    }
    public function render()
    {
        return view('livewire.home');
    }
}
