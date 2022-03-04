<?php

namespace Tests\Feature\LivewireTest;

use App\CreateData;
use App\Http\Livewire\AddCartItem;
use App\Http\Livewire\AddCartItemColor;
use App\Http\Livewire\AddCartItemSize;
use App\Http\Livewire\ShoppingCart;
use App\Http\Livewire\UpdateCartItem;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;
    use CreateData;

    /** @test */
    public function normal_products_are_added_to_the_cart()
    {
        $data = $this->createData(false, false,1);

        Livewire::test(AddCartItem::class, ['product' => $data["product0"]])
            ->call('addItem', $data["product0"])
            ->assertStatus(200);

        $this->assertEquals(Cart::content()->first()->name, $data["product0"]->name);

    }

    /** @test */
    public function color_products_are_added_to_the_cart()
    {
        $data = $this->createData(false, false,1);

        Livewire::test(AddCartItemColor::class, ['product' => $data["product0"]])
            ->call('addItem', $data["product0"])
            ->assertStatus(200);

        $this->assertEquals(Cart::content()->first()->name, $data["product0"]->name);
    }

    /** @test */
    public function size_products_are_added_to_the_cart()
    {
        $data = $this->createData(true, false, 1);

        Livewire::test(AddCartItemSize::class, ['product' => $data["product0"]])
            ->call('addItem', $data["product0"])
            ->assertStatus(200);

        $this->assertEquals(Cart::content()->first()->name, $data["product0"]->name);
    }

    /** @test */
    public function can_not_add_more_products_than_stock()
    {
        $data = $this->createData(false, false,1);
        $quantity = $data["product0"]->quantity;

        for ($i = 0; $i < $quantity; $i++){
            Livewire::test(AddCartItem::class, ['product' => $data["product0"]])
                ->call('addItem', $data["product0"]);
            $data["product0"]->quantity = qty_available($data["product0"]->id);
        }

        $this->assertEquals(Cart::content()->first()->qty, $quantity);
    }

    /** @test */
    public function editing_shopping_cart()
    {
        $data = $this->createData(false, false,1);

        Livewire::test(AddCartItem::class, ['product' => $data["product0"]])
            ->call('addItem', $data["product0"]);

        Livewire::test(ShoppingCart::class)
            ->assertViewIs('livewire.shopping-cart')
            ->assertSee($data["product0"]->name);

        Livewire::test(UpdateCartItem::class, ['rowId' => Cart::content()->first()->rowId, 'qty' => Cart::content()->first()->qty])
            ->assertViewIs('livewire.update-cart-item')
            ->call('increment')
            ->assertSet('qty',2);

        $this->assertEquals(Cart::subtotal(), $data["product0"]->price*2);

        Livewire::test(UpdateCartItem::class, ['rowId' => Cart::content()->first()->rowId, 'qty' => Cart::content()->first()->qty])
            ->assertViewIs('livewire.update-cart-item')
            ->call('decrement')
            ->assertSet('qty',1);

        $this->assertEquals(Cart::subtotal(), $data["product0"]->price);

    }

    /** @test */
    public function delete_the_shopping_cart()
    {
        $data = $this->createData(false, false,1);

        Livewire::test(AddCartItem::class, ['product' => $data["product0"]])
            ->call('addItem', $data["product0"]);

        Livewire::test(ShoppingCart::class)
            ->assertViewIs('livewire.shopping-cart')
            ->assertSee($data["product0"]->name)
            ->call('destroy')
            ->assertSeeHtml('<p class="text-lg text-gray-700 mt-4">TU CARRITO DE COMPRAS ESTÁ VACÍO</p>');
    }

    /** @test */
    public function delete_product_in_the_shopping_cart()
    {
        $data = $this->createData(false, false,1);

        Livewire::test(AddCartItem::class, ['product' => $data["product0"]])
            ->call('addItem', $data["product0"]);

        Livewire::test(ShoppingCart::class)
            ->assertViewIs('livewire.shopping-cart')
            ->assertSee($data["product0"]->name)
            ->call('delete', Cart::content()->first()->rowId)
            ->assertDontSee($data["product0"]->name);

    }

    /** @test */
    public function shopping_cart_saved_in_bd_when_session_close()
    {
        $data = $this->createData(false, false,1, 1);

        $this->actingAs($data["user0"]);

        Livewire::test(AddCartItem::class, ['product' => $data["product0"]])
            ->call('addItem', $data["product0"]);

        Livewire::test(ShoppingCart::class)
            ->assertViewIs('livewire.shopping-cart')
            ->assertSee($data["product0"]->name);

        $this->post('/logout');

        $this->assertDatabaseHas('shoppingcart', [
            'identifier' => $data["user0"]->id
        ]);
    }

    /** @test */
    public function shopping_cart_restored_when_login_back_in()
    {
        $data = $this->createData(false, false,2, 1);

        $this->actingAs($data["user0"]);

        Livewire::test(AddCartItem::class, ['product' => $data["product0"]])
            ->call('addItem', $data["product0"]);

        Livewire::test(AddCartItem::class, ['product' => $data["product1"]])
            ->call('addItem', $data["product1"]);

        Livewire::test(ShoppingCart::class)
            ->assertViewIs('livewire.shopping-cart')
            ->assertSee($data["product0"]->name)
            ->assertSee($data["product1"]->name);

        $this->post('/logout');

        $this->actingAs($data["user0"]);

        $this->get('/shopping-cart')
            ->assertSee($data["product0"]->name)
            ->assertSee($data["product1"]->name);
    }

    /** @test */
    public function normal_products_stock_change_when_added_to_the_cart()
    {
        $data = $this->createData(false, false,1);

        Livewire::test(AddCartItem::class, ['product' => $data["product0"]])
            ->call('addItem', $data["product0"])
            ->assertStatus(200);

        $this->assertEquals(qty_available($data["product0"]->id), 14);

    }

    /** @test */
    public function color_products_stock_change_when_added_to_the_cart()
    {
        $data = $this->createData(true, false,1);
        $color = $this->createColor();

        $data["product0"]->colors()->attach($color->id, ['quantity' => 10]);

        Livewire::test(AddCartItemColor::class, ['product' => $data["product0"]])
            ->set('options', ['color_id' => $color->id])
            ->call('addItem', $data["product0"])
            ->assertStatus(200);

        $this->assertEquals(qty_available($data["product0"]->id, $color->id), 9);
    }

    /** @test */
    public function size_products_stock_change_when_added_to_the_cart()
    {
        $data = $this->createData(true, true,1);

        $color = $this->createColor();

        $size = $this->createSize($data["product0"]);

        $size->colors()->attach($color->id, ['quantity' => 10]);


        Livewire::test(AddCartItemSize::class, ['product' => $data["product0"]])
            ->set('options', ['size_id' => $size->id, 'color_id' => $color->id])
            ->call('addItem', $data["product0"])
            ->assertStatus(200);

        $this->assertEquals(qty_available($data["product0"]->id, $color->id, $size->id), 9);
    }
}
