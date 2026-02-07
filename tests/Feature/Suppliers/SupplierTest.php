<?php

namespace Tests\Feature\Suppliers;

use App\Models\Product;
use App\Models\StockEntry;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
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
        $response = $this->get(route('gerant.suppliers.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_create(): void
    {
        $response = $this->get(route('gerant.suppliers.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_store(): void
    {
        $response = $this->post(route('gerant.suppliers.store'), []);

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_show(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->get(route('gerant.suppliers.show', $supplier));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_edit(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->get(route('gerant.suppliers.edit', $supplier));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_update(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->put(route('gerant.suppliers.update', $supplier), []);

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_destroy(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->delete(route('gerant.suppliers.destroy', $supplier));

        $response->assertRedirect(route('login'));
    }

    public function test_vendeur_receives_403_for_index(): void
    {
        $response = $this->actingAs($this->vendeur)->get(route('gerant.suppliers.index'));

        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_create(): void
    {
        $response = $this->actingAs($this->vendeur)->get(route('gerant.suppliers.create'));

        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_store(): void
    {
        $response = $this->actingAs($this->vendeur)->post(route('gerant.suppliers.store'), [
            'name' => 'Test Supplier',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('suppliers', ['name' => 'Test Supplier']);
    }

    public function test_vendeur_receives_403_for_show(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->actingAs($this->vendeur)->get(route('gerant.suppliers.show', $supplier));

        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_edit(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->actingAs($this->vendeur)->get(route('gerant.suppliers.edit', $supplier));

        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_update(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Original Name']);

        $response = $this->actingAs($this->vendeur)->put(route('gerant.suppliers.update', $supplier), [
            'name' => 'Modified Name',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'Original Name']);
    }

    public function test_vendeur_receives_403_for_destroy(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->actingAs($this->vendeur)->delete(route('gerant.suppliers.destroy', $supplier));

        $response->assertStatus(403);
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
    }

    public function test_gerant_can_access_suppliers_index(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.suppliers.index'));

        $response->assertStatus(200);
        $response->assertViewIs('suppliers.index');
    }

    public function test_gerant_can_access_suppliers_create(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.suppliers.create'));

        $response->assertStatus(200);
        $response->assertViewIs('suppliers.create');
    }

    // ============================================================
    // TESTS INDEX
    // ============================================================

    public function test_index_displays_list_of_suppliers(): void
    {
        $suppliers = Supplier::factory()->count(5)->create();

        $response = $this->actingAs($this->gerant)->get(route('gerant.suppliers.index'));

        $response->assertStatus(200);
        foreach ($suppliers as $supplier) {
            $response->assertSee($supplier->name);
        }
    }

    public function test_index_search_by_name_works(): void
    {
        $targetSupplier = Supplier::factory()->create(['name' => 'Tech Distributors']);
        $otherSupplier = Supplier::factory()->create(['name' => 'Food Company']);

        $response = $this->actingAs($this->gerant)->get(route('gerant.suppliers.index', [
            'search' => 'Tech',
        ]));

        $response->assertStatus(200);
        $response->assertSee($targetSupplier->name);
        $response->assertDontSee($otherSupplier->name);
    }

    public function test_index_search_by_phone_works(): void
    {
        $targetSupplier = Supplier::factory()->create(['phone' => '0612345678']);
        $otherSupplier = Supplier::factory()->create(['phone' => '0687654321']);

        $response = $this->actingAs($this->gerant)->get(route('gerant.suppliers.index', [
            'search' => '061234',
        ]));

        $response->assertStatus(200);
        $response->assertSee($targetSupplier->name);
        $response->assertDontSee($otherSupplier->name);
    }

    public function test_index_search_by_email_works(): void
    {
        $targetSupplier = Supplier::factory()->create(['email' => 'tech@example.com']);
        $otherSupplier = Supplier::factory()->create(['email' => 'food@example.com']);

        $response = $this->actingAs($this->gerant)->get(route('gerant.suppliers.index', [
            'search' => 'tech@',
        ]));

        $response->assertStatus(200);
        $response->assertSee($targetSupplier->name);
        $response->assertDontSee($otherSupplier->name);
    }

    public function test_index_search_escapes_special_characters(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Test 100% Supplier']);

        $response = $this->actingAs($this->gerant)->get(route('gerant.suppliers.index', [
            'search' => '100%',
        ]));

        $response->assertStatus(200);
    }

    public function test_index_displays_stock_entries_count_per_supplier(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Test Supplier']);
        $product = Product::factory()->create();
        StockEntry::factory()->count(3)->create([
            'supplier_id' => $supplier->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.suppliers.index'));

        $response->assertStatus(200);
        $response->assertViewHas('suppliers', function ($suppliers) use ($supplier) {
            $sup = $suppliers->firstWhere('id', $supplier->id);
            return $sup && $sup->stock_entries_count === 3;
        });
    }

    public function test_index_displays_zero_entries_for_new_supplier(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'New Supplier']);

        $response = $this->actingAs($this->gerant)->get(route('gerant.suppliers.index'));

        $response->assertStatus(200);
        $response->assertViewHas('suppliers', function ($suppliers) use ($supplier) {
            $sup = $suppliers->firstWhere('id', $supplier->id);
            return $sup && $sup->stock_entries_count === 0;
        });
    }

    // ============================================================
    // TESTS CREATE / STORE
    // ============================================================

    public function test_create_displays_form(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.suppliers.create'));

        $response->assertStatus(200);
        $response->assertViewIs('suppliers.create');
    }

    public function test_store_can_create_supplier_with_all_fields(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.suppliers.store'), [
            'name' => 'New Supplier',
            'phone' => '0612345678',
            'email' => 'contact@newsupplier.com',
        ]);

        $response->assertRedirect(route('gerant.suppliers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('suppliers', [
            'name' => 'New Supplier',
            'phone' => '0612345678',
            'email' => 'contact@newsupplier.com',
        ]);
    }

    public function test_store_can_create_supplier_with_only_name(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.suppliers.store'), [
            'name' => 'Minimal Supplier',
        ]);

        $response->assertRedirect(route('gerant.suppliers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('suppliers', [
            'name' => 'Minimal Supplier',
            'phone' => null,
            'email' => null,
        ]);
    }

    public function test_store_validation_fails_when_name_is_missing(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.suppliers.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('suppliers', 0);
    }

    public function test_store_validation_fails_when_name_exceeds_max_length(): void
    {
        $longName = str_repeat('a', 256);

        $response = $this->actingAs($this->gerant)->post(route('gerant.suppliers.store'), [
            'name' => $longName,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_validation_fails_when_email_is_invalid(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.suppliers.store'), [
            'name' => 'Test Supplier',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_store_validation_fails_when_phone_exceeds_max_length(): void
    {
        $longPhone = str_repeat('0', 51);

        $response = $this->actingAs($this->gerant)->post(route('gerant.suppliers.store'), [
            'name' => 'Test Supplier',
            'phone' => $longPhone,
        ]);

        $response->assertSessionHasErrors('phone');
    }

    // ============================================================
    // TESTS SHOW
    // ============================================================

    public function test_show_displays_supplier_details(): void
    {
        $supplier = Supplier::factory()->create([
            'name' => 'Test Supplier',
            'phone' => '0612345678',
            'email' => 'test@supplier.com',
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.suppliers.show', $supplier));

        $response->assertStatus(200);
        $response->assertViewIs('suppliers.show');
        $response->assertSee('Test Supplier');
        $response->assertSee('0612345678');
        $response->assertSee('test@supplier.com');
    }

    public function test_show_displays_stock_entries_history(): void
    {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['name' => 'Test Product']);
        StockEntry::factory()->create([
            'supplier_id' => $supplier->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'date' => '2024-01-15',
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.suppliers.show', $supplier));

        $response->assertStatus(200);
        $response->assertSee('Test Product');
        $response->assertSee('50');
    }

    public function test_show_returns_404_for_nonexistent_supplier(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.suppliers.show', 99999));

        $response->assertStatus(404);
    }

    // ============================================================
    // TESTS EDIT / UPDATE
    // ============================================================

    public function test_edit_displays_form_with_existing_data(): void
    {
        $supplier = Supplier::factory()->create([
            'name' => 'Test Supplier',
            'phone' => '0612345678',
            'email' => 'test@supplier.com',
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.suppliers.edit', $supplier));

        $response->assertStatus(200);
        $response->assertViewIs('suppliers.edit');
        $response->assertViewHas('supplier', $supplier);
        $response->assertSee('Test Supplier');
        $response->assertSee('0612345678');
        $response->assertSee('test@supplier.com');
    }

    public function test_update_can_modify_supplier_with_valid_data(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->gerant)->put(route('gerant.suppliers.update', $supplier), [
            'name' => 'New Name',
            'phone' => '0698765432',
            'email' => 'new@email.com',
        ]);

        $response->assertRedirect(route('gerant.suppliers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'New Name',
            'phone' => '0698765432',
            'email' => 'new@email.com',
        ]);
    }

    public function test_update_can_remove_optional_fields(): void
    {
        $supplier = Supplier::factory()->create([
            'name' => 'Test Supplier',
            'phone' => '0612345678',
            'email' => 'test@supplier.com',
        ]);

        $response = $this->actingAs($this->gerant)->put(route('gerant.suppliers.update', $supplier), [
            'name' => 'Test Supplier',
            'phone' => '',
            'email' => '',
        ]);

        $response->assertRedirect(route('gerant.suppliers.index'));
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Test Supplier',
            'phone' => null,
            'email' => null,
        ]);
    }

    public function test_update_validation_fails_when_name_is_missing(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Original Name']);

        $response = $this->actingAs($this->gerant)->put(route('gerant.suppliers.update', $supplier), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'Original Name']);
    }

    public function test_update_validation_fails_when_email_is_invalid(): void
    {
        $supplier = Supplier::factory()->create(['email' => 'valid@email.com']);

        $response = $this->actingAs($this->gerant)->put(route('gerant.suppliers.update', $supplier), [
            'name' => $supplier->name,
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'email' => 'valid@email.com']);
    }

    public function test_update_returns_404_for_nonexistent_supplier(): void
    {
        $response = $this->actingAs($this->gerant)->put(route('gerant.suppliers.update', 99999), [
            'name' => 'Some Name',
        ]);

        $response->assertStatus(404);
    }

    // ============================================================
    // TESTS DESTROY
    // ============================================================

    public function test_destroy_can_delete_supplier_without_stock_entries(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->actingAs($this->gerant)->delete(route('gerant.suppliers.destroy', $supplier));

        $response->assertRedirect(route('gerant.suppliers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }

    public function test_destroy_cannot_delete_supplier_with_stock_entries(): void
    {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();
        StockEntry::factory()->create([
            'supplier_id' => $supplier->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($this->gerant)->delete(route('gerant.suppliers.destroy', $supplier));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
    }

    public function test_destroy_cannot_delete_supplier_with_multiple_stock_entries(): void
    {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();
        StockEntry::factory()->count(5)->create([
            'supplier_id' => $supplier->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($this->gerant)->delete(route('gerant.suppliers.destroy', $supplier));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
        $this->assertDatabaseCount('stock_entries', 5);
    }

    public function test_destroy_returns_404_for_nonexistent_supplier(): void
    {
        $response = $this->actingAs($this->gerant)->delete(route('gerant.suppliers.destroy', 99999));

        $response->assertStatus(404);
    }

    // ============================================================
    // TESTS SUPPLEMENTAIRES
    // ============================================================

    public function test_suppliers_are_ordered_by_name(): void
    {
        Supplier::factory()->create(['name' => 'Zebra Corp']);
        Supplier::factory()->create(['name' => 'Alpha Inc']);
        Supplier::factory()->create(['name' => 'Middle Co']);

        $response = $this->actingAs($this->gerant)->get(route('gerant.suppliers.index'));

        $response->assertStatus(200);
        $response->assertViewHas('suppliers', function ($suppliers) {
            $names = $suppliers->pluck('name')->toArray();
            return $names[0] === 'Alpha Inc' && $names[1] === 'Middle Co' && $names[2] === 'Zebra Corp';
        });
    }

    public function test_suppliers_are_paginated(): void
    {
        Supplier::factory()->count(20)->create();

        $response = $this->actingAs($this->gerant)->get(route('gerant.suppliers.index'));

        $response->assertStatus(200);
        $response->assertViewHas('suppliers', function ($suppliers) {
            return $suppliers->count() === 15 && $suppliers->total() === 20;
        });
    }

    public function test_supplier_with_null_contact_info_displays_correctly(): void
    {
        $supplier = Supplier::factory()->create([
            'name' => 'No Contact Supplier',
            'phone' => null,
            'email' => null,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.suppliers.index'));

        $response->assertStatus(200);
        $response->assertSee('No Contact Supplier');
    }
}
