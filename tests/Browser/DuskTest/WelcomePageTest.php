<?php

namespace Tests\Browser\DuskTest;

use App\Models\Category;
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
}
