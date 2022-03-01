<?php

namespace App\Http\Livewire\Admin;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ShowProducts2 extends Component
{

use WithPagination;

    public $search;
    public $pagination = 15;
    public $columns = ['Nombre','Categoría','Estado','Precio','Subcategoria','Marca','Stock','Colores','Tallas','Fecha Creación','Fecha Edición'];
    public $selectedColumns = [];
    public $show = false;
    public $order = 'name';
    public $show2 = 'asc';


    public function showColumn($column)
    {
        return in_array($column, $this->selectedColumns);
    }

    public function mount()
    {
        $this->selectedColumns = $this->columns;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPagination()
    {
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::where('name', 'LIKE', "%{$this->search}%")->orderBy($this->order,$this->show2)->paginate($this->pagination);


        return view('livewire.admin.show-products2', compact('products'))
            ->layout('layouts.admin');
    }
}
