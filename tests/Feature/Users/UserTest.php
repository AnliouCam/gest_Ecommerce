<?php

namespace Tests\Feature\Users;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $gerant;
    private User $vendeur;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gerant = User::factory()->gerant()->create();
        $this->vendeur = User::factory()->vendeur()->create();
    }

    // ============================================================
    // TESTS D'AUTORISATION
    // ============================================================

    public function test_unauthenticated_user_is_redirected_to_login_for_index(): void
    {
        $response = $this->get(route('gerant.users.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_create(): void
    {
        $response = $this->get(route('gerant.users.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_store(): void
    {
        $response = $this->post(route('gerant.users.store'), []);

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_show(): void
    {
        $response = $this->get(route('gerant.users.show', $this->vendeur));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_edit(): void
    {
        $response = $this->get(route('gerant.users.edit', $this->vendeur));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_update(): void
    {
        $response = $this->put(route('gerant.users.update', $this->vendeur), []);

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_destroy(): void
    {
        $response = $this->delete(route('gerant.users.destroy', $this->vendeur));

        $response->assertRedirect(route('login'));
    }

    public function test_vendeur_receives_403_for_index(): void
    {
        $response = $this->actingAs($this->vendeur)->get(route('gerant.users.index'));

        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_create(): void
    {
        $response = $this->actingAs($this->vendeur)->get(route('gerant.users.create'));

        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_store(): void
    {
        $response = $this->actingAs($this->vendeur)->post(route('gerant.users.store'), [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'vendeur',
        ]);

        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_show(): void
    {
        $response = $this->actingAs($this->vendeur)->get(route('gerant.users.show', $this->gerant));

        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_edit(): void
    {
        $response = $this->actingAs($this->vendeur)->get(route('gerant.users.edit', $this->gerant));

        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_update(): void
    {
        $response = $this->actingAs($this->vendeur)->put(route('gerant.users.update', $this->gerant), [
            'name' => 'Modified Name',
            'email' => $this->gerant->email,
            'role' => 'gerant',
        ]);

        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_destroy(): void
    {
        $otherVendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($this->vendeur)->delete(route('gerant.users.destroy', $otherVendeur));

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $otherVendeur->id]);
    }

    public function test_gerant_can_access_users_index(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('users.index');
    }

    public function test_gerant_can_access_users_create(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.users.create'));

        $response->assertStatus(200);
        $response->assertViewIs('users.create');
    }

    // ============================================================
    // TESTS INDEX
    // ============================================================

    public function test_index_displays_list_of_users(): void
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->gerant)->get(route('gerant.users.index'));

        $response->assertStatus(200);
        $response->assertViewHas('users');
    }

    public function test_index_filter_by_role_gerant(): void
    {
        User::factory()->gerant()->count(2)->create();
        User::factory()->vendeur()->count(3)->create();

        $response = $this->actingAs($this->gerant)->get(route('gerant.users.index', ['role' => 'gerant']));

        $response->assertStatus(200);
        $response->assertViewHas('users', function ($users) {
            return $users->every(fn($user) => $user->role === 'gerant');
        });
    }

    public function test_index_filter_by_role_vendeur(): void
    {
        User::factory()->gerant()->count(2)->create();
        User::factory()->vendeur()->count(3)->create();

        $response = $this->actingAs($this->gerant)->get(route('gerant.users.index', ['role' => 'vendeur']));

        $response->assertStatus(200);
        $response->assertViewHas('users', function ($users) {
            return $users->every(fn($user) => $user->role === 'vendeur');
        });
    }

    public function test_index_search_by_name_works(): void
    {
        $targetUser = User::factory()->create(['name' => 'Jean Dupont']);
        User::factory()->create(['name' => 'Marie Martin']);

        $response = $this->actingAs($this->gerant)->get(route('gerant.users.index', ['search' => 'Jean']));

        $response->assertStatus(200);
        $response->assertSee('Jean Dupont');
        $response->assertDontSee('Marie Martin');
    }

    public function test_index_search_by_email_works(): void
    {
        $targetUser = User::factory()->create(['email' => 'specific@test.com']);
        User::factory()->create(['email' => 'other@test.com']);

        $response = $this->actingAs($this->gerant)->get(route('gerant.users.index', ['search' => 'specific@']));

        $response->assertStatus(200);
        $response->assertSee('specific@test.com');
    }

    public function test_index_displays_sales_count_per_user(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.users.index'));

        $response->assertStatus(200);
        $response->assertViewHas('users', function ($users) {
            return $users->every(fn($user) => isset($user->sales_count));
        });
    }

    // ============================================================
    // TESTS CREATE / STORE
    // ============================================================

    public function test_create_displays_form(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.users.create'));

        $response->assertStatus(200);
        $response->assertViewIs('users.create');
    }

    public function test_store_can_create_user_with_valid_data(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.users.store'), [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'vendeur',
        ]);

        $response->assertRedirect(route('gerant.users.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'role' => 'vendeur',
        ]);
    }

    public function test_store_can_create_gerant(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.users.store'), [
            'name' => 'New Gerant',
            'email' => 'newgerant@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'gerant',
        ]);

        $response->assertRedirect(route('gerant.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'newgerant@test.com',
            'role' => 'gerant',
        ]);
    }

    public function test_store_hashes_password(): void
    {
        $this->actingAs($this->gerant)->post(route('gerant.users.store'), [
            'name' => 'Test User',
            'email' => 'hashtest@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'vendeur',
        ]);

        $user = User::where('email', 'hashtest@test.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertNotEquals('password123', $user->password);
    }

    public function test_store_validation_fails_when_name_is_missing(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.users.store'), [
            'email' => 'test@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'vendeur',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_validation_fails_when_email_is_missing(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.users.store'), [
            'name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'vendeur',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_store_validation_fails_when_email_is_invalid(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.users.store'), [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'vendeur',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_store_validation_fails_when_email_already_exists(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.users.store'), [
            'name' => 'Test User',
            'email' => $this->vendeur->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'vendeur',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_store_validation_fails_when_password_is_too_short(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.users.store'), [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'short',
            'password_confirmation' => 'short',
            'role' => 'vendeur',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_store_validation_fails_when_password_confirmation_does_not_match(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.users.store'), [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
            'role' => 'vendeur',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_store_validation_fails_when_role_is_invalid(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.users.store'), [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors('role');
    }

    // ============================================================
    // TESTS SHOW
    // ============================================================

    public function test_show_displays_user_details(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.users.show', $this->vendeur));

        $response->assertStatus(200);
        $response->assertViewIs('users.show');
        $response->assertSee($this->vendeur->name);
        $response->assertSee($this->vendeur->email);
    }

    public function test_show_returns_404_for_nonexistent_user(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.users.show', 99999));

        $response->assertStatus(404);
    }

    // ============================================================
    // TESTS EDIT / UPDATE
    // ============================================================

    public function test_edit_displays_form_with_existing_data(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.users.edit', $this->vendeur));

        $response->assertStatus(200);
        $response->assertViewIs('users.edit');
        $response->assertViewHas('user', $this->vendeur);
        $response->assertSee($this->vendeur->name);
        $response->assertSee($this->vendeur->email);
    }

    public function test_update_can_modify_user_name(): void
    {
        $response = $this->actingAs($this->gerant)->put(route('gerant.users.update', $this->vendeur), [
            'name' => 'Updated Name',
            'email' => $this->vendeur->email,
            'role' => 'vendeur',
        ]);

        $response->assertRedirect(route('gerant.users.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $this->vendeur->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_update_can_modify_user_email(): void
    {
        $response = $this->actingAs($this->gerant)->put(route('gerant.users.update', $this->vendeur), [
            'name' => $this->vendeur->name,
            'email' => 'newemail@test.com',
            'role' => 'vendeur',
        ]);

        $response->assertRedirect(route('gerant.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $this->vendeur->id,
            'email' => 'newemail@test.com',
        ]);
    }

    public function test_update_can_change_password(): void
    {
        $oldPassword = $this->vendeur->password;

        $this->actingAs($this->gerant)->put(route('gerant.users.update', $this->vendeur), [
            'name' => $this->vendeur->name,
            'email' => $this->vendeur->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'role' => 'vendeur',
        ]);

        $this->vendeur->refresh();
        $this->assertNotEquals($oldPassword, $this->vendeur->password);
        $this->assertTrue(Hash::check('newpassword123', $this->vendeur->password));
    }

    public function test_update_keeps_password_when_not_provided(): void
    {
        $oldPassword = $this->vendeur->password;

        $this->actingAs($this->gerant)->put(route('gerant.users.update', $this->vendeur), [
            'name' => 'Updated Name',
            'email' => $this->vendeur->email,
            'role' => 'vendeur',
        ]);

        $this->vendeur->refresh();
        $this->assertEquals($oldPassword, $this->vendeur->password);
    }

    public function test_update_can_change_role_from_vendeur_to_gerant(): void
    {
        $response = $this->actingAs($this->gerant)->put(route('gerant.users.update', $this->vendeur), [
            'name' => $this->vendeur->name,
            'email' => $this->vendeur->email,
            'role' => 'gerant',
        ]);

        $response->assertRedirect(route('gerant.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $this->vendeur->id,
            'role' => 'gerant',
        ]);
    }

    public function test_update_cannot_change_last_gerant_to_vendeur(): void
    {
        $response = $this->actingAs($this->gerant)->put(route('gerant.users.update', $this->gerant), [
            'name' => $this->gerant->name,
            'email' => $this->gerant->email,
            'role' => 'vendeur',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', [
            'id' => $this->gerant->id,
            'role' => 'gerant',
        ]);
    }

    public function test_update_can_change_gerant_to_vendeur_if_other_gerants_exist(): void
    {
        $secondGerant = User::factory()->gerant()->create();

        $response = $this->actingAs($this->gerant)->put(route('gerant.users.update', $secondGerant), [
            'name' => $secondGerant->name,
            'email' => $secondGerant->email,
            'role' => 'vendeur',
        ]);

        $response->assertRedirect(route('gerant.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $secondGerant->id,
            'role' => 'vendeur',
        ]);
    }

    public function test_update_validation_fails_when_email_already_exists_for_another_user(): void
    {
        $response = $this->actingAs($this->gerant)->put(route('gerant.users.update', $this->vendeur), [
            'name' => $this->vendeur->name,
            'email' => $this->gerant->email,
            'role' => 'vendeur',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_update_returns_404_for_nonexistent_user(): void
    {
        $response = $this->actingAs($this->gerant)->put(route('gerant.users.update', 99999), [
            'name' => 'Test',
            'email' => 'test@test.com',
            'role' => 'vendeur',
        ]);

        $response->assertStatus(404);
    }

    // ============================================================
    // TESTS DESTROY
    // ============================================================

    public function test_destroy_can_delete_user_without_sales(): void
    {
        $userToDelete = User::factory()->vendeur()->create();

        $response = $this->actingAs($this->gerant)->delete(route('gerant.users.destroy', $userToDelete));

        $response->assertRedirect(route('gerant.users.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }

    public function test_destroy_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->gerant)->delete(route('gerant.users.destroy', $this->gerant));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->gerant->id]);
    }

    public function test_destroy_cannot_delete_last_gerant(): void
    {
        $secondGerant = User::factory()->gerant()->create();

        $response = $this->actingAs($secondGerant)->delete(route('gerant.users.destroy', $this->gerant));

        $response->assertRedirect(route('gerant.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $this->gerant->id]);
    }

    public function test_destroy_cannot_delete_user_with_sales(): void
    {
        Sale::factory()->create(['user_id' => $this->vendeur->id]);

        $response = $this->actingAs($this->gerant)->delete(route('gerant.users.destroy', $this->vendeur));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->vendeur->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_user(): void
    {
        $response = $this->actingAs($this->gerant)->delete(route('gerant.users.destroy', 99999));

        $response->assertStatus(404);
    }

    // ============================================================
    // TESTS SUPPLEMENTAIRES
    // ============================================================

    public function test_users_are_ordered_by_name(): void
    {
        User::factory()->create(['name' => 'Zebra User']);
        User::factory()->create(['name' => 'Alpha User']);

        $response = $this->actingAs($this->gerant)->get(route('gerant.users.index'));

        $response->assertStatus(200);
        $response->assertViewHas('users', function ($users) {
            $names = $users->pluck('name')->toArray();
            $sortedNames = $names;
            sort($sortedNames);
            return $names === $sortedNames;
        });
    }

    public function test_users_are_paginated(): void
    {
        User::factory()->count(20)->create();

        $response = $this->actingAs($this->gerant)->get(route('gerant.users.index'));

        $response->assertStatus(200);
        $response->assertViewHas('users', function ($users) {
            return $users->count() === 15;
        });
    }

    public function test_current_user_is_highlighted_in_list(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.users.index'));

        $response->assertStatus(200);
        $response->assertSee('(vous)');
    }
}
