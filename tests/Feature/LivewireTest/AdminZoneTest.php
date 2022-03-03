<?php

namespace Tests\Feature\LivewireTest;

use App\CreateProduct;
use App\Http\Livewire\Admin\ShowProducts;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminZoneTest extends TestCase
{
    use RefreshDatabase;
    use CreateProduct;

    /** @test */
    public function not_logged_user_cant_access_admin_routes()
    {
        $this->get('/admin')->assertStatus(302)->assertRedirect('/login');
    }

    /** @test */
    public function only_admin_users_can_access_admin()
    {
        $role = Role::create(['name' => 'admin']);

        $admin = User::factory()->create()->assignRole('admin');
        $endUser = User::factory()->create();

        $this->actingAs($admin)->get('/admin')->assertStatus(200);
        $this->actingAs($endUser)->get('/admin')->assertStatus(403);
    }

    /** @test */
    public function search_bar_works_in_admin_view()
    {
        $product1 = $this->createProduct(false, false);
        $product2 = $this->createProduct(false, false);

        Livewire::test(ShowProducts::class, ['search' => $product1->name])
            ->assertSee($product1->name)
            ->assertDontSee($product2->name);
    }
}
