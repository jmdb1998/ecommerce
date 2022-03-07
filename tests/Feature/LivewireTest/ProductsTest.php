<?php

namespace Tests\Feature\LivewireTest;

use tests\CreateData;
use App\Http\Livewire\AddCartItem;
use App\Http\Livewire\AddCartItemColor;
use App\Http\Livewire\AddCartItemSize;
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

        Livewire::test(AddCartItem::class, ['product' => $data["product0"]])
            ->assertViewIs('livewire.add-cart-item')
            ->assertSee('quantity');

    }

    /** @test */
    public function color_products_stock_is_seen()
    {
        $data = $this->createData(true, false);

        Livewire::test(AddCartItemColor::class, ['product' => $data["product0"]])
            ->assertViewIs('livewire.add-cart-item-color')
            ->assertSee('quantity');
    }

    /** @test */
    public function size_products_stock_is_seen()
    {
        $data = $this->createData(true, true);

        Livewire::test(AddCartItemSize::class, ['product' => $data["product0"]])
            ->assertViewIs('livewire.add-cart-item-size')
            ->assertSee('quantity');
    }
}
