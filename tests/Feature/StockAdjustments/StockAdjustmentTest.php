<?php

namespace Tests\Feature\StockAdjustments;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    private User $gerant;
    private User $vendeur;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gerant = User::factory()->gerant()->create();
        $this->vendeur = User::factory()->vendeur()->create();

        $category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $category->id,
            'quantity' => 100,
        ]);
    }

    // ============================================================
    // TESTS D'AUTORISATION - NON AUTHENTIFIE
    // ============================================================

    public function test_unauthenticated_user_is_redirected_to_login_for_index(): void
    {
        $response = $this->get(route('gerant.stock-adjustments.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_create(): void
    {
        $response = $this->get(route('gerant.stock-adjustments.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_store(): void
    {
        $response = $this->post(route('gerant.stock-adjustments.store'), []);

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_show(): void
    {
        $adjustment = StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
        ]);

        $response = $this->get(route('gerant.stock-adjustments.show', $adjustment));

        $response->assertRedirect(route('login'));
    }

    // ============================================================
    // TESTS D'AUTORISATION - VENDEUR REFUSE
    // ============================================================

    public function test_vendeur_receives_403_for_index(): void
    {
        $response = $this->actingAs($this->vendeur)->get(route('gerant.stock-adjustments.index'));

        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_create(): void
    {
        $response = $this->actingAs($this->vendeur)->get(route('gerant.stock-adjustments.create'));

        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_store(): void
    {
        $response = $this->actingAs($this->vendeur)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'inventaire',
            'quantity' => 10,
            'reason' => 'Test',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    public function test_vendeur_receives_403_for_show(): void
    {
        $adjustment = StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
        ]);

        $response = $this->actingAs($this->vendeur)->get(route('gerant.stock-adjustments.show', $adjustment));

        $response->assertStatus(403);
    }

    // ============================================================
    // TESTS D'AUTORISATION - GERANT AUTORISE
    // ============================================================

    public function test_gerant_can_access_stock_adjustments_index(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.index'));

        $response->assertStatus(200);
        $response->assertViewIs('stock-adjustments.index');
    }

    public function test_gerant_can_access_stock_adjustments_create(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.create'));

        $response->assertStatus(200);
        $response->assertViewIs('stock-adjustments.create');
    }

    // ============================================================
    // TESTS INDEX
    // ============================================================

    public function test_index_displays_list_of_stock_adjustments(): void
    {
        StockAdjustment::factory()->count(5)->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.index'));

        $response->assertStatus(200);
        $response->assertViewHas('stockAdjustments', function ($stockAdjustments) {
            return $stockAdjustments->count() === 5;
        });
    }

    public function test_index_filter_by_type_works(): void
    {
        StockAdjustment::factory()->count(3)->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
            'type' => 'perte',
        ]);
        StockAdjustment::factory()->count(2)->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
            'type' => 'casse',
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.index', [
            'type' => 'perte',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('stockAdjustments', function ($stockAdjustments) {
            return $stockAdjustments->count() === 3;
        });
    }

    public function test_index_filter_by_product_works(): void
    {
        $otherProduct = Product::factory()->create(['quantity' => 50]);

        StockAdjustment::factory()->count(3)->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
        ]);
        StockAdjustment::factory()->count(2)->create([
            'product_id' => $otherProduct->id,
            'user_id' => $this->gerant->id,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.index', [
            'product_id' => $this->product->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('stockAdjustments', function ($stockAdjustments) {
            return $stockAdjustments->count() === 3;
        });
    }

    public function test_index_filter_by_date_from_works(): void
    {
        StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
            'created_at' => '2024-01-10 10:00:00',
        ]);
        StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
            'created_at' => '2024-01-15 10:00:00',
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.index', [
            'date_from' => '2024-01-12',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('stockAdjustments', function ($stockAdjustments) {
            return $stockAdjustments->count() === 1;
        });
    }

    public function test_index_filter_by_date_to_works(): void
    {
        StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
            'created_at' => '2024-01-10 10:00:00',
        ]);
        StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
            'created_at' => '2024-01-15 10:00:00',
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.index', [
            'date_to' => '2024-01-12',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('stockAdjustments', function ($stockAdjustments) {
            return $stockAdjustments->count() === 1;
        });
    }

    public function test_index_filter_by_date_range_works(): void
    {
        StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
            'created_at' => '2024-01-05 10:00:00',
        ]);
        StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
            'created_at' => '2024-01-15 10:00:00',
        ]);
        StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
            'created_at' => '2024-01-25 10:00:00',
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.index', [
            'date_from' => '2024-01-10',
            'date_to' => '2024-01-20',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('stockAdjustments', function ($stockAdjustments) {
            return $stockAdjustments->count() === 1;
        });
    }

    public function test_index_search_by_reason_works(): void
    {
        StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
            'reason' => 'Vol en magasin',
        ]);
        StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
            'reason' => 'Produit casse',
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.index', [
            'search' => 'Vol',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('stockAdjustments', function ($stockAdjustments) {
            return $stockAdjustments->count() === 1
                && $stockAdjustments->first()->reason === 'Vol en magasin';
        });
    }

    public function test_index_search_by_product_name_works(): void
    {
        $targetProduct = Product::factory()->create(['name' => 'iPhone 15', 'quantity' => 50]);
        $otherProduct = Product::factory()->create(['name' => 'Samsung Galaxy', 'quantity' => 50]);

        StockAdjustment::factory()->create([
            'product_id' => $targetProduct->id,
            'user_id' => $this->gerant->id,
        ]);
        StockAdjustment::factory()->create([
            'product_id' => $otherProduct->id,
            'user_id' => $this->gerant->id,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.index', [
            'search' => 'iPhone',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('stockAdjustments', function ($stockAdjustments) use ($targetProduct) {
            return $stockAdjustments->count() === 1
                && $stockAdjustments->first()->product_id === $targetProduct->id;
        });
    }

    public function test_index_search_by_product_sku_works(): void
    {
        $targetProduct = Product::factory()->create(['sku' => 'SKU-TARGET-123', 'quantity' => 50]);
        $otherProduct = Product::factory()->create(['sku' => 'SKU-OTHER-456', 'quantity' => 50]);

        StockAdjustment::factory()->create([
            'product_id' => $targetProduct->id,
            'user_id' => $this->gerant->id,
        ]);
        StockAdjustment::factory()->create([
            'product_id' => $otherProduct->id,
            'user_id' => $this->gerant->id,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.index', [
            'search' => 'TARGET',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('stockAdjustments', function ($stockAdjustments) use ($targetProduct) {
            return $stockAdjustments->count() === 1
                && $stockAdjustments->first()->product_id === $targetProduct->id;
        });
    }

    public function test_index_search_by_user_name_works(): void
    {
        $otherGerant = User::factory()->gerant()->create(['name' => 'Jean Pierre']);

        StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
        ]);
        StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $otherGerant->id,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.index', [
            'search' => 'Jean',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('stockAdjustments', function ($stockAdjustments) use ($otherGerant) {
            return $stockAdjustments->count() === 1
                && $stockAdjustments->first()->user_id === $otherGerant->id;
        });
    }

    public function test_index_adjustments_are_ordered_by_created_at_desc(): void
    {
        StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
            'created_at' => '2024-01-01 10:00:00',
        ]);
        StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
            'created_at' => '2024-01-15 10:00:00',
        ]);
        StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
            'created_at' => '2024-01-10 10:00:00',
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.index'));

        $response->assertStatus(200);
        $response->assertViewHas('stockAdjustments', function ($stockAdjustments) {
            $dates = $stockAdjustments->pluck('created_at')->map(fn($d) => $d->format('Y-m-d'))->toArray();
            return $dates === ['2024-01-15', '2024-01-10', '2024-01-01'];
        });
    }

    public function test_index_provides_products_list_for_filter(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.index'));

        $response->assertStatus(200);
        $response->assertViewHas('products');
    }

    public function test_index_provides_types_list_for_filter(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.index'));

        $response->assertStatus(200);
        $response->assertViewHas('types', ['perte', 'casse', 'inventaire', 'autre']);
    }

    public function test_stock_adjustments_are_paginated(): void
    {
        StockAdjustment::factory()->count(20)->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.index'));

        $response->assertStatus(200);
        $response->assertViewHas('stockAdjustments', function ($stockAdjustments) {
            return $stockAdjustments->count() === 15 && $stockAdjustments->total() === 20;
        });
    }

    // ============================================================
    // TESTS CREATE / STORE
    // ============================================================

    public function test_create_displays_form_with_products_and_types(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.create'));

        $response->assertStatus(200);
        $response->assertViewIs('stock-adjustments.create');
        $response->assertViewHas('products');
        $response->assertViewHas('types');
    }

    public function test_store_can_create_stock_adjustment_with_valid_data(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'inventaire',
            'quantity' => 10,
            'reason' => 'Correction apres comptage',
        ]);

        $response->assertRedirect(route('gerant.stock-adjustments.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('stock_adjustments', [
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
            'type' => 'inventaire',
            'quantity' => 10,
            'reason' => 'Correction apres comptage',
        ]);
    }

    public function test_store_assigns_authenticated_user_automatically(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'inventaire',
            'quantity' => 5,
        ]);

        $response->assertRedirect(route('gerant.stock-adjustments.index'));
        $this->assertDatabaseHas('stock_adjustments', [
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
        ]);
    }

    public function test_store_updates_product_stock_with_positive_quantity(): void
    {
        $initialStock = $this->product->quantity;

        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'inventaire',
            'quantity' => 20,
        ]);

        $response->assertRedirect(route('gerant.stock-adjustments.index'));
        $this->product->refresh();
        $this->assertEquals($initialStock + 20, $this->product->quantity);
    }

    public function test_store_updates_product_stock_with_negative_quantity(): void
    {
        $initialStock = $this->product->quantity;

        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'inventaire',
            'quantity' => -10,
        ]);

        $response->assertRedirect(route('gerant.stock-adjustments.index'));
        $this->product->refresh();
        $this->assertEquals($initialStock - 10, $this->product->quantity);
    }

    public function test_store_converts_positive_quantity_to_negative_for_perte(): void
    {
        $initialStock = $this->product->quantity;

        $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'perte',
            'quantity' => 5,
            'reason' => 'Vol en magasin',
        ]);

        $this->assertDatabaseHas('stock_adjustments', [
            'product_id' => $this->product->id,
            'type' => 'perte',
            'quantity' => -5,
        ]);

        $this->product->refresh();
        $this->assertEquals($initialStock - 5, $this->product->quantity);
    }

    public function test_store_converts_positive_quantity_to_negative_for_casse(): void
    {
        $initialStock = $this->product->quantity;

        $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'casse',
            'quantity' => 3,
            'reason' => 'Produit tombe',
        ]);

        $this->assertDatabaseHas('stock_adjustments', [
            'product_id' => $this->product->id,
            'type' => 'casse',
            'quantity' => -3,
        ]);

        $this->product->refresh();
        $this->assertEquals($initialStock - 3, $this->product->quantity);
    }

    public function test_store_keeps_negative_quantity_for_perte(): void
    {
        $initialStock = $this->product->quantity;

        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'perte',
            'quantity' => -5,
        ]);

        $response->assertRedirect(route('gerant.stock-adjustments.index'));
        $this->assertDatabaseHas('stock_adjustments', [
            'product_id' => $this->product->id,
            'type' => 'perte',
            'quantity' => -5,
        ]);

        $this->product->refresh();
        $this->assertEquals($initialStock - 5, $this->product->quantity);
    }

    public function test_store_allows_positive_quantity_for_inventaire(): void
    {
        $initialStock = $this->product->quantity;

        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'inventaire',
            'quantity' => 15,
        ]);

        $response->assertRedirect(route('gerant.stock-adjustments.index'));
        $this->assertDatabaseHas('stock_adjustments', [
            'product_id' => $this->product->id,
            'type' => 'inventaire',
            'quantity' => 15,
        ]);

        $this->product->refresh();
        $this->assertEquals($initialStock + 15, $this->product->quantity);
    }

    public function test_store_allows_positive_quantity_for_autre(): void
    {
        $initialStock = $this->product->quantity;

        $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'autre',
            'quantity' => 10,
            'reason' => 'Don recu',
        ]);

        $this->assertDatabaseHas('stock_adjustments', [
            'product_id' => $this->product->id,
            'type' => 'autre',
            'quantity' => 10,
        ]);

        $this->product->refresh();
        $this->assertEquals($initialStock + 10, $this->product->quantity);
    }

    public function test_store_prevents_stock_going_negative(): void
    {
        $this->product->update(['quantity' => 10]);

        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'inventaire',
            'quantity' => -15,
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('stock_adjustments', 0);
        $this->product->refresh();
        $this->assertEquals(10, $this->product->quantity);
    }

    public function test_store_prevents_stock_going_negative_for_perte(): void
    {
        $this->product->update(['quantity' => 5]);

        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'perte',
            'quantity' => 10,
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('stock_adjustments', 0);
        $this->product->refresh();
        $this->assertEquals(5, $this->product->quantity);
    }

    public function test_store_allows_exact_stock_removal(): void
    {
        $this->product->update(['quantity' => 10]);

        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'perte',
            'quantity' => 10,
        ]);

        $response->assertRedirect(route('gerant.stock-adjustments.index'));
        $this->product->refresh();
        $this->assertEquals(0, $this->product->quantity);
    }

    // ============================================================
    // TESTS VALIDATION STORE
    // ============================================================

    public function test_store_validation_fails_when_product_is_missing(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'type' => 'inventaire',
            'quantity' => 10,
        ]);

        $response->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    public function test_store_validation_fails_when_product_does_not_exist(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => 99999,
            'type' => 'inventaire',
            'quantity' => 10,
        ]);

        $response->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    public function test_store_validation_fails_when_type_is_missing(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'quantity' => 10,
        ]);

        $response->assertSessionHasErrors('type');
        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    public function test_store_validation_fails_when_type_is_invalid(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'invalid_type',
            'quantity' => 10,
        ]);

        $response->assertSessionHasErrors('type');
        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    public function test_store_accepts_all_valid_types(): void
    {
        $validTypes = ['perte', 'casse', 'inventaire', 'autre'];

        foreach ($validTypes as $type) {
            $this->product->update(['quantity' => 100]);

            $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
                'product_id' => $this->product->id,
                'type' => $type,
                'quantity' => $type === 'inventaire' || $type === 'autre' ? 5 : -5,
            ]);

            $response->assertRedirect(route('gerant.stock-adjustments.index'));
            $this->assertDatabaseHas('stock_adjustments', [
                'product_id' => $this->product->id,
                'type' => $type,
            ]);
        }
    }

    public function test_store_validation_fails_when_quantity_is_missing(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'inventaire',
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    public function test_store_validation_fails_when_quantity_is_zero(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'inventaire',
            'quantity' => 0,
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    public function test_store_validation_fails_when_quantity_is_not_integer(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'inventaire',
            'quantity' => 'abc',
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    public function test_store_accepts_reason_as_optional(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'inventaire',
            'quantity' => 10,
        ]);

        $response->assertRedirect(route('gerant.stock-adjustments.index'));
        $this->assertDatabaseHas('stock_adjustments', [
            'product_id' => $this->product->id,
            'reason' => null,
        ]);
    }

    public function test_store_validates_reason_max_length(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'inventaire',
            'quantity' => 10,
            'reason' => str_repeat('a', 1001),
        ]);

        $response->assertSessionHasErrors('reason');
        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    public function test_store_accepts_reason_at_max_length(): void
    {
        $reason = str_repeat('a', 1000);

        $response = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'inventaire',
            'quantity' => 10,
            'reason' => $reason,
        ]);

        $response->assertRedirect(route('gerant.stock-adjustments.index'));
        $this->assertDatabaseHas('stock_adjustments', [
            'product_id' => $this->product->id,
            'reason' => $reason,
        ]);
    }

    // ============================================================
    // TESTS SHOW
    // ============================================================

    public function test_show_displays_stock_adjustment_details(): void
    {
        $adjustment = StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
            'quantity' => -5,
            'type' => 'perte',
            'reason' => 'Vol suspecte',
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.show', $adjustment));

        $response->assertStatus(200);
        $response->assertViewIs('stock-adjustments.show');
        $response->assertSee($this->product->name);
        $response->assertSee($this->gerant->name);
        $response->assertSee('Perte'); // ucfirst in view
        $response->assertSee('Vol suspecte');
    }

    public function test_show_returns_404_for_nonexistent_adjustment(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.show', 99999));

        $response->assertStatus(404);
    }

    public function test_show_loads_product_and_user_relations(): void
    {
        $adjustment = StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.show', $adjustment));

        $response->assertStatus(200);
        $response->assertViewHas('stockAdjustment', function ($stockAdjustment) {
            return $stockAdjustment->relationLoaded('product')
                && $stockAdjustment->relationLoaded('user');
        });
    }

    // ============================================================
    // TESTS ROUTES INEXISTANTES (TRACABILITE)
    // Routes not defined return 404 in Laravel
    // ============================================================

    public function test_edit_route_does_not_exist(): void
    {
        $adjustment = StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
        ]);

        $response = $this->actingAs($this->gerant)->get("/gerant/stock-adjustments/{$adjustment->id}/edit");

        // Route not defined returns 404
        $response->assertStatus(404);
    }

    public function test_update_route_does_not_exist(): void
    {
        $adjustment = StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
            'quantity' => -5,
        ]);

        $response = $this->actingAs($this->gerant)->put(
            "/gerant/stock-adjustments/{$adjustment->id}",
            ['quantity' => 999]
        );

        // Route not defined returns 405 (method not allowed on existing resource route)
        $response->assertStatus(405);
        // Verify data was not modified
        $adjustment->refresh();
        $this->assertEquals(-5, $adjustment->quantity);
    }

    public function test_destroy_route_does_not_exist(): void
    {
        $adjustment = StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
        ]);
        $adjustmentId = $adjustment->id;

        $response = $this->actingAs($this->gerant)->delete("/gerant/stock-adjustments/{$adjustment->id}");

        // Route not defined returns 405 (method not allowed on existing resource route)
        $response->assertStatus(405);
        // Verify record still exists
        $this->assertDatabaseHas('stock_adjustments', ['id' => $adjustmentId]);
    }

    // ============================================================
    // TESTS SUPPLEMENTAIRES
    // ============================================================

    public function test_multiple_adjustments_for_same_product_accumulate(): void
    {
        $this->product->update(['quantity' => 100]);

        // Perte: -10 (converti de +10 a -10)
        $response1 = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'perte',
            'quantity' => 10,
        ]);
        $response1->assertRedirect(route('gerant.stock-adjustments.index'));

        // Inventaire: +5
        $response2 = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'inventaire',
            'quantity' => 5,
        ]);
        $response2->assertRedirect(route('gerant.stock-adjustments.index'));

        // Casse: -3 (converti de +3 a -3)
        $response3 = $this->actingAs($this->gerant)->post(route('gerant.stock-adjustments.store'), [
            'product_id' => $this->product->id,
            'type' => 'casse',
            'quantity' => 3,
        ]);
        $response3->assertRedirect(route('gerant.stock-adjustments.index'));

        // Final: 100 - 10 + 5 - 3 = 92
        $this->product->refresh();
        $this->assertEquals(92, $this->product->quantity);
        $this->assertDatabaseCount('stock_adjustments', 3);
    }

    public function test_index_search_is_escaped_for_sql_injection(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.index', [
            'search' => "'; DROP TABLE stock_adjustments; --",
        ]));

        $response->assertStatus(200);
        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    public function test_pagination_preserves_query_string(): void
    {
        StockAdjustment::factory()->count(20)->create([
            'product_id' => $this->product->id,
            'user_id' => $this->gerant->id,
            'type' => 'perte',
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.stock-adjustments.index', [
            'type' => 'perte',
            'page' => 2,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('stockAdjustments', function ($stockAdjustments) {
            return $stockAdjustments->currentPage() === 2;
        });
    }
}
