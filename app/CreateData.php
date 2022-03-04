<?php

namespace App;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Image;
use App\Models\Product;
use App\Models\Size;
use App\Models\Subcategory;
use App\Models\User;

trait CreateData
{
    public $result = [];

    public function createProduct($color = false, $size = false, $status = 2)
    {
        $brand = Brand::factory()->create();

        $category = Category::factory()->create();
        $category->brands()->attach($brand->id);

        $subcategory = Subcategory::factory()->create([
            'category_id' => $category->id,
            'color' => $color,
            'size' => $size,
        ]);

        $product = Product::factory()->create([
            'subcategory_id' => $subcategory->id,
            'price' => 10.0,
            'status' => $status
        ]);

        Image::factory()->create([
            'imageable_id' => $product->id,
            'imageable_type' => Product::class
        ]);

        array_push($this->result, $brand, $category, $subcategory, $product);
        return $this->result;
    }

    public function createColor()
    {
        $color = Color::create(['name' => 'prueba']);
        return $color;
    }

    public function createSize($product)
    {
        $size = Size::factory(['name' => 'prueba', 'product_id' => $product->id])->create();
        return $size;
    }

    public function createUser()
    {
       $user = User::factory()->create();
        array_push($this->result, $user);
        return $this->result;
    }

    public function createData($color, $size, $user = false ,$count)
    {
        for ($i=0; $i<$count; $i++){
            $this->createProduct($color, $size);
        }

        if ($user = true){
            $this->createUser();
        }

        return $this->result; /*Posicion 0: brand
                           Posicion 1: category
                            Posicion 2: subcategory
                          Posicion 3: product
                           Posicion $: user*/
    }
}

