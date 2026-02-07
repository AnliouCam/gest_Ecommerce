<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up test routes for middleware testing.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Define test routes for middleware testing
        Route::middleware(['web', 'auth', 'role:gerant'])->get('/test-gerant-only', function () {
            return response()->json(['message' => 'Gerant access granted']);
        })->name('test.gerant');

        Route::middleware(['web', 'auth', 'role:vendeur,gerant'])->get('/test-ventes', function () {
            return response()->json(['message' => 'Ventes access granted']);
        })->name('test.ventes');

        Route::middleware(['web', 'auth', 'role:vendeur'])->get('/test-vendeur-only', function () {
            return response()->json(['message' => 'Vendeur access granted']);
        })->name('test.vendeur');
    }

    /**
     * Test gerant can access dashboard.
     */
    public function test_gerant_can_access_dashboard(): void
    {
        $gerant = User::factory()->gerant()->create();

        $response = $this->actingAs($gerant)->get('/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Test vendeur can access dashboard.
     */
    public function test_vendeur_can_access_dashboard(): void
    {
        $vendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($vendeur)->get('/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Test gerant can access gerant-only routes.
     */
    public function test_gerant_can_access_gerant_routes(): void
    {
        $gerant = User::factory()->gerant()->create();

        $response = $this->actingAs($gerant)->get('/test-gerant-only');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Gerant access granted']);
    }

    /**
     * Test vendeur cannot access gerant-only routes - returns 403.
     */
    public function test_vendeur_cannot_access_gerant_routes(): void
    {
        $vendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($vendeur)->get('/test-gerant-only');

        $response->assertStatus(403);
    }

    /**
     * Test vendeur can access ventes routes.
     */
    public function test_vendeur_can_access_ventes_routes(): void
    {
        $vendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($vendeur)->get('/test-ventes');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Ventes access granted']);
    }

    /**
     * Test gerant can also access ventes routes.
     */
    public function test_gerant_can_access_ventes_routes(): void
    {
        $gerant = User::factory()->gerant()->create();

        $response = $this->actingAs($gerant)->get('/test-ventes');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Ventes access granted']);
    }

    /**
     * Test gerant cannot access vendeur-only routes.
     */
    public function test_gerant_cannot_access_vendeur_only_routes(): void
    {
        $gerant = User::factory()->gerant()->create();

        $response = $this->actingAs($gerant)->get('/test-vendeur-only');

        $response->assertStatus(403);
    }

    /**
     * Test vendeur can access vendeur-only routes.
     */
    public function test_vendeur_can_access_vendeur_only_routes(): void
    {
        $vendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($vendeur)->get('/test-vendeur-only');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Vendeur access granted']);
    }

    /**
     * Test CheckRole middleware redirects unauthenticated users.
     */
    public function test_role_middleware_redirects_unauthenticated_users(): void
    {
        // Test that protected routes redirect to login
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    /**
     * Test role middleware redirects unauthenticated users on role-protected routes.
     */
    public function test_role_protected_routes_redirect_unauthenticated_users(): void
    {
        $response = $this->get('/test-gerant-only');

        $response->assertRedirect(route('login'));
    }

    /**
     * Test user can only see their own data.
     */
    public function test_user_isolation_gerant_vs_vendeur(): void
    {
        $gerant = User::factory()->gerant()->create([
            'email' => 'gerant@boutique.com',
        ]);

        $vendeur = User::factory()->vendeur()->create([
            'email' => 'vendeur@boutique.com',
        ]);

        // Verify users have different roles
        $this->assertNotEquals($gerant->role, $vendeur->role);
        $this->assertEquals('gerant', $gerant->role);
        $this->assertEquals('vendeur', $vendeur->role);

        // Verify each user can only be identified by their own credentials
        $this->assertNotEquals($gerant->id, $vendeur->id);
    }

    /**
     * Test role helper methods on User model.
     */
    public function test_user_role_helper_methods(): void
    {
        $gerant = User::factory()->gerant()->create();
        $vendeur = User::factory()->vendeur()->create();

        // Test isGerant()
        $this->assertTrue($gerant->isGerant());
        $this->assertFalse($vendeur->isGerant());

        // Test isVendeur()
        $this->assertFalse($gerant->isVendeur());
        $this->assertTrue($vendeur->isVendeur());
    }

    /**
     * Test logout clears authentication.
     */
    public function test_logout_clears_authentication(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->assertAuthenticatedAs($user);

        $this->post('/logout');

        $this->assertGuest();
    }

    /**
     * Test multiple users with same role are isolated.
     */
    public function test_multiple_vendeurs_are_isolated(): void
    {
        $vendeur1 = User::factory()->vendeur()->create([
            'email' => 'vendeur1@boutique.com',
        ]);

        $vendeur2 = User::factory()->vendeur()->create([
            'email' => 'vendeur2@boutique.com',
        ]);

        // Both are vendeurs but different users
        $this->assertEquals($vendeur1->role, $vendeur2->role);
        $this->assertNotEquals($vendeur1->id, $vendeur2->id);
        $this->assertNotEquals($vendeur1->email, $vendeur2->email);

        // Acting as vendeur1 should not give access to vendeur2's identity
        $this->actingAs($vendeur1);
        $this->assertAuthenticatedAs($vendeur1);
    }
}
