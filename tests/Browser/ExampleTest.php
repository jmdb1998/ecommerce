<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function Categories_Esxist()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('Categorías')
            ->screenshot('example-test');
        });
    }

    public function It_shows_the_categories()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('Categorías')
                ->screenshot('example-test');
        });
    }
}
