<?php

namespace Tests;

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

    public function createCategory()
    {
        Category::factory()->create();
    }

    public function createSubcategory($category, $color = false, $size = false)
    {
        return Subcategory::factory()->create([
            'category_id' => $category->id,
            'color' => $color,
            'size' => $size,
        ]);
    }

    public function createBrand($category)
    {
        $brand = Brand::factory()->create();
        $category->brands()->attach($brand->id);
        return $brand;
    }

    public function createProduct($color, $size, $status, $i)
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
            'status' => $status,
        ]);

        Image::factory()->create([
            'imageable_id' => $product->id,
            'imageable_type' => Product::class
        ]);

        $this->result += ["brand".$i => $brand,
                "category".$i => $category,
                "subcategory".$i => $subcategory,
                "product".$i => $product];
    }

    public function createUser()
    {
        $user = User::factory()->create();
        return $user;
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

    public function createData($color = false, $size = false, $status = 2, $productCount = 1)
    {
        for ($i=0; $i < $productCount; $i++){
            $this->createProduct($color, $size, $status,$i);
        }
        return $this->result;
    }
}
