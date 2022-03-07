<?php

namespace Tests;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Image;
use App\Models\Product;
use App\Models\Size;
use App\Models\Subcategory;

trait CreateProduct
{

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

    public function createProduct($color = false, $size=false, $status = 2)
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

        return $product;
    }

}
