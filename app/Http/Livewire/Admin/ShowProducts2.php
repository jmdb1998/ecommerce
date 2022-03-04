<?php

namespace App\Http\Livewire\Admin;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class ShowProducts2 extends Component
{

use WithPagination;

    public $search, $categorySearch, $priceSearch,$subcategorySearch, $brandSearch, $colorsFilter, $sizeFilter, $quantity, $reset;
    public $status = 2;
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

    public function resetFilter()
    {
        $this->reset(['search', 'categorySearch', 'priceSearch', 'subcategorySearch', 'brandSearch', 'colorsFilter', 'sizeFilter', 'quantity', 'reset']);
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::query()->search($this->search)
            ->categoryFilter($this->categorySearch)
            ->subcategoryFilter($this->subcategorySearch)
            ->brandFilter($this->brandSearch)
            ->statusFilter($this->status);


        if ($this->colorsFilter) {
            $products = Product::colorsFilter($this->colorsFilter);
        }

        if ($this->sizeFilter) {
            $products = Product::sizeFilter($this->sizeFilter);
        }

        if ($this->priceSearch) {
            $products = $products->where('price', 'LIKE', "%{$this->priceSearch}%");
        }

        $products = $products->orderBy($this->sortField,$this->sortDirection)->paginate($this->pagination);
        $categories = Category::get();

        return view('livewire.admin.show-products2', compact('products', 'categories'))
            ->layout('layouts.admin');
    }
}
