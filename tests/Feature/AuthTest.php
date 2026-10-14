<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Hospital Information System');
        $response->assertSee('Sign In to Portal');
    }

    public function test_users_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'doctor@hospital.local',
            'password' => bcrypt('password123'),
            'role' => 'doctor',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'doctor@hospital.local',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'doctor@hospital.local',
            'password' => bcrypt('password123'),
        ]);

        $this->post('/login', [
            'email' => 'doctor@hospital.local',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }
}
