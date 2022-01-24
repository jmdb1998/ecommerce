<?php

namespace Tests\Browser;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class WelcomePageTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function categories_are_seen()
    {
        Category::factory()->create([
            'name' => 'Celulares y tablets'
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(500)
                ->clickLink('Categorías')
                ->assertSee('Celulares y tablets');

        });
    }

    /** @test */
    public function subcategories_are_seen()
    {
        $category1 = Category::factory()->create([
            'name' => 'Celulares y tablets'
        ]);

        $category2 = Category::factory()->create([
            'name' => 'Consola y videojuegos'
        ]);

        Subcategory::factory()->create([
            'category_id' => $category1->id,
            'name' => 'Celulares y smartphones'
        ]);

        Subcategory::factory()->create([
            'category_id' => $category2->id,
            'name' => 'Xbox'
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(500)
                ->clickLink('Categorías')
                ->assertSee('Celulares y tablets')
                ->mouseover('@categories')
                ->assertSee('Celulares y smartphones')
                ->assertDontSee('Xbox')
                ->screenshot('prueba_subcategoria');

        });
    }

    /** @test */
    public function login_test_not_logged()
    {
        Category::factory()->create();

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->click('@not_logged_image')
                ->pause(500)
                ->screenshot('login_test_not_logged')
                ->assertSee('Iniciar sesión')
                ->assertSee('Registrarse')
                ->screenshot('login_test_not_logged');
        });
    }

    /** @test */
    public function login_test_logged()
    {
        Category::factory()->create();
        $user = User::factory()->create();

        $this->browse(function ($browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('@login-button')
                ->assertPathIs('/dashboard')
                ->click('@profile_image')
                ->pause(500)
                ->assertSee('Perfil')
                ->assertSee('Finalizar sesión')
                ->screenshot('login_test_logged');
        });
    }

    /** @test */
    public function it_shows_five_products()
    {
        $brand = Brand::factory()->create();

        $category = Category::factory()->create();
        $category->brands()->attach($brand->id);

        $subcategory = Subcategory::factory()->create([
            'category_id' => $category->id,
        ]);

        $product1 = Product::factory()->create([
            'subcategory_id' => $subcategory->id,
        ]);

        Image::factory()->create([
            'imageable_id' => $product1->id,
            'imageable_type' => Product::class
        ]);


        $product2 = Product::factory()->create([
            'subcategory_id' => $subcategory->id,

        ]);
        Image::factory()->create([
            'imageable_id' => $product2->id,
            'imageable_type' => Product::class
        ]);


        $product3 = Product::factory()->create([
            'subcategory_id' => $subcategory->id,
        ]);
        Image::factory()->create([
            'imageable_id' => $product3->id,
            'imageable_type' => Product::class
        ]);

        $product4 = Product::factory()->create([
            'subcategory_id' => $subcategory->id,
        ]);
        Image::factory()->create([
            'imageable_id' => $product4->id,
            'imageable_type' => Product::class
        ]);

        $product5 = Product::factory()->create([
            'subcategory_id' => $subcategory->id,
        ]);
        Image::factory()->create([
            'imageable_id' => $product5->id,
            'imageable_type' => Product::class
        ]);

        $this->browse(function (Browser $browser) use ($product1,$product2,$product3,$product4,$product5) {
            $browser->visit('/')
                ->pause(500)
                ->assertSee($product1->name)
                ->assertSee($product2->name)
                /*->assertSee($product3->name)
                ->assertSee($product4->name)
                ->assertSee($product5->name)*/
                ->screenshot('it_shows_five_products');
        });
    }
}
