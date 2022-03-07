<?php

namespace Tests\Browser\DuskTest;

use App\CreateData;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CategoriesTest extends DuskTestCase
{
    use DatabaseMigrations;
    use CreateData;

    /** @test */
    public function can_access_categories()
    {
        $data = $this->createData(false, false, 2);

        $this->browse(function (Browser $browser) use ($data) {
            $browser->visit('/')
                ->pause(500)
                ->clickLink('Categorías')
                ->assertSee($data["category0"]->name)
                ->mouseover('@categories')
                ->pause(500)
                ->assertSee($data["subcategory0"]->name)
                ->pause(500)
                ->clickLink($data["subcategory0"]->name)
                ->pause(500)
                ->assertSee($data["category0"]->name)
                ->pause(500)
                ->assertSee($data["subcategory0"]->name)
                ->pause(500)
                ->assertSee(ucfirst($data["brand0"]->name))
                ->pause(500)
                ->assertSee($data["product"]->name)
                ->pause(500)
                ->assertDontSee($data["product1"]->name)
                ->screenshot('se_ve_la_categoria');

        });
    }

    /** @test */
    public function filter_products_by_category()
    {
        $brand = Brand::factory()->create();

        $category = Category::factory()->create();
        $category->brands()->attach($brand->id);

        $subcategory1 = Subcategory::factory()->create([
            'category_id' => $category->id,
        ]);

        $subcategory2 = Subcategory::factory()->create([
            'category_id' => $category->id,
        ]);

        $product1 = Product::factory()->create([
            'subcategory_id' => $subcategory1->id,
        ]);

        Image::factory()->create([
            'imageable_id' => $product1->id,
            'imageable_type' => Product::class
        ]);

        $product2 = Product::factory()->create([
            'subcategory_id' => $subcategory2->id,
        ]);

        Image::factory()->create([
            'imageable_id' => $product2->id,
            'imageable_type' => Product::class
        ]);

        $this->browse(function (Browser $browser) use ($category, $subcategory1, $product1, $subcategory2, $product2) {
            $browser->visit('/')
                ->pause(500)
                ->clickLink('Categorías')
                ->assertSee($category->name)
                ->mouseover('@categories')
                ->clickLink($category->name)
                ->pause(500)
                ->assertSee(ucfirst($subcategory1->name))
                ->pause(500)
                ->click("@subCategoria")
                ->pause(500)
                ->assertSee($product1->name)
                ->assertDontSee($product2->name)
                ->screenshot('filtrar_por_categoria');

        });
    }

    /** @test */
    public function filter_products_by_brand()
    {
        $brand1 = Brand::factory()->create();
        $brand2 = Brand::factory()->create();

        $category = Category::factory()->create();
        $category->brands()->attach($brand1->id);
        $category->brands()->attach($brand2->id);

        $subcategory = Subcategory::factory()->create([
            'category_id' => $category->id,
        ]);

        $product1 = Product::factory()->create([
            'subcategory_id' => $subcategory->id,
            'brand_id' => $brand1->id
        ]);

        Image::factory()->create([
            'imageable_id' => $product1->id,
            'imageable_type' => Product::class
        ]);

        $product2 = Product::factory()->create([
            'subcategory_id' => $subcategory->id,
            'brand_id' => $brand2->id
        ]);

        Image::factory()->create([
            'imageable_id' => $product2->id,
            'imageable_type' => Product::class
        ]);

        $this->browse(function (Browser $browser) use ($category, $subcategory, $brand1, $brand2, $product1, $product2) {
            $browser->visit('/categories/'. $category->slug)
                ->pause(500)
                ->assertSee(ucfirst($brand1->name))
                ->pause(500)
                ->click("@brand")
                ->pause(500)
                ->assertSee($product1->name)
                ->assertDontSee($product2->name)
                ->screenshot('filtrar_por_marca');

        });
    }
}
