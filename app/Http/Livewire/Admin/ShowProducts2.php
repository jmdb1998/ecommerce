<?php

namespace App\Http\Livewire\Admin;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
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

    public function filterSize()
    {
        $pTalla = Product::select('products.name as nombre_producto', 'subcategories.category_id as id_categoria', 'products.status as status', 'products.price as precio', 'products.subcategory_id as subcategoria', 'brands.name as nombre_marca', DB::raw("SUM('color_size.quantity') as cantidad_color"), 'subcategories.color as color', 'subcategories.size as talla', 'products.created_at', 'products.updated_at')
            ->join('subcategories','products.subcategory_id','=','subcategories.id')
            ->join('sizes','sizes.product_id','=','products.id')
            ->join('color_size','color_size.size_id','=','sizes.id')
            ->join('brands','brands.id','=','products.brand_id')
            ->where(function ($query) {
                $query->where([['subcategories.color','=',1], ['subcategories.size','=',1]]);
            })
            ->groupByRaw('products.name, subcategories.category_id, products.status, products.price, products.subcategory_id, brands.name, subcategories.color, subcategories.size, products.created_at, products.updated_at');

        return $pTalla;
    }

    public function render()
    {
        /*$pTalla = Product::select('products.name as nombre_producto', 'subcategories.category_id as id_categoria', 'products.status as status', 'products.price as precio', 'products.subcategory_id as subcategoria', 'brands.name as nombre_marca', DB::raw("SUM('color_size.quantity') as cantidad_color"), 'subcategories.color as color', 'subcategories.size as talla', 'products.created_at', 'products.updated_at')
            ->join('subcategories','products.subcategory_id','=','subcategories.id')
            ->join('sizes','sizes.product_id','=','products.id')
            ->join('color_size','color_size.size_id','=','sizes.id')
            ->join('brands','brands.id','=','products.brand_id')
            ->where(function ($query) {
                $query->where([['subcategories.color','=',1], ['subcategories.size','=',1]]);
            })
            ->groupByRaw('products.name, subcategories.category_id, products.status, products.price, products.subcategory_id, brands.name, subcategories.color, subcategories.size, products.created_at, products.updated_at');

        $pColor = Product::select('products.name as nombre_producto', 'subcategories.category_id as id_categoria', 'products.status as status', 'products.price as precio', 'products.subcategory_id as subcategoria', 'brands.name as nombre_marca', DB::raw("SUM('color_product.quantity') as stock"), 'subcategories.color as color', 'subcategories.size as talla', 'products.created_at', 'products.updated_at')
            ->join('subcategories','products.subcategory_id','=','subcategories.id')
            ->join('color_product','color_product.product_id','=','products.id')
            ->join('brands','brands.id','=','products.brand_id')
            ->where(function ($query) {
                $query->where([['subcategories.color','=',1], ['subcategories.size','=',0]]);
            })
            ->groupByRaw('products.name, subcategories.category_id, products.status, products.price, products.subcategory_id, brands.name, subcategories.color, subcategories.size, products.created_at, products.updated_at');

        $pColor->union($pTalla);

        $pNormales = Product::select('products.name as nombre_producto', 'subcategories.category_id as id_categoria', 'products.status as status', 'products.price as precio', 'products.subcategory_id as subcategoria', 'brands.name as nombre_marca', 'products.quantity as stock', 'subcategories.color as color', 'subcategories.size as talla', 'products.created_at', 'products.updated_at')
            ->join('subcategories','products.subcategory_id','=','subcategories.id')
            ->join('brands','brands.id','=','products.brand_id')
            ->where(function ($query) {
                $query->where([['subcategories.color','=',0], ['subcategories.size','=',0]]);
            });

        $result = DB::table('*')
            ->select('*')->from($pNormales->union($pColor))->get();*/

        $products = Product::where('name', 'LIKE', "%{$this->search}%")
            ->orWhere('price', 'LIKE', "%{$this->search}%")
            ->orWhere('status', 'LIKE', "%{$this->search}%")
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->pagination);


        return view('livewire.admin.show-products2', compact('products'))
            ->layout('layouts.admin');
    }
}
