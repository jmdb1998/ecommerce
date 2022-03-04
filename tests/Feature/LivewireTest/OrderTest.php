<?php

namespace Tests\Feature\LivewireTest;

use App\CreateData;
use App\Http\Livewire\AddCartItem;
use App\Http\Livewire\AddCartItemColor;
use App\Http\Livewire\AddCartItemSize;
use App\Http\Livewire\CreateOrder;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;
    use CreateData;

    /** @test */
    public function only_an_registered_user_can_make_an_order()
    {
        $normalProduct = $this->createProduct(false, false);
        $user = $this->createUser();
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
        $data = $this->createData(false, false, true, 1);
        $this->actingAs($data[4]);

        Livewire::test(AddCartItem::class, ['product' => $data[3]])
            ->call('addItem', $data[3])
            ->assertStatus(200);

        Livewire::test(CreateOrder::class,['contact' => 'Test', 'phone' => 633444816])
            ->call('create_order')
            ->assertStatus(200)
            ->assertRedirect('/orders/1/payment');

        $this->assertDatabaseMissing('shoppingcart', [
            'identifier' => $data[4]->id
        ]);

    }

    /** @test */
    public function when_order_is_created_normal_product_stock_changes_in_DB()
    {
        $data = $this->createData(false, false, true, 1);
        $this->actingAs($data[4]);

        Livewire::test(AddCartItem::class, ['product' => $data[3]])
            ->call('addItem', $data[3])
            ->assertStatus(200);

        Livewire::test(CreateOrder::class,['contact' => 'Test', 'phone' => 633444816])
            ->call('create_order')
            ->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $data[3]->id,
            'quantity' => 14
        ]);

    }

    /** @test */
    public function when_order_is_created_color_product_stock_changes_in_DB()
    {
        $colorProduct = $this->createProduct(true, false);
        $color = $this->createColor();

        $colorProduct->colors()->attach($color->id, ['quantity' => 10]);

        $user = $this->createUser();
        $this->actingAs($user);

        Livewire::test(AddCartItemColor::class, ['product' => $colorProduct])
            ->set('options', ['color_id' => $color->id])
            ->call('addItem', $colorProduct)
            ->assertStatus(200);

        Livewire::test(CreateOrder::class,['contact' => 'Test', 'phone' => 633444816])
            ->call('create_order')
            ->assertStatus(200);

        $this->assertDatabaseHas('color_product', [
            'product_id' => $colorProduct->id,
            'quantity' => 9
        ]);
    }

    /** @test */
    public function when_order_is_created_size_product_stock_changes_in_DB()
    {
        $sizeProduct = $this->createProduct(true, true);

        $color = $this->createColor();

        $size = $this->createSize($sizeProduct);

        $size->colors()->attach($color->id, ['quantity' => 10]);

        $user = $this->createUser();
        $this->actingAs($user);

        Livewire::test(AddCartItemSize::class, ['product' => $sizeProduct])
            ->set('options', ['size_id' => $size->id, 'color_id' => $color->id])
            ->call('addItem', $sizeProduct)
            ->assertStatus(200);

        Livewire::test(CreateOrder::class,['contact' => 'Test', 'phone' => 633444816])
            ->call('create_order')
            ->assertStatus(200);

        $this->assertDatabaseHas('color_size', [
            'quantity' => 9
        ]);
    }

    /** @test */
    public function check_the_expiration_of_pending_orders()
    {
        $normalProduct = $this->createProduct();
        $user = $this->createUser();
        $this->actingAs($user);

        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct);

        Livewire::test(CreateOrder::class,['contact' => 'Test', 'phone' => 633444816])
            ->call('create_order')
            ->assertStatus(200);

        $order = Order::first();
        $order->created_at = now()->subMinute(15);
        $order->save();

        $this->artisan('schedule:run');
        $order = Order::first();
        $this->assertEquals($order->status, 5);
    }



    /** @test */
    public function cant_access_other_user_order()
    {
        $normalProduct = $this->createProduct();
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $this->actingAs($user1);

        Livewire::test(AddCartItem::class, ['product' => $normalProduct])
            ->call('addItem', $normalProduct);

        Livewire::test(CreateOrder::class, ['contact' => 'Test', 'phone' => 633444816])
            ->call('create_order')
            ->assertStatus(200);

        $this->actingAs($user2)
            ->get('/orders/7/')->assertStatus(403);
    }
}
