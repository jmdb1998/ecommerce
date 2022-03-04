<?php

namespace Tests\Feature\LivewireTest;

use App\CreateProduct;
use App\Http\Livewire\AddCartItem;
use App\Http\Livewire\AddCartItemColor;
use App\Http\Livewire\AddCartItemSize;
use App\Http\Livewire\ShoppingCart;
use App\Http\Livewire\UpdateCartItem;
use App\Models\Color;
use App\Models\Size;
use App\Models\User;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;
    use CreateProduct;

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
    public function can_not_add_more_products_than_stock()
    {
        $normalProduct = $this->createProduct();
        $quantity = $normalProduct->quantity;

        for ($i = 0; $i < $quantity; $i++){
            Livewire::test(AddCartItem::class, ['product' => $normalProduct])
                ->call('addItem', $normalProduct);
            $normalProduct->quantity = qty_available($normalProduct->id);
        }

        $this->assertEquals(Cart::content()->first()->qty, $quantity);
    }

    /** @test */
    public function editing_shopping_cart()
    {
        $normalProduct = $this->createProduct();

        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct);

        Livewire::test(ShoppingCart::class)
            ->assertViewIs('livewire.shopping-cart')
            ->assertSee($normalProduct->name);

        Livewire::test(UpdateCartItem::class, ['rowId' => Cart::content()->first()->rowId, 'qty' => Cart::content()->first()->qty])
            ->assertViewIs('livewire.update-cart-item')
            ->call('increment')
            ->assertSet('qty',2);

        $this->assertEquals(Cart::subtotal(), $normalProduct->price*2);

        Livewire::test(UpdateCartItem::class, ['rowId' => Cart::content()->first()->rowId, 'qty' => Cart::content()->first()->qty])
            ->assertViewIs('livewire.update-cart-item')
            ->call('decrement')
            ->assertSet('qty',1);

        $this->assertEquals(Cart::subtotal(), $normalProduct->price);

    }

    /** @test */
    public function delete_the_shopping_cart()
    {
        $normalProduct = $this->createProduct();

        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct);

        Livewire::test(ShoppingCart::class)
            ->assertViewIs('livewire.shopping-cart')
            ->assertSee($normalProduct->name)
            ->call('destroy')
            ->assertSeeHtml('<p class="text-lg text-gray-700 mt-4">TU CARRITO DE COMPRAS ESTÁ VACÍO</p>');
    }

    /** @test */
    public function delete_product_in_the_shopping_cart()
    {
        $normalProduct = $this->createProduct();

        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct);

        Livewire::test(ShoppingCart::class)
            ->assertViewIs('livewire.shopping-cart')
            ->assertSee($normalProduct->name)
            ->call('delete', Cart::content()->first()->rowId)
            ->assertDontSee($normalProduct->name);

    }

    /** @test */
    public function shopping_cart_saved_in_bd_when_session_close()
    {
        $normalProduct = $this->createProduct();
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct);

        Livewire::test(ShoppingCart::class)
            ->assertViewIs('livewire.shopping-cart')
            ->assertSee($normalProduct->name);

        $this->post('/logout');

        $this->assertDatabaseHas('shoppingcart', [
            'identifier' => $user->id
        ]);
    }

    /** @test */
    public function normal_products_stock_change_when_added_to_the_cart()
    {
        $normalProduct = $this->createProduct(false, false);

        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct)
            ->assertStatus(200);

        $this->assertEquals(qty_available($normalProduct->id), 14);

    }

    /** @test */
    public function color_products_stock_change_when_added_to_the_cart()
    {
        $colorProduct = $this->createProduct(true, false);
        $color = $this->createColor();

        $colorProduct->colors()->attach($color->id, ['quantity' => 10]);

        Livewire::test(AddCartItemColor::class, ['product' => $colorProduct])
            ->set('options', ['color_id' => $color->id])
            ->call('addItem', $colorProduct)
            ->assertStatus(200);

        $this->assertEquals(qty_available($colorProduct->id, $color->id), 9);
    }

    /** @test */
    public function size_products_stock_change_when_added_to_the_cart()
    {
        $sizeProduct = $this->createProduct(true, true);

        $color = $this->createColor();

        $size = $this->createSize($sizeProduct);

        $size->colors()->attach($color->id, ['quantity' => 10]);


        Livewire::test(AddCartItemSize::class, ['product' => $sizeProduct])
            ->set('options', ['size_id' => $size->id, 'color_id' => $color->id])
            ->call('addItem', $sizeProduct)
            ->assertStatus(200);

        $this->assertEquals(qty_available($sizeProduct->id, $color->id, $size->id), 9);
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
}
