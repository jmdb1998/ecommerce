<?php

namespace Tests\Browser\DuskTest;

use App\Http\Livewire\AddCartItem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Livewire\Livewire;
use Tests\CreateData;
use Tests\DuskTestCase;

class OrderTest extends DuskTestCase
{
    use DatabaseMigrations;
    use CreateData;

    public function it_show_shipping_when_selected()
    {
        $this->browse(function (Browser $browser) {
            $product = $this->createProduct();
            Livewire::test(AddCartItem::class, ['product' => $product])
                ->call('addItem', $product);

            $browser->loginAs(User::factory()->create());
            $browser->visit('/orders/create')->check('@domicilio');

            $browser->assertVisible('@formulario_rellenar');
        });
    }

    public function no_shipping_pick_up_in_store()
    {
        $this->browse(function (Browser $browser) {
            $product = $this->createProduct();
            Livewire::test(AddCartItem::class, ['product' => $product])
                ->call('addItem', $product);

            $browser->loginAs(User::factory()->create());
            $browser->visit('/orders/create')->check('@tienda');

            $browser->assertMissing('@formulario_rellenar');
        });
    }
}
