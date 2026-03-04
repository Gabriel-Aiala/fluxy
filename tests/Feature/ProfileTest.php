<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_unavailable(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertNotFound();
    }

    public function test_profile_update_route_is_unavailable(): void
    {
        $user = User::factory()->create();

        $status = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ])->getStatusCode();

        $this->assertContains($status, [404, 405]);
    }

    public function test_profile_delete_route_is_unavailable(): void
    {
        $user = User::factory()->create();

        $status = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ])->getStatusCode();

        $this->assertContains($status, [404, 405]);
    }
}
