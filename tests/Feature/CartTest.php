<?php

namespace Tests\Feature;

use App\Http\Livewire\AddCartItem;
use App\Http\Livewire\AddCartItemColor;
use App\Http\Livewire\AddCartItemSize;
use App\Http\Livewire\DropdownCart;
use App\Http\Livewire\Search;
use App\Http\Livewire\ShoppingCart;
use App\Http\Livewire\UpdateCartItem;
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
        $normalProduct2 = $this->createProduct(true, true);

        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct);

        Livewire::test(DropdownCart::class)
            ->assertSee($normalProduct->name)
            ->assertDontSee($normalProduct2->name);
    }

    /** @test */
    public function red_dot_increase_in_number()
    {
        $normalProduct = $this->createProduct(false, false);

        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct);

        $this->assertEquals(Cart::count(), 1);
    }

    /** @test */
    public function can_not_add_more_products_than_stock()
    {
        $normalProduct = $this->createProduct(false, false);

        for ($i = 0; $i <= $normalProduct->quantity; $i++){
            Livewire::test(AddCartItem::class, ['product' => $normalProduct])
                ->call('addItem', $normalProduct);
        }

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /** @test */
    public function search_testing()
    {
        $normalProduct = $this->createProduct(false, false);

        Livewire::test(Search::class, ['search' => $normalProduct->name])
            ->assertViewIs('livewire.search')
            ->assertViewHas('products', $normalProduct->name);

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /** @test */
    public function see_shopping_cart()
    {
        $normalProduct = $this->createProduct(false, false);
        $normalProduct2 = $this->createProduct(false, false);

        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct);

        Livewire::test(ShoppingCart::class)
            ->assertViewIs('livewire.shopping-cart')
            ->assertSee($normalProduct->name)
            ->assertDontSee($normalProduct2->name);
    }

    /** @test */
    public function editing_shopping_cart()
    {
        $normalProduct = $this->createProduct(false, false);

        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct);

        Livewire::test(ShoppingCart::class)
            ->assertViewIs('livewire.shopping-cart')
            ->assertSee($normalProduct->name);

        Livewire::test(UpdateCartItem::class, ['rowId' => Cart::content()->first()->rowId])
            ->assertViewIs('livewire.update-cart-item')
            ->call('increment');

        dd(Cart::content()->first()->qty);

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
