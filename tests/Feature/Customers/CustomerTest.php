<?php

namespace Tests\Feature\Customers;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // AUTHORIZATION TESTS
    // ==========================================

    public function test_unauthenticated_user_is_redirected_to_login_for_index(): void
    {
        $response = $this->get(route('ventes.customers.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_create(): void
    {
        $response = $this->get(route('ventes.customers.create'));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_store(): void
    {
        $response = $this->post(route('ventes.customers.store'));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_show(): void
    {
        $customer = Customer::factory()->create();
        $response = $this->get(route('ventes.customers.show', $customer));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_edit(): void
    {
        $customer = Customer::factory()->create();
        $response = $this->get(route('ventes.customers.edit', $customer));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_update(): void
    {
        $customer = Customer::factory()->create();
        $response = $this->put(route('ventes.customers.update', $customer));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_destroy(): void
    {
        $customer = Customer::factory()->create();
        $response = $this->delete(route('ventes.customers.destroy', $customer));
        $response->assertRedirect(route('login'));
    }

    public function test_vendeur_can_access_customers_index(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $response = $this->actingAs($vendeur)->get(route('ventes.customers.index'));
        $response->assertStatus(200);
    }

    public function test_vendeur_can_access_customers_create(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $response = $this->actingAs($vendeur)->get(route('ventes.customers.create'));
        $response->assertStatus(200);
    }

    public function test_vendeur_can_access_customers_show(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $customer = Customer::factory()->create();
        $response = $this->actingAs($vendeur)->get(route('ventes.customers.show', $customer));
        $response->assertStatus(200);
    }

    public function test_vendeur_can_access_customers_edit(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $customer = Customer::factory()->create();
        $response = $this->actingAs($vendeur)->get(route('ventes.customers.edit', $customer));
        $response->assertStatus(200);
    }

    public function test_gerant_can_access_customers_index(): void
    {
        $gerant = User::factory()->gerant()->create();
        $response = $this->actingAs($gerant)->get(route('ventes.customers.index'));
        $response->assertStatus(200);
    }

    public function test_gerant_can_access_customers_create(): void
    {
        $gerant = User::factory()->gerant()->create();
        $response = $this->actingAs($gerant)->get(route('ventes.customers.create'));
        $response->assertStatus(200);
    }

    // ==========================================
    // INDEX TESTS
    // ==========================================

    public function test_index_displays_list_of_customers(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        Customer::factory()->count(3)->create();

        $response = $this->actingAs($vendeur)->get(route('ventes.customers.index'));

        $response->assertStatus(200);
        $response->assertViewHas('customers', function ($customers) {
            return $customers->count() === 3;
        });
    }

    public function test_index_search_by_name_works(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        Customer::factory()->create(['name' => 'Jean Dupont']);
        Customer::factory()->create(['name' => 'Marie Martin']);

        $response = $this->actingAs($vendeur)->get(route('ventes.customers.index', [
            'search' => 'Jean',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('customers', function ($customers) {
            return $customers->count() === 1 && $customers->first()->name === 'Jean Dupont';
        });
    }

    public function test_index_search_by_phone_works(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        Customer::factory()->create(['phone' => '0612345678']);
        Customer::factory()->create(['phone' => '0698765432']);

        $response = $this->actingAs($vendeur)->get(route('ventes.customers.index', [
            'search' => '0612',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('customers', function ($customers) {
            return $customers->count() === 1;
        });
    }

    public function test_index_search_by_email_works(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        Customer::factory()->create(['email' => 'jean@example.com']);
        Customer::factory()->create(['email' => 'marie@example.com']);

        $response = $this->actingAs($vendeur)->get(route('ventes.customers.index', [
            'search' => 'jean@',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('customers', function ($customers) {
            return $customers->count() === 1;
        });
    }

    public function test_customers_are_paginated(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        Customer::factory()->count(20)->create();

        $response = $this->actingAs($vendeur)->get(route('ventes.customers.index'));

        $response->assertStatus(200);
        $response->assertViewHas('customers', function ($customers) {
            return $customers->count() === 15;
        });
    }

    public function test_customers_are_ordered_by_name(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        Customer::factory()->create(['name' => 'Zoe']);
        Customer::factory()->create(['name' => 'Alice']);
        Customer::factory()->create(['name' => 'Bob']);

        $response = $this->actingAs($vendeur)->get(route('ventes.customers.index'));

        $response->assertStatus(200);
        $response->assertViewHas('customers', function ($customers) {
            return $customers->first()->name === 'Alice' && $customers->last()->name === 'Zoe';
        });
    }

    // ==========================================
    // CREATE/STORE TESTS
    // ==========================================

    public function test_create_displays_form(): void
    {
        $vendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($vendeur)->get(route('ventes.customers.create'));

        $response->assertStatus(200);
        $response->assertSee('Nouveau client');
    }

    public function test_store_can_create_customer_with_valid_data(): void
    {
        $vendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($vendeur)->post(route('ventes.customers.store'), [
            'name' => 'Jean Dupont',
            'phone' => '0612345678',
            'email' => 'jean@example.com',
            'address' => '123 Rue de la Paix, Paris',
        ]);

        $response->assertRedirect(route('ventes.customers.index'));
        $this->assertDatabaseHas('customers', [
            'name' => 'Jean Dupont',
            'phone' => '0612345678',
            'email' => 'jean@example.com',
            'address' => '123 Rue de la Paix, Paris',
        ]);
    }

    public function test_store_can_create_customer_without_optional_fields(): void
    {
        $vendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($vendeur)->post(route('ventes.customers.store'), [
            'name' => 'Jean Dupont',
            'phone' => '0612345678',
        ]);

        $response->assertRedirect(route('ventes.customers.index'));
        $this->assertDatabaseHas('customers', [
            'name' => 'Jean Dupont',
            'phone' => '0612345678',
            'email' => null,
            'address' => null,
        ]);
    }

    public function test_store_validation_fails_when_name_is_missing(): void
    {
        $vendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($vendeur)->post(route('ventes.customers.store'), [
            'phone' => '0612345678',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_validation_fails_when_phone_is_missing(): void
    {
        $vendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($vendeur)->post(route('ventes.customers.store'), [
            'name' => 'Jean Dupont',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_store_validation_fails_when_email_is_invalid(): void
    {
        $vendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($vendeur)->post(route('ventes.customers.store'), [
            'name' => 'Jean Dupont',
            'phone' => '0612345678',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_store_validation_fails_when_name_is_too_long(): void
    {
        $vendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($vendeur)->post(route('ventes.customers.store'), [
            'name' => str_repeat('a', 256),
            'phone' => '0612345678',
        ]);

        $response->assertSessionHasErrors('name');
    }

    // ==========================================
    // SHOW TESTS
    // ==========================================

    public function test_show_displays_customer_details(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $customer = Customer::factory()->create(['name' => 'Jean Dupont']);

        $response = $this->actingAs($vendeur)->get(route('ventes.customers.show', $customer));

        $response->assertStatus(200);
        $response->assertSee('Jean Dupont');
    }

    public function test_show_displays_purchase_history(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $customer = Customer::factory()->create();
        Sale::factory()->count(3)->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($vendeur)->get(route('ventes.customers.show', $customer));

        $response->assertStatus(200);
        $response->assertViewHas('sales', function ($sales) {
            return $sales->count() === 3;
        });
    }

    public function test_show_displays_stats(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $customer = Customer::factory()->create();
        Sale::factory()->count(2)->create([
            'customer_id' => $customer->id,
            'status' => 'completed',
            'total' => 10000,
        ]);
        Sale::factory()->cancelled()->create([
            'customer_id' => $customer->id,
            'total' => 5000,
        ]);

        $response = $this->actingAs($vendeur)->get(route('ventes.customers.show', $customer));

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total_spent'] == 20000
                && $stats['total_orders'] === 2
                && $stats['cancelled_orders'] === 1;
        });
    }

    public function test_show_returns_404_for_nonexistent_customer(): void
    {
        $vendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($vendeur)->get(route('ventes.customers.show', 99999));

        $response->assertStatus(404);
    }

    // ==========================================
    // EDIT/UPDATE TESTS
    // ==========================================

    public function test_edit_displays_form_with_existing_data(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $customer = Customer::factory()->create([
            'name' => 'Jean Dupont',
            'phone' => '0612345678',
        ]);

        $response = $this->actingAs($vendeur)->get(route('ventes.customers.edit', $customer));

        $response->assertStatus(200);
        $response->assertSee('Jean Dupont');
        $response->assertSee('0612345678');
    }

    public function test_update_can_modify_customer_name(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $customer = Customer::factory()->create(['name' => 'Jean Dupont']);

        $response = $this->actingAs($vendeur)->put(route('ventes.customers.update', $customer), [
            'name' => 'Jean Martin',
            'phone' => $customer->phone,
        ]);

        $response->assertRedirect(route('ventes.customers.index'));
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Jean Martin',
        ]);
    }

    public function test_update_can_modify_customer_phone(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $customer = Customer::factory()->create(['phone' => '0612345678']);

        $response = $this->actingAs($vendeur)->put(route('ventes.customers.update', $customer), [
            'name' => $customer->name,
            'phone' => '0698765432',
        ]);

        $response->assertRedirect(route('ventes.customers.index'));
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'phone' => '0698765432',
        ]);
    }

    public function test_update_can_add_email(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $customer = Customer::factory()->create(['email' => null]);

        $response = $this->actingAs($vendeur)->put(route('ventes.customers.update', $customer), [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => 'jean@example.com',
        ]);

        $response->assertRedirect(route('ventes.customers.index'));
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'email' => 'jean@example.com',
        ]);
    }

    public function test_update_can_add_address(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $customer = Customer::factory()->create(['address' => null]);

        $response = $this->actingAs($vendeur)->put(route('ventes.customers.update', $customer), [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'address' => '123 Rue de la Paix',
        ]);

        $response->assertRedirect(route('ventes.customers.index'));
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'address' => '123 Rue de la Paix',
        ]);
    }

    public function test_update_validation_fails_when_name_is_missing(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $customer = Customer::factory()->create();

        $response = $this->actingAs($vendeur)->put(route('ventes.customers.update', $customer), [
            'phone' => '0612345678',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_update_validation_fails_when_phone_is_missing(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $customer = Customer::factory()->create();

        $response = $this->actingAs($vendeur)->put(route('ventes.customers.update', $customer), [
            'name' => 'Jean Dupont',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_update_returns_404_for_nonexistent_customer(): void
    {
        $vendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($vendeur)->put(route('ventes.customers.update', 99999), [
            'name' => 'Jean Dupont',
            'phone' => '0612345678',
        ]);

        $response->assertStatus(404);
    }

    // ==========================================
    // DESTROY TESTS
    // ==========================================

    public function test_destroy_can_delete_customer_without_sales(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $customer = Customer::factory()->create();

        $response = $this->actingAs($vendeur)->delete(route('ventes.customers.destroy', $customer));

        $response->assertRedirect(route('ventes.customers.index'));
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_destroy_cannot_delete_customer_with_sales(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $customer = Customer::factory()->create();
        Sale::factory()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($vendeur)->delete(route('ventes.customers.destroy', $customer));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_customer(): void
    {
        $vendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($vendeur)->delete(route('ventes.customers.destroy', 99999));

        $response->assertStatus(404);
    }

    // ==========================================
    // SECURITY TESTS
    // ==========================================

    public function test_index_search_is_escaped_for_sql_injection(): void
    {
        $vendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($vendeur)->get(route('ventes.customers.index', [
            'search' => "'; DROP TABLE customers; --",
        ]));

        $response->assertStatus(200);
        $this->assertDatabaseCount('customers', 0);
    }
}
