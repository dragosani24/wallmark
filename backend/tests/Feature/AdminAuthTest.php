<?php

namespace Tests\Feature;

use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_login_and_sees_the_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@wall.test',
            'password' => 'secret-password',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@wall.test',
            'password' => 'secret-password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create([
            'email' => 'admin@wall.test',
            'password' => 'secret-password',
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'admin@wall.test',
            'password' => 'wrong',
        ]);

        $response->assertRedirect('/admin/login');
        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_admin_can_log_out(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/logout');

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_admin_can_list_and_delete_links(): void
    {
        $user = User::factory()->create();
        $link = Link::factory()->create();

        $this->actingAs($user)->get('/admin/links')->assertOk()->assertSee($link->title);
        $this->actingAs($user)->delete("/admin/links/{$link->id}")->assertRedirect();

        $this->assertDatabaseMissing('links', ['id' => $link->id]);
    }
}
