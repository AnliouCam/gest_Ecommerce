<?php

namespace Tests\Feature\StockEntries;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockEntry;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockEntryTest extends TestCase
{
    use RefreshDatabase;

    private User $gerant;
    private User $vendeur;
    private Supplier $supplier;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gerant = User::factory()->gerant()->create();
        $this->vendeur = User::factory()->vendeur()->create();

        $this->supplier = Supplier::factory()->create();
        $category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $category->id,
            'quantity' => 100,
        ]);
    }

    // ============================================================
    // TESTS D'AUTORISATION
    // ============================================================

    public function test_unauthenticated_user_is_redirected_to_login_for_index(): void
    {
        $response = $this->get(route('gerant.stock-entries.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_create(): void
    {
        $response = $this->get(route('gerant.stock-entries.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_store(): void
    {
        $response = $this->post(route('gerant.stock-entries.store'), []);

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_show(): void
    {
        $entry = StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->get(route('gerant.stock-entries.show', $entry));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_edit(): void
    {
        $entry = StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->get(route('gerant.stock-entries.edit', $entry));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_update(): void
    {
        $entry = StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->put(route('gerant.stock-entries.update', $entry), []);

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_destroy(): void
    {
        $entry = StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->delete(route('gerant.stock-entries.destroy', $entry));

        $response->assertRedirect(route('login'));
    }

    public function test_vendeur_receives_403_for_index(): void
    {
        $response = $this->actingAs($this->vendeur)->get(route('gerant.stock-entries.index'));

        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_create(): void
    {
        $response = $this->actingAs($this->vendeur)->get(route('gerant.stock-entries.create'));

        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_store(): void
    {
        $response = $this->actingAs($this->vendeur)->post(route('gerant.stock-entries.store'), [
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('stock_entries', 0);
    }

    public function test_vendeur_receives_403_for_show(): void
    {
        $entry = StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->vendeur)->get(route('gerant.stock-entries.show', $entry));

        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_edit(): void
    {
        $entry = StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->vendeur)->get(route('gerant.stock-entries.edit', $entry));

        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_update(): void
    {
        $entry = StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
        ]);

        $response = $this->actingAs($this->vendeur)->put(route('gerant.stock-entries.update', $entry), [
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 20,
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('stock_entries', ['id' => $entry->id, 'quantity' => 10]);
    }

    public function test_vendeur_receives_403_for_destroy(): void
    {
        $entry = StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->vendeur)->delete(route('gerant.stock-entries.destroy', $entry));

        $response->assertStatus(403);
        $this->assertDatabaseHas('stock_entries', ['id' => $entry->id]);
    }

    public function test_gerant_can_access_stock_entries_index(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-entries.index'));

        $response->assertStatus(200);
        $response->assertViewIs('stock-entries.index');
    }

    public function test_gerant_can_access_stock_entries_create(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-entries.create'));

        $response->assertStatus(200);
        $response->assertViewIs('stock-entries.create');
    }

    // ============================================================
    // TESTS INDEX
    // ============================================================

    public function test_index_displays_list_of_stock_entries(): void
    {
        $entries = StockEntry::factory()->count(5)->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-entries.index'));

        $response->assertStatus(200);
        $response->assertViewHas('stockEntries', function ($stockEntries) {
            return $stockEntries->count() === 5;
        });
    }

    public function test_index_filter_by_supplier_works(): void
    {
        $otherSupplier = Supplier::factory()->create();
        StockEntry::factory()->count(3)->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
        ]);
        StockEntry::factory()->count(2)->create([
            'supplier_id' => $otherSupplier->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-entries.index', [
            'supplier_id' => $this->supplier->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('stockEntries', function ($stockEntries) {
            return $stockEntries->count() === 3;
        });
    }

    public function test_index_filter_by_date_range_works(): void
    {
        StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'date' => '2024-01-10',
        ]);
        StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'date' => '2024-01-15',
        ]);
        StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'date' => '2024-01-20',
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-entries.index', [
            'date_from' => '2024-01-12',
            'date_to' => '2024-01-18',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('stockEntries', function ($stockEntries) {
            return $stockEntries->count() === 1;
        });
    }

    public function test_index_search_by_supplier_name_works(): void
    {
        $targetSupplier = Supplier::factory()->create(['name' => 'Tech Supplier']);
        $otherSupplier = Supplier::factory()->create(['name' => 'Food Supplier']);

        StockEntry::factory()->create([
            'supplier_id' => $targetSupplier->id,
            'product_id' => $this->product->id,
        ]);
        StockEntry::factory()->create([
            'supplier_id' => $otherSupplier->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-entries.index', [
            'search' => 'Tech',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('stockEntries', function ($stockEntries) use ($targetSupplier) {
            return $stockEntries->count() === 1 && $stockEntries->first()->supplier_id === $targetSupplier->id;
        });
    }

    public function test_index_search_by_product_sku_works(): void
    {
        $targetProduct = Product::factory()->create(['sku' => 'SKU-TARGET-123']);
        $otherProduct = Product::factory()->create(['sku' => 'SKU-OTHER-456']);

        StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $targetProduct->id,
        ]);
        StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $otherProduct->id,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-entries.index', [
            'search' => 'TARGET',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('stockEntries', function ($stockEntries) use ($targetProduct) {
            return $stockEntries->count() === 1 && $stockEntries->first()->product_id === $targetProduct->id;
        });
    }

    public function test_index_entries_are_ordered_by_date_desc(): void
    {
        StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'date' => '2024-01-01',
        ]);
        StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'date' => '2024-01-15',
        ]);
        StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'date' => '2024-01-10',
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-entries.index'));

        $response->assertStatus(200);
        $response->assertViewHas('stockEntries', function ($stockEntries) {
            $dates = $stockEntries->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->toArray();
            return $dates === ['2024-01-15', '2024-01-10', '2024-01-01'];
        });
    }

    public function test_index_provides_suppliers_list_for_filter(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-entries.index'));

        $response->assertStatus(200);
        $response->assertViewHas('suppliers');
    }

    public function test_index_provides_products_list_for_filter(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-entries.index'));

        $response->assertStatus(200);
        $response->assertViewHas('products');
    }

    // ============================================================
    // TESTS CREATE / STORE
    // ============================================================

    public function test_create_displays_form_with_suppliers_and_products(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-entries.create'));

        $response->assertStatus(200);
        $response->assertViewIs('stock-entries.create');
        $response->assertViewHas('suppliers');
        $response->assertViewHas('products');
    }

    public function test_store_can_create_stock_entry_with_valid_data(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-entries.store'), [
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
            'date' => '2024-01-15',
        ]);

        $response->assertRedirect(route('gerant.stock-entries.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('stock_entries', [
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
        ]);
        $entry = StockEntry::first();
        $this->assertEquals('2024-01-15', $entry->date->format('Y-m-d'));
    }

    public function test_store_updates_product_stock_automatically(): void
    {
        $initialStock = $this->product->quantity;

        $this->actingAs($this->gerant)->post(route('gerant.stock-entries.store'), [
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
            'date' => now()->format('Y-m-d'),
        ]);

        $this->product->refresh();
        $this->assertEquals($initialStock + 50, $this->product->quantity);
    }

    public function test_store_validation_fails_when_supplier_is_missing(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-entries.store'), [
            'product_id' => $this->product->id,
            'quantity' => 50,
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('supplier_id');
        $this->assertDatabaseCount('stock_entries', 0);
    }

    public function test_store_validation_fails_when_product_is_missing(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-entries.store'), [
            'supplier_id' => $this->supplier->id,
            'quantity' => 50,
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('stock_entries', 0);
    }

    public function test_store_validation_fails_when_quantity_is_missing(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-entries.store'), [
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('stock_entries', 0);
    }

    public function test_store_validation_fails_when_quantity_is_zero(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-entries.store'), [
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 0,
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('stock_entries', 0);
    }

    public function test_store_validation_fails_when_quantity_is_negative(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-entries.store'), [
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => -5,
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('stock_entries', 0);
    }

    public function test_store_validation_fails_when_date_is_missing(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-entries.store'), [
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
        ]);

        $response->assertSessionHasErrors('date');
        $this->assertDatabaseCount('stock_entries', 0);
    }

    public function test_store_validation_fails_when_date_is_in_future(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-entries.store'), [
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
            'date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('date');
        $this->assertDatabaseCount('stock_entries', 0);
    }

    public function test_store_validation_fails_when_supplier_does_not_exist(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-entries.store'), [
            'supplier_id' => 99999,
            'product_id' => $this->product->id,
            'quantity' => 50,
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('supplier_id');
        $this->assertDatabaseCount('stock_entries', 0);
    }

    public function test_store_validation_fails_when_product_does_not_exist(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-entries.store'), [
            'supplier_id' => $this->supplier->id,
            'product_id' => 99999,
            'quantity' => 50,
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('stock_entries', 0);
    }

    // ============================================================
    // TESTS SHOW
    // ============================================================

    public function test_show_displays_stock_entry_details(): void
    {
        $entry = StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 75,
            'date' => '2024-01-15',
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-entries.show', $entry));

        $response->assertStatus(200);
        $response->assertViewIs('stock-entries.show');
        $response->assertSee($this->supplier->name);
        $response->assertSee($this->product->name);
        $response->assertSee('75');
    }

    public function test_show_returns_404_for_nonexistent_entry(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-entries.show', 99999));

        $response->assertStatus(404);
    }

    // ============================================================
    // TESTS EDIT / UPDATE
    // ============================================================

    public function test_edit_displays_form_with_existing_data(): void
    {
        $entry = StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-entries.edit', $entry));

        $response->assertStatus(200);
        $response->assertViewIs('stock-entries.edit');
        $response->assertViewHas('stockEntry', $entry);
        $response->assertViewHas('suppliers');
        $response->assertViewHas('products');
    }

    public function test_update_can_modify_stock_entry_with_valid_data(): void
    {
        $entry = StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
            'date' => '2024-01-10',
        ]);

        $newSupplier = Supplier::factory()->create();

        $response = $this->actingAs($this->gerant)->put(route('gerant.stock-entries.update', $entry), [
            'supplier_id' => $newSupplier->id,
            'product_id' => $this->product->id,
            'quantity' => 75,
            'date' => '2024-01-15',
        ]);

        $response->assertRedirect(route('gerant.stock-entries.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('stock_entries', [
            'id' => $entry->id,
            'supplier_id' => $newSupplier->id,
            'quantity' => 75,
        ]);
        $entry->refresh();
        $this->assertEquals('2024-01-15', $entry->date->format('Y-m-d'));
    }

    public function test_update_adjusts_product_stock_when_quantity_changes(): void
    {
        $this->product->update(['quantity' => 100]);

        $entry = StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
        ]);

        $this->actingAs($this->gerant)->put(route('gerant.stock-entries.update', $entry), [
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 80,
            'date' => now()->format('Y-m-d'),
        ]);

        $this->product->refresh();
        $this->assertEquals(130, $this->product->quantity);
    }

    public function test_update_adjusts_stock_when_product_changes(): void
    {
        $this->product->update(['quantity' => 100]);
        $newProduct = Product::factory()->create(['quantity' => 50]);

        $entry = StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 30,
        ]);

        $this->actingAs($this->gerant)->put(route('gerant.stock-entries.update', $entry), [
            'supplier_id' => $this->supplier->id,
            'product_id' => $newProduct->id,
            'quantity' => 30,
            'date' => now()->format('Y-m-d'),
        ]);

        $this->product->refresh();
        $newProduct->refresh();

        $this->assertEquals(70, $this->product->quantity);
        $this->assertEquals(80, $newProduct->quantity);
    }

    public function test_update_validation_fails_when_quantity_is_zero(): void
    {
        $entry = StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
        ]);

        $response = $this->actingAs($this->gerant)->put(route('gerant.stock-entries.update', $entry), [
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 0,
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseHas('stock_entries', ['id' => $entry->id, 'quantity' => 50]);
    }

    public function test_update_validation_fails_when_date_is_in_future(): void
    {
        $entry = StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'date' => '2024-01-10',
        ]);

        $response = $this->actingAs($this->gerant)->put(route('gerant.stock-entries.update', $entry), [
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
            'date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('date');
    }

    public function test_update_returns_404_for_nonexistent_entry(): void
    {
        $response = $this->actingAs($this->gerant)->put(route('gerant.stock-entries.update', 99999), [
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(404);
    }

    // ============================================================
    // TESTS DESTROY
    // ============================================================

    public function test_destroy_can_delete_stock_entry(): void
    {
        $this->product->update(['quantity' => 100]);

        $entry = StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 30,
        ]);

        $response = $this->actingAs($this->gerant)->delete(route('gerant.stock-entries.destroy', $entry));

        $response->assertRedirect(route('gerant.stock-entries.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('stock_entries', ['id' => $entry->id]);
    }

    public function test_destroy_restores_product_stock(): void
    {
        $this->product->update(['quantity' => 100]);

        $entry = StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 30,
        ]);

        $this->actingAs($this->gerant)->delete(route('gerant.stock-entries.destroy', $entry));

        $this->product->refresh();
        $this->assertEquals(70, $this->product->quantity);
    }

    public function test_destroy_fails_when_product_stock_would_be_negative(): void
    {
        $this->product->update(['quantity' => 20]);

        $entry = StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
        ]);

        $response = $this->actingAs($this->gerant)->delete(route('gerant.stock-entries.destroy', $entry));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('stock_entries', ['id' => $entry->id]);
        $this->product->refresh();
        $this->assertEquals(20, $this->product->quantity);
    }

    public function test_destroy_returns_404_for_nonexistent_entry(): void
    {
        $response = $this->actingAs($this->gerant)->delete(route('gerant.stock-entries.destroy', 99999));

        $response->assertStatus(404);
    }

    // ============================================================
    // TESTS SUPPLEMENTAIRES
    // ============================================================

    public function test_stock_entries_are_paginated(): void
    {
        StockEntry::factory()->count(20)->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-entries.index'));

        $response->assertStatus(200);
        $response->assertViewHas('stockEntries', function ($stockEntries) {
            return $stockEntries->count() === 15 && $stockEntries->total() === 20;
        });
    }

    public function test_multiple_entries_for_same_product_accumulate_stock(): void
    {
        $this->product->update(['quantity' => 0]);

        $this->actingAs($this->gerant)->post(route('gerant.stock-entries.store'), [
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
            'date' => now()->format('Y-m-d'),
        ]);

        $this->actingAs($this->gerant)->post(route('gerant.stock-entries.store'), [
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'quantity' => 30,
            'date' => now()->format('Y-m-d'),
        ]);

        $this->product->refresh();
        $this->assertEquals(80, $this->product->quantity);
    }

    public function test_stock_entry_loads_supplier_and_product_relations(): void
    {
        $entry = StockEntry::factory()->create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-entries.show', $entry));

        $response->assertStatus(200);
        $response->assertViewHas('stockEntry', function ($stockEntry) {
            return $stockEntry->relationLoaded('supplier') && $stockEntry->relationLoaded('product');
        });
    }
}
