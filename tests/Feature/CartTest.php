<?php

namespace Tests\Feature;

use App\Http\Livewire\AddCartItem;
use App\Http\Livewire\AddCartItemColor;
use App\Http\Livewire\AddCartItemSize;
use App\Http\Livewire\DropdownCart;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function normal_products_are_added_to_the_cart()
    {
        $normalProduct = $this->createProduct(false, false);

        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct)
            ->assertStatus(200);

        $this->assertEquals(Cart::content()->first()->name, $normalProduct->name);

    }

    /** @test */
    public function color_products_are_added_to_the_cart()
    {
        $colorProduct = $this->createProduct(true, false);

        Livewire::test(AddCartItemColor::class, ['product' => $colorProduct])
            ->call('addItem', $colorProduct)
            ->assertStatus(200);

        $this->assertEquals(Cart::content()->first()->name, $colorProduct->name);
    }

    /** @test */
    public function size_products_are_added_to_the_cart()
    {
        $sizeProduct = $this->createProduct(true, true);

        Livewire::test(AddCartItemSize::class, ['product' => $sizeProduct])
            ->call('addItem', $sizeProduct)
            ->assertStatus(200);

        $this->assertEquals(Cart::content()->first()->name, $sizeProduct->name);
    }

    /** @test */
    public function products_are_in_the_drop_down_cart()
    {
        $normalProduct = $this->createProduct(true, true);

        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct);

        Livewire::test(DropdownCart::class)
            ->assertSee($normalProduct->name);
    }



    public function createProduct($color = false, $size = false)
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
        ]);

        Image::factory()->create([
            'imageable_id' => $product->id,
            'imageable_type' => Product::class
        ]);

        return $product;
    }
}
