<?php

namespace Tests\Feature\LivewireTest;

use App\Http\Livewire\Admin\ShowProducts;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\CreateData;
use Tests\TestCase;

class AdminZoneTest extends TestCase
{
    use RefreshDatabase;
    use CreateData;

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
        $data = $this->createData(false, false,2,2);

        Livewire::test(ShowProducts::class, ['search' => $data["product0"]->name])
            ->assertSee($data["product0"]->name)
            ->assertDontSee($data["product1"]->name);
    }
}
