<?php

namespace Tests\Feature\LivewireTest;

use App\Http\Livewire\AddCartItem;
use App\Http\Livewire\AddCartItemColor;
use App\Http\Livewire\AddCartItemSize;
use App\Http\Livewire\CreateOrder;
use App\Http\Livewire\DropdownCart;
use App\Http\Livewire\Search;
use App\Http\Livewire\ShoppingCart;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\ColorProduct;
use App\Models\Image;
use App\Models\Product;
use App\Models\Size;
use App\Models\Subcategory;
use App\Models\User;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class ViewTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function search_a_product()
    {
        $normalProduct = $this->createProduct();
        $normalProduct2 = $this->createProduct();

        Livewire::test(Search::class, ['search' => $normalProduct->name])
            ->assertViewIs('livewire.search')
            ->assertSee('products', $normalProduct->name)
            ->assertDontSee($normalProduct2->name);
    }

    /** @test */
    public function search_nothing_and_show_all_products()
    {
        $normalProduct = $this->createProduct();
        $normalProduct2 = $this->createProduct();

        Livewire::test(Search::class, ['search' => ' '])
            ->assertViewIs('livewire.search')
            ->assertSee('products', $normalProduct->name)
            ->assertSee($normalProduct2->name);
    }

    /** @test */
    public function see_shopping_cart()
    {
        $normalProduct = $this->createProduct();
        $normalProduct2 = $this->createProduct();

        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct);

        Livewire::test(ShoppingCart::class)
            ->assertViewIs('livewire.shopping-cart')
            ->assertSee($normalProduct->name)
            ->assertDontSee($normalProduct2->name);
    }

    /** @test */
    public function products_are_seen_in_the_drop_down_cart()
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
        $normalProduct = $this->createProduct();

        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct);

        $this->assertEquals(Cart::count(), 1);
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
            'price' => 10.0,
        ]);

        Image::factory()->create([
            'imageable_id' => $product->id,
            'imageable_type' => Product::class
        ]);

        return $product;
    }
}
