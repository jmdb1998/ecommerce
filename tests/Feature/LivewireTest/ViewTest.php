<?php

namespace Tests\Feature\LivewireTest;

use App\Http\Livewire\AddCartItem;
use App\Http\Livewire\DropdownCart;
use App\Http\Livewire\Search;
use App\Http\Livewire\ShoppingCart;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\CreateData;
use Tests\TestCase;

class ViewTest extends TestCase
{
    use RefreshDatabase;
    use CreateData;

    /** @test */
    public function search_a_product()
    {
        $data = $this->createData(false, false, 2, 2);

        Livewire::test(Search::class, ['search' => $data["product0"]->name])
            ->assertViewIs('livewire.search')
            ->assertSee('products', $data["product0"]->name)
            ->assertDontSee($data["product1"]->name);
    }

    /** @test */
    public function search_nothing_and_show_all_products()
    {
        $data = $this->createData(false, false, 2,2);

        Livewire::test(Search::class, ['search' => ' '])
            ->assertViewIs('livewire.search')
            ->assertSee('products', $data["product0"]->name)
            ->assertSee($data["product1"]->name);
    }

    /** @test */
    public function see_shopping_cart()
    {
        $data = $this->createData(false, false, 2,2);

        Livewire::test(AddCartItem::class, ['product' => $data["product0"]])
            ->call('addItem', $data["product0"]);

        Livewire::test(ShoppingCart::class)
            ->assertViewIs('livewire.shopping-cart')
            ->assertSee($data["product0"]->name)
            ->assertDontSee($data["product1"]->name);
    }

    /** @test */
    public function products_are_seen_in_the_drop_down_cart()
    {
        $data = $this->createData(false, false, 2,2);

        Livewire::test(AddCartItem::class, ['product' => $data["product0"]])
            ->call('addItem', $data["product0"]);

        Livewire::test(DropdownCart::class)
            ->assertSee($data["product0"]->name)
            ->assertDontSee($data["product1"]->name);
    }

    /** @test */
    public function red_dot_increase_in_number()
    {
        $data = $this->createData(false, false,2,2);

        Livewire::test(AddCartItem::class, ['product' => $data["product0"]])
            ->call('addItem', $data["product0"]);

        $this->assertEquals(Cart::count(), 1);
    }
}
