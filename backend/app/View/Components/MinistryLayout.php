<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class MinistryLayout extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $headerTitle = null
    ) {}

    public function render(): View
    {
        return view('layouts.ministry');
    }
}
