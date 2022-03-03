<?php

namespace Tests\Browser\DuskTest;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProductsTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_shows_five_products()
    {
        $product1 = $this->create_product();
        $product2 = $this->create_product();
        $product3 = $this->create_product();
        $product4 = $this->create_product();
        $product5 = $this->create_product();

        $this->browse(function (Browser $browser) use ($product1,$product2,$product3,$product4,$product5) {
            $browser->visit('/')
                ->pause(500)
                ->assertSee($product1->name)
                ->pause(1000)
                ->assertSee($product2->name)
                ->pause(1000)
                ->assertSee($product3->name)
                ->pause(1000)
                ->assertSee($product4->name)
                ->pause(1000)
                ->assertSee($product5->name)
                ->screenshot('it_shows_five_products');
        });
    }

    /** @test */
    public function it_shows_published_products()
    {
        $published = $this->create_product();

        $notPublished = $this->create_product(false, false, 1);

        $this->browse(function (Browser $browser) use ($published,$notPublished) {
            $browser->visit('/')
                ->pause(500)
                ->assertSee($published->name)
                ->pause(1000)
                ->assertDontSee($notPublished->name)
                ->screenshot('it_shows_published_products');
        });
    }

    /** @test */
    public function the_details_of_the_product_can_be_seen()
    {
        $brand = Brand::factory()->create();

        $category = Category::factory()->create();
        $category->brands()->attach($brand->id);

        $subcategory = Subcategory::factory()->create([
            'category_id' => $category->id,
        ]);

        $product = Product::factory()->create([
            'subcategory_id' => $subcategory->id,
            'brand_id' => $brand->id
        ]);

        Image::factory()->create([
            'imageable_id' => $product->id,
            'imageable_type' => Product::class
        ]);

        $this->browse(function (Browser $browser) use ($category, $subcategory, $product) {
            $browser->visit('/categories/'. $category->slug)
                ->pause(500)
                ->assertSee($product->name)
                ->pause(500)
                ->click('@product')
                ->pause(500)
                ->assertPathIs('/products/' . $product->slug)
                ->assertSee($product->name)
                ->pause(500)
                ->assertSee($product->price)
                ->assertSee($product->quantity)
                ->pause(500)
                ->assertVisible('@button_+')
                ->assertVisible('@button_-')
                ->assertVisible('@carrito')
                ->screenshot('muestra_detalles_del_prodcto');
        });
    }

    /** @test */
    public function test_the_add_button()
    {
        $product = $this->create_product();

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit('/products/' . $product->slug)
                ->pause(500)
                ->assertSee($product->name);
            for ($i = 0; $i< $product->quantity; $i++){
                $browser->press('@button_+');
            };
            $browser->pause(500)
                ->assertButtonDisabled('@button_+')
                ->screenshot('boton_mas');
        });
    }

    /** @test */
    public function test_the_substract_button()
    {
        $product = $this->create_product();

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit('/products/' . $product->slug)
                ->pause(500)
                ->assertSee($product->name);
            for ($i = 0; $i< $product->quantity; $i++){
                $browser->press('@button_-');
            };
            $browser->pause(500)
                ->assertButtonDisabled('@button_-')
                ->screenshot('boton_mas');
        });
    }

    /** @test */
    public function test_the_color_and_size_can_be_seen()
    {

        $product = $this->create_product(true, true);

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit('/products/' . $product->slug)
                ->pause(500)
                ->assertSee($product->name)
                ->pause(500)
                ->assertSee('@talla')
                ->assertSee('@color')
                ->screenshot('muestra_talla_y_color');

        });
    }

    public function create_product($color = false, $size = false, $status = 2)
    {
        $brand = Brand::factory()->create();

        $category = Category::factory()->create();
        $category->brands()->attach($brand->id);

        $subcategory = Subcategory::factory()->create([
            'category_id' => $category->id,
            'color' => $color,
            'size' => $size
        ]);

        $product = Product::factory()->create([
            'subcategory_id' => $subcategory->id,
            'brand_id' => $brand->id
        ]);

        Image::factory()->create([
            'imageable_id' => $product->id,
            'imageable_type' => Product::class
        ]);

        return $product;
    }
}
