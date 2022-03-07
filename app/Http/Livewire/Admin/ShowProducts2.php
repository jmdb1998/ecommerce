<?php

namespace App\Http\Livewire\Admin;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use App\Filter\ProductFilter;

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

    protected function getProduct(ProductFilter $productFilter){
        $products = Product::query()
            ->filterBy($productFilter, [
                'search' => $this->search,
                'category' => $this->categorySearch,
                'price' => $this->priceSearch,
                'subcategory' => $this->subcategorySearch,
                'brand' => $this->brandSearch,
                'colors' => $this->colorsFilter,
                'sizes' => $this->sizeFilter
            ])->orderBy($this->sortField,$this->sortDirection)->paginate($this->pagination);

        return $products;

    }

    public function render(ProductFilter $productFilter)
    {

        return view('livewire.admin.show-products2', [
            'products' => $this->getProduct($productFilter)
        ])->layout('layouts.admin');
    }
}
