<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Layout extends Component
{
    public function __construct(
        public string $title,
        public array $company,
    ) {}

    public function render(): View
    {
        return view('components.layout');
    }
}
