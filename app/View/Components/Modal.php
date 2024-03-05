<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Modal extends Component
{
    public $modalId;
    public $modalClass;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($modalId = 'defaultModal', $modalClass = 'modal-dialog-centered')
    {
        $this->modalId = $modalId;
        $this->modalClass = $modalClass;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.modal');
    }
}
