<?php

namespace Tests\Browser;

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
        $brand = Brand::factory()->create();

        $category = Category::factory()->create();
        $category->brands()->attach($brand->id);

        $subcategory = Subcategory::factory()->create([
            'category_id' => $category->id,
        ]);

        $published = Product::factory()->create([
            'subcategory_id' => $subcategory->id,
        ]);

        Image::factory()->create([
            'imageable_id' => $published->id,
            'imageable_type' => Product::class
        ]);


        $notPublished = Product::factory()->create([
            'subcategory_id' => $subcategory->id,
            'status' => 1,
        ]);
        Image::factory()->create([
            'imageable_id' => $notPublished->id,
            'imageable_type' => Product::class
        ]);

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
}

