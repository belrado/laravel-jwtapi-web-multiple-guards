<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Ramsey\Collection\Collection;

class Tab extends Component
{
    public $tabType;

    public $tabId;

    /**
     * Create a new component instance.
     *
     * @return void
     */

    public function __construct($tabType, $tabId)
    {
        $this->tabType = $tabType;
        $this->tabId = $tabId;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.tab');
    }
}
