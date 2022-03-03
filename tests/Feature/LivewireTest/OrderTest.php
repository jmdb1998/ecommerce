<?php

namespace Tests\Feature\LivewireTest;

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
            ->assertRedirect('/orders/1/payment');

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
            ->assertRedirect('/orders/1/payment');

        $this->assertDatabaseHas('products', [
            'id' => $normalProduct->id,
            'quantity' => 14
        ]);

    }

    /** @test */
    public function when_order_is_created_color_product_stock_changes_in_DB()
    {
        $colorProduct = $this->createProduct(true, false);
        $color = Color::find(1);
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(AddCartItemColor::class, ['product' => $colorProduct])
            ->call('addItem', $colorProduct)
            ->assertStatus(200);

        Livewire::test(CreateOrder::class,['contact' => 'Test', 'phone' => 633444816])
            ->call('create_order')
            ->assertStatus(200)
            ->assertRedirect('/orders/1/payment');

        $this->assertDatabaseHas('color_product', [
            /*'color_id' => $color->id,*/
            'product_id' => $colorProduct->id,
            'quantity' => 9
        ]);

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

    }

    /** @test */
    public function when_order_is_created_size_product_stock_changes_in_DB()
    {
        $sizeProduct = $this->createProduct(true, true);
        $color = Color::first();
        $size = Size::first();
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(AddCartItemSize::class, ['product' => $sizeProduct])
            ->call('addItem', $sizeProduct)
            ->assertStatus(200);

        Livewire::test(CreateOrder::class,['contact' => 'Test', 'phone' => 633444816])
            ->call('create_order')
            ->assertStatus(200)
            ->assertRedirect('/orders/1/payment');

        $this->assertDatabaseHas('color_size', [
            'color_id' => $color->id,
            'size_id' => $size->id,
            'quantity' => 9
        ]);

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

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
