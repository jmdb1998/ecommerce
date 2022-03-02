<?php

namespace Tests\Feature\LivewireTest;

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
    }

    /** @test */
    public function search_testing()
    {
        $normalProduct = $this->createProduct(false, false);
        $normalProduct2 = $this->createProduct(false, false);

        Livewire::test(Search::class, ['search' => $normalProduct->name])
            ->assertViewIs('livewire.search')
            ->assertSee('products', $normalProduct->name)
            ->assertDontSee($normalProduct2->name);
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
        $normalProduct = $this->createProduct(false, false);

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
        $normalProduct = $this->createProduct(false, false);

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
        $normalProduct = $this->createProduct(false, false);
        $user = User::factory()->create();
        $this->actingAs($user);

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
    public function shopping_cart_is_restored_when_login_back_in()
    {
        $this->markTestIncomplete();

        $normalProduct = $this->createProduct(false, false);
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

        $this->actingAs($user);

        Livewire::test(ShoppingCart::class)
            ->assertViewIs('livewire.shopping-cart')
            ->assertSee($normalProduct->name);
    }


    /** @test */
    public function normal_products_stock_change_when_added_to_the_cart()
    {
        $normalProduct = $this->createProduct(false, false);

        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct)
            ->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $normalProduct->id,
            'quantity' => 14
        ]);

        $this->markTestIncomplete();

    }

    /** @test */
    public function color_products_stock_change_when_added_to_the_cart()
    {
        $colorProduct = $this->createProduct(true, false);
        $color = Color::create([
            'name' => 'prueba',
        ]);

        $colorProduct->colors()->attach($color->id);

        Livewire::test(AddCartItemColor::class, ['product' => $colorProduct])
            ->call('addItem', $colorProduct)
            ->assertStatus(200);

        $this->assertEquals(Cart::content()->first()->name, $colorProduct->name);

        $this->markTestIncomplete();
    }

    /** @test */
    public function size_products_stock_change_when_added_to_the_cart()
    {
        $sizeProduct = $this->createProduct(true, true);
        $color = Color::factory()->create();
        $color->attach();

        Livewire::test(AddCartItemSize::class, ['product' => $sizeProduct])
            ->call('addItem', $sizeProduct)
            ->assertStatus(200);

        $this->assertEquals(Cart::content()->first()->name, $sizeProduct->name);

        $this->markTestIncomplete();
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
