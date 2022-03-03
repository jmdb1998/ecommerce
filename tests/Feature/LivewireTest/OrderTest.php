<?php

namespace Tests\Feature\LivewireTest;

use App\CreateProduct;
use App\Http\Livewire\AddCartItem;
use App\Http\Livewire\AddCartItemColor;
use App\Http\Livewire\AddCartItemSize;
use App\Http\Livewire\CreateOrder;
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

class OrderTest extends TestCase
{
    use RefreshDatabase;
    use CreateProduct;

    /** @test */
    public function only_an_registered_user_can_make_an_order()
    {
        $normalProduct = $this->createProduct(false, false);
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct)
            ->assertStatus(200);

        Livewire::test(CreateOrder::class,['contact' => 'Test', 'phone' => 633444816])
            ->call('create_order')
            ->assertStatus(200)
            ->assertRedirect('/orders/1/payment');

    }

    /** @test */
    public function an_unregistered_user_cant_make_an_order()
    {
        $normalProduct = $this->createProduct(false, false);
        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct)
            ->assertStatus(200);

        $this->get('/orders/1')->assertStatus(302)->assertRedirect('/login');

    }

    /** @test */
    public function order_is_created_and_cart_destroyed()
    {
        $normalProduct = $this->createProduct(false, false);
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct)
            ->assertStatus(200);

        Livewire::test(CreateOrder::class,['contact' => 'Test', 'phone' => 633444816])
            ->call('create_order')
            ->assertStatus(200)
            ->assertRedirect('/orders/2/payment');

        $this->assertDatabaseMissing('shoppingcart', [
            'identifier' => $user->id
        ]);

    }

    /** @test */
    public function when_order_is_created_normal_product_stock_changes_in_DB()
    {
        $normalProduct = $this->createProduct(false, false);
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct)
            ->assertStatus(200);

        Livewire::test(CreateOrder::class,['contact' => 'Test', 'phone' => 633444816])
            ->call('create_order')
            ->assertStatus(200)
            ->assertRedirect('/orders/3/payment');

        $this->assertDatabaseHas('products', [
            'id' => $normalProduct->id,
            'quantity' => 14
        ]);

    }

    /** @test */
    public function when_order_is_created_color_product_stock_changes_in_DB()
    {
        $this->markTestIncomplete();
        $user = User::factory()->create();

        $colorProduct = $this->createProduct(true, false);
        $color = Color::create([
            'name' => 'prueba',
        ]);

        $colorProduct->colors()->attach($color->id, ['quantity' => 10]);

        $this->actingAs($user);

        Livewire::test(AddCartItemColor::class, ['product' => $colorProduct, 'color_id' => $color->id])
            ->call('addItem', $colorProduct)
            ->assertStatus(200);

        Livewire::test(CreateOrder::class,['contact' => 'Test', 'phone' => 633444816])
            ->call('create_order')
            ->assertStatus(200)
            ->assertRedirect('/orders/1/payment');

        $this->assertDatabaseHas('color_product', [
            'id' => $colorProduct->id,
            'quantity' => 9
        ]);
    }

    /** @test */
    public function when_order_is_created_size_product_stock_changes_in_DB()
    {
        $this->markTestIncomplete();

        $sizeProduct = $this->createProduct(true, true);

        $color = Color::create([
            'name' => 'prueba',
        ]);

        $size = Size::factory([
            'name' => 'prueba_talla',
            'product_id' => $sizeProduct->id
        ])->create();

        $sizeProduct->colors()->attach($color->id, ['quantity' => 10]);
        $size->colors()->attach($sizeProduct->id, ['quantity' => 10]);

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(AddCartItemSize::class, ['product' => $sizeProduct])
            ->call('addItem', $sizeProduct)
            ->assertStatus(200);

        Livewire::test(CreateOrder::class,['contact' => 'Test', 'phone' => 633444816])
            ->call('create_order')
            ->assertStatus(200)
            ->assertRedirect('/orders/5/payment');

        $this->assertDatabaseHas('color_size', [
            'color_id' => $color->id,
            'size_id' => $size->id,
            'quantity' => 9
        ]);
    }
}
