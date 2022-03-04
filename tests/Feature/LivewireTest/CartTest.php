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

        Livewire::test(AddCartItem::class, ['product' => $data[3]])
            ->call('addItem', $data[3])
            ->assertStatus(200);

        $this->assertEquals(Cart::content()->first()->name, $data[3]->name);

    }

    /** @test */
    public function color_products_are_added_to_the_cart()
    {
        $data = $this->createData(true, false, 1);

        Livewire::test(AddCartItemColor::class, ['product' => $data[3]])
            ->call('addItem', $data[3])
            ->assertStatus(200);

        $this->assertEquals(Cart::content()->first()->name, $data[3]->name);
    }

    /** @test */
    public function size_products_are_added_to_the_cart()
    {
        $data = $this->createData(true, true,1);

        Livewire::test(AddCartItemSize::class, ['product' => $data[3]])
            ->call('addItem', $data[3])
            ->assertStatus(200);

        $this->assertEquals(Cart::content()->first()->name, $data[3]->name);
    }

    /** @test */
    public function can_not_add_more_products_than_stock()
    {
        $data = $this->createData(false, false,1);

        $quantity = $data[3]->quantity;

        for ($i = 0; $i < $quantity; $i++){
            Livewire::test(AddCartItem::class, ['product' => $data[3]])
                ->call('addItem', $data[3]);
            $data[3]->quantity = qty_available($data[3]->id);
        }

        $this->assertEquals(Cart::content()->first()->qty, $quantity);
    }

    /** @test */
    public function editing_shopping_cart()
    {
        $data = $this->createData(false, false,1);

        Livewire::test(AddCartItem::class, ['product' => $data[3]])
            ->call('addItem', $data[3]);

        Livewire::test(ShoppingCart::class)
            ->assertViewIs('livewire.shopping-cart')
            ->assertSee($data[3]->name);

        Livewire::test(UpdateCartItem::class, ['rowId' => Cart::content()->first()->rowId, 'qty' => Cart::content()->first()->qty])
            ->assertViewIs('livewire.update-cart-item')
            ->call('increment')
            ->assertSet('qty',2);

        $this->assertEquals(Cart::subtotal(), $data[3]->price*2);

        Livewire::test(UpdateCartItem::class, ['rowId' => Cart::content()->first()->rowId, 'qty' => Cart::content()->first()->qty])
            ->assertViewIs('livewire.update-cart-item')
            ->call('decrement')
            ->assertSet('qty',1);

        $this->assertEquals(Cart::subtotal(), $data[3]->price);

    }

    /** @test */
    public function delete_the_shopping_cart()
    {
        $data = $this->createData(false, false,1);

        Livewire::test(AddCartItem::class, ['product' => $data[3]])
            ->call('addItem', $data[3]);

        Livewire::test(ShoppingCart::class)
            ->assertViewIs('livewire.shopping-cart')
            ->assertSee($data[3]->name)
            ->call('destroy')
            ->assertSeeHtml('<p class="text-lg text-gray-700 mt-4">TU CARRITO DE COMPRAS ESTÁ VACÍO</p>');
    }

    /** @test */
    public function delete_product_in_the_shopping_cart()
    {
        $data = $this->createData(false, false,1);

        Livewire::test(AddCartItem::class, ['product' => $data[3]])
            ->call('addItem', $data[3]);

        Livewire::test(ShoppingCart::class)
            ->assertViewIs('livewire.shopping-cart')
            ->assertSee($data[3]->name)
            ->call('delete', Cart::content()->first()->rowId)
            ->assertDontSee($data[3]->name);

    }

    /** @test */
    public function shopping_cart_saved_in_bd_when_session_close()
    {
        $data = $this->createData(false, false,1);
        $user = $this->createUser();
        $this->actingAs($user);

        Livewire::test(AddCartItem::class, ['product' => $data[3]])
            ->call('addItem', $data[3]);

        Livewire::test(ShoppingCart::class)
            ->assertViewIs('livewire.shopping-cart')
            ->assertSee($data[3]->name);

        $this->post('/logout');

        $this->assertDatabaseHas('shoppingcart', [
            'identifier' => $user->id
        ]);
    }

    /** @test */
    public function normal_products_stock_change_when_added_to_the_cart()
    {
        $data = $this->createData(false, false,1);

        Livewire::test(AddCartItem::class, ['product' => $data[3]])
            ->call('addItem', $data[3])
            ->assertStatus(200);

        $this->assertEquals(qty_available($data[3]->id), 14);

    }

    /** @test */
    public function color_products_stock_change_when_added_to_the_cart()
    {
        $data = $this->createData(true, false,1);
        $color = $this->createColor();

        $data[3]->colors()->attach($color->id, ['quantity' => 10]);

        Livewire::test(AddCartItemColor::class, ['product' => $data[3]])
            ->set('options', ['color_id' => $color->id])
            ->call('addItem', $data[3])
            ->assertStatus(200);

        $this->assertEquals(qty_available($data[3]->id, $color->id), 9);
    }

    /** @test */
    public function size_products_stock_change_when_added_to_the_cart()
    {
        $data = $this->createData(true, true,1);

        $color = $this->createColor();

        $size = $this->createSize($data[3]);

        $size->colors()->attach($color->id, ['quantity' => 10]);


        Livewire::test(AddCartItemSize::class, ['product' => $data[3]])
            ->set('options', ['size_id' => $size->id, 'color_id' => $color->id])
            ->call('addItem', $data[3])
            ->assertStatus(200);

        $this->assertEquals(qty_available($data[3]->id, $color->id, $size->id), 9);
    }
}
