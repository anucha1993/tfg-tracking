<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Contact extends Component
{
    public function __construct(
        public array $company,
    ) {}

    public function render(): View
    {
        return view('components.contact');
    }
}
