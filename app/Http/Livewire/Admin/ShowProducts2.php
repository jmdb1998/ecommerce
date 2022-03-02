<?php

namespace App\Http\Livewire\Admin;

use App\Models\Product;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ShowProducts2 extends Component
{

use WithPagination;

    public $search, $categorySearch, $priceSearch,$subcategorySearch, $brandSearch, $status, $colorsFilter, $sizeFilter;
    public $category, $subcategory;
    public $pagination = 15;
    public $columns = ['Nombre','Categoría','Estado','Precio','Subcategoria','Marca','Stock','Colores','Tallas','Fecha Creación','Fecha Edición'];
    public $selectedColumns = [];
    public $show = false;
    public $order = 'name';
    public $sortField = 'id';
    public $sortDirection = 'asc';

    public function sortBy($field)
    {
        if ($this->sortField === $field){
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        }else{
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }


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
        $products = Product::query()->where('name', 'LIKE', "%{$this->search}%")->paginate($this->pagination);

        if ($this->categorySearch) {
            $products = $products->whereHas('subcategory', function (Builder $query) {
                $query->whereHas('category', function (Builder $query) {
                    $query->where('name', 'LIKE', "%{$this->categorySearch}%");
                });
            });
        }

        if ($this->subcategorySearch) {
            $products = $products->whereHas('subcategory', function (Builder $query) {
                $query->where('name', 'LIKE', "%{$this->subcategorySearch}%");
            });
        }

        if ($this->brandSearch) {
            $products = $products->whereHas('brand', function (Builder $query) {
                $query->where('name', 'LIKE', "%{$this->brandSearch}%");
            });
        }

        if ($this->status) {
            $products = $products->where('status', $this->status);
        }

        if ($this->priceSearch) {
            $products = $products->where('price', 'LIKE', "%{$this->priceSearch}%");
        }

        if ($this->colorsFilter) {
            $products = $products->whereHas('colors');
        }

        if ($this->sizeFilter) {
            $products = $products->whereHas('sizes');
        }


        return view('livewire.admin.show-products2', compact('products'))
            ->layout('layouts.admin');
    }
}
