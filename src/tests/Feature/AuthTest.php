<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');

        $response
            ->assertOk()
            ->assertSee('ログイン')
            ->assertSee('新規登録');
    }

    public function test_user_can_login(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response = $this->withSession(['_token' => 'test-token'])->post('/login', [
            '_token' => 'test-token',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/jobs');
        $this->assertTrue(Auth::check());
    }

    public function test_user_can_register(): void
    {
        $response = $this->withSession(['_token' => 'test-token'])->post('/register', [
            '_token' => 'test-token',
            'name' => 'Career User',
            'email' => 'career@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/jobs');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Career User',
            'email' => 'career@example.com',
        ]);
    }

    public function test_protected_pages_redirect_guests_to_login(): void
    {
        $response = $this->get('/gmail');

        $response->assertRedirect('/login');
    }

    public function test_user_can_logout(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->withSession(['_token' => 'test-token'])->post('/logout', [
            '_token' => 'test-token',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
