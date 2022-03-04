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

    public function createProduct($color = false, $size = false, $status = 2, $i)
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


        return $this->result = ["brand".$i => $brand,
                                "category".$i => $category,
                                "subcategory".$i => $subcategory,
                                "product".$i => $product];
    }

    public function createColor($i)
    {
        $color = Color::create(['name' => 'prueba']);
        return $this->result = ["color".$i => $color];
    }

    public function createSize($product, $i)
    {
        $size = Size::factory(['name' => 'prueba', 'product_id' => $product->id])->create();
        return $this->result = ["size".$i => $size];
    }

    public function createUser($i)
    {
       $user = User::factory()->create();
        return $this->result = ["user".$i => $user];
    }

    public function createData($color, $size, $productCount = null, $userCount = null, $colorCount = null, $sizeCount = null)
    {
        if (isset($productCount)){
            for ($i=0; $i<$productCount; $i++){
                $this->createProduct($color, $size,2,$i);
            }
        }

        if (isset($userCount)){
            for ($i=0; $i<$userCount; $i++){
                $this->createUser($i);
            }
        }

        if (isset($colorCount)){
            for ($i=0; $i<$colorCount; $i++){
                $this->createColor($i);
            }
        }

        return $this->result;
    }
}

