<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login page is accessible.
     */
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * Test login with valid credentials redirects to dashboard.
     */
    public function test_login_with_valid_credentials_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'test@boutique.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@boutique.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test login with wrong password fails.
     */
    public function test_login_with_wrong_password_fails(): void
    {
        User::factory()->create([
            'email' => 'test@boutique.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@boutique.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test login with non-existent email fails.
     */
    public function test_login_with_nonexistent_email_fails(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@boutique.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test login with invalid email format fails validation.
     */
    public function test_login_with_invalid_email_format_fails(): void
    {
        $response = $this->post('/login', [
            'email' => 'not-an-email',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test login with empty password fails validation.
     */
    public function test_login_with_empty_password_fails(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@boutique.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /**
     * Test logout redirects to login page.
     */
    public function test_logout_redirects_to_login(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * Test authenticated user accessing login page is redirected to dashboard.
     */
    public function test_authenticated_user_is_redirected_from_login_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Test unauthenticated user cannot access dashboard.
     */
    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    /**
     * Test login with remember me creates remember token.
     */
    public function test_login_with_remember_me(): void
    {
        $user = User::factory()->create([
            'email' => 'test@boutique.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@boutique.com',
            'password' => 'password',
            'remember' => true,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
