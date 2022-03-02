<?php

namespace Tests\Feature\LivewireTest;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminZoneTest extends TestCase
{
    /** @test */
    public function not_logged_user_cant_access_admin_routes()
    {
        $this->get('/admin')->assertStatus(302)->assertRedirect('/login');
    }

    /** @test */
    public function only_admin_users_can_access_admin()
    {
        $admin = User::factory()->create()->assignRole('admin');
        $endUser = User::factory()->create();

        $this->actingAs($admin)->get('/admin')->assertStatus(200);
        $this->actingAs($endUser)->get('/admin')->assertStatus(403);
    }
}
