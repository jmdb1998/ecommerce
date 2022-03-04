<?php

namespace Tests\Feature\LivewireTest;

use App\CreateData;
use App\CreateProduct;
use App\Http\Livewire\AddCartItem;
use App\Http\Livewire\AddCartItemColor;
use App\Http\Livewire\AddCartItemSize;
use App\Http\Livewire\DropdownCart;
use App\Http\Livewire\Search;
use App\Http\Livewire\ShoppingCart;
use App\Http\Livewire\UpdateCartItem;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Image;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductsTest extends TestCase
{
    use RefreshDatabase;
    use CreateData;

    /** @test */
    public function normal_products_stock_is_seen()
    {
        $data = $this->createData(false, false);

        Livewire::test(AddCartItem::class, ['product' => $data[3]])
            ->assertViewIs('livewire.add-cart-item')
            ->assertSee('quantity');

    }

    /** @test */
    public function color_products_stock_is_seen()
    {
        $data = $this->createData(true, false);

        Livewire::test(AddCartItemColor::class, ['product' => $data[3]])
            ->assertViewIs('livewire.add-cart-item-color')
            ->assertSee('quantity');
    }

    /** @test */
    public function size_products_stock_is_seen()
    {
        $data = $this->createData(true, true);

        Livewire::test(AddCartItemSize::class, ['product' => $data[3]])
            ->assertViewIs('livewire.add-cart-item-size')
            ->assertSee('quantity');
    }
}
