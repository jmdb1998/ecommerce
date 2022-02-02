<?php

namespace App\Http\Livewire\Admin;

use App\Models\Brand;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateCategory extends Component
{
    public $brands;
    public $createForm = [
        'name' => null,
        'slug' => null,
        'icon' => null,
        'image' => null,
        'brands' => [],
    ];
    protected $rules = [
        'createForm.name' => 'required',
        'createForm.slug' => 'required|unique:categories,slug',
        'createForm.icon' => 'required',
        'createForm.image' => 'required|image|max:1024',
        'createForm.brands' => 'required',
    ];

    protected $validationAttributes = [
        'createForm.name' => 'nombre',
        'createForm.slug' => 'slug',
        'createForm.icon' => 'icono',
        'createForm.image' => 'imagen',
        'createForm.brands' => 'marcas',
    ];

    public function updatedCreateFormName($value)
    {
        $this->createForm['slug'] = Str::slug($value);
    }

    public function mount()
    {
        $this->getBrands();
    }

    public function getBrands()
    {
        $this->brands = Brand::all();
    }

    public function save()
    {
        $this->validate();
    }

    public function render()
    {
        return view('livewire.admin.create-category');
    }
}
