<?php

namespace Tests\Feature\Sales;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleCancellationTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // AUTHORIZATION TESTS
    // ==========================================

    public function test_unauthenticated_user_is_redirected_to_login_for_index(): void
    {
        $response = $this->get(route('gerant.sales.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_show(): void
    {
        $sale = Sale::factory()->create();
        $response = $this->get(route('gerant.sales.show', $sale));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_cancel_form(): void
    {
        $sale = Sale::factory()->create();
        $response = $this->get(route('gerant.sales.cancel.form', $sale));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_cancel(): void
    {
        $sale = Sale::factory()->create();
        $response = $this->post(route('gerant.sales.cancel', $sale));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_cancelled_history(): void
    {
        $response = $this->get(route('gerant.sales.cancelled'));
        $response->assertRedirect(route('login'));
    }

    public function test_vendeur_receives_403_for_index(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $response = $this->actingAs($vendeur)->get(route('gerant.sales.index'));
        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_show(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $sale = Sale::factory()->create();
        $response = $this->actingAs($vendeur)->get(route('gerant.sales.show', $sale));
        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_cancel_form(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $sale = Sale::factory()->create();
        $response = $this->actingAs($vendeur)->get(route('gerant.sales.cancel.form', $sale));
        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_cancel(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $sale = Sale::factory()->create();
        $response = $this->actingAs($vendeur)->post(route('gerant.sales.cancel', $sale), [
            'cancel_reason' => 'Test reason for cancellation',
        ]);
        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_cancelled_history(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $response = $this->actingAs($vendeur)->get(route('gerant.sales.cancelled'));
        $response->assertStatus(403);
    }

    public function test_gerant_can_access_sales_index(): void
    {
        $gerant = User::factory()->gerant()->create();
        $response = $this->actingAs($gerant)->get(route('gerant.sales.index'));
        $response->assertStatus(200);
    }

    public function test_gerant_can_access_sales_show(): void
    {
        $gerant = User::factory()->gerant()->create();
        $sale = Sale::factory()->create();
        $response = $this->actingAs($gerant)->get(route('gerant.sales.show', $sale));
        $response->assertStatus(200);
    }

    public function test_gerant_can_access_cancel_form(): void
    {
        $gerant = User::factory()->gerant()->create();
        $sale = Sale::factory()->create();
        $response = $this->actingAs($gerant)->get(route('gerant.sales.cancel.form', $sale));
        $response->assertStatus(200);
    }

    public function test_gerant_can_access_cancelled_history(): void
    {
        $gerant = User::factory()->gerant()->create();
        $response = $this->actingAs($gerant)->get(route('gerant.sales.cancelled'));
        $response->assertStatus(200);
    }

    // ==========================================
    // INDEX TESTS
    // ==========================================

    public function test_index_displays_list_of_sales(): void
    {
        $gerant = User::factory()->gerant()->create();
        Sale::factory()->count(3)->create();

        $response = $this->actingAs($gerant)->get(route('gerant.sales.index'));

        $response->assertStatus(200);
        $response->assertViewHas('sales', function ($sales) {
            return $sales->count() === 3;
        });
    }

    public function test_index_filter_by_status_completed(): void
    {
        $gerant = User::factory()->gerant()->create();
        Sale::factory()->count(2)->create(['status' => 'completed']);
        Sale::factory()->cancelled()->create();

        $response = $this->actingAs($gerant)->get(route('gerant.sales.index', [
            'status' => 'completed',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('sales', function ($sales) {
            return $sales->count() === 2 && $sales->every(fn ($s) => $s->status === 'completed');
        });
    }

    public function test_index_filter_by_status_cancelled(): void
    {
        $gerant = User::factory()->gerant()->create();
        Sale::factory()->count(2)->create(['status' => 'completed']);
        Sale::factory()->cancelled()->create();

        $response = $this->actingAs($gerant)->get(route('gerant.sales.index', [
            'status' => 'cancelled',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('sales', function ($sales) {
            return $sales->count() === 1 && $sales->first()->status === 'cancelled';
        });
    }

    public function test_index_filter_by_user(): void
    {
        $gerant = User::factory()->gerant()->create();
        $vendeur = User::factory()->vendeur()->create();

        Sale::factory()->count(2)->create(['user_id' => $vendeur->id]);
        Sale::factory()->create(['user_id' => $gerant->id]);

        $response = $this->actingAs($gerant)->get(route('gerant.sales.index', [
            'user_id' => $vendeur->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('sales', function ($sales) use ($vendeur) {
            return $sales->count() === 2 && $sales->every(fn ($s) => $s->user_id === $vendeur->id);
        });
    }

    public function test_index_filter_by_date_range(): void
    {
        $gerant = User::factory()->gerant()->create();

        Sale::factory()->create(['created_at' => now()->subDays(10)]);
        Sale::factory()->create(['created_at' => now()->subDays(5)]);
        Sale::factory()->create(['created_at' => now()]);

        $response = $this->actingAs($gerant)->get(route('gerant.sales.index', [
            'date_from' => now()->subDays(7)->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('sales', function ($sales) {
            return $sales->count() === 2;
        });
    }

    public function test_index_search_by_customer_name(): void
    {
        $gerant = User::factory()->gerant()->create();
        $customer = Customer::factory()->create(['name' => 'Jean Dupont']);

        Sale::factory()->create(['customer_id' => $customer->id]);
        Sale::factory()->count(2)->anonymous()->create();

        $response = $this->actingAs($gerant)->get(route('gerant.sales.index', [
            'search' => 'Jean',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('sales', function ($sales) {
            return $sales->count() === 1;
        });
    }

    public function test_sales_are_paginated(): void
    {
        $gerant = User::factory()->gerant()->create();
        Sale::factory()->count(20)->create();

        $response = $this->actingAs($gerant)->get(route('gerant.sales.index'));

        $response->assertStatus(200);
        $response->assertViewHas('sales', function ($sales) {
            return $sales->count() === 15;
        });
    }

    // ==========================================
    // SHOW TESTS
    // ==========================================

    public function test_show_displays_sale_details(): void
    {
        $gerant = User::factory()->gerant()->create();
        $product = Product::factory()->create(['name' => 'Test Product']);
        $sale = Sale::factory()->create();
        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($gerant)->get(route('gerant.sales.show', $sale));

        $response->assertStatus(200);
        $response->assertViewHas('sale', function ($s) use ($sale) {
            return $s->id === $sale->id;
        });
        $response->assertSee('Test Product');
    }

    public function test_show_displays_cancellation_info_for_cancelled_sale(): void
    {
        $gerant = User::factory()->gerant()->create();
        $sale = Sale::factory()->cancelled()->create([
            'cancel_reason' => 'Test cancellation reason',
        ]);

        $response = $this->actingAs($gerant)->get(route('gerant.sales.show', $sale));

        $response->assertStatus(200);
        $response->assertSee('Test cancellation reason');
        $response->assertSee('Annulee');
    }

    public function test_show_returns_404_for_nonexistent_sale(): void
    {
        $gerant = User::factory()->gerant()->create();

        $response = $this->actingAs($gerant)->get(route('gerant.sales.show', 99999));

        $response->assertStatus(404);
    }

    // ==========================================
    // CANCEL FORM TESTS
    // ==========================================

    public function test_cancel_form_displays_sale_info(): void
    {
        $gerant = User::factory()->gerant()->create();
        $sale = Sale::factory()->create();
        SaleItem::factory()->create(['sale_id' => $sale->id]);

        $response = $this->actingAs($gerant)->get(route('gerant.sales.cancel.form', $sale));

        $response->assertStatus(200);
        $response->assertSee('Annuler la vente');
    }

    public function test_cancel_form_redirects_if_already_cancelled(): void
    {
        $gerant = User::factory()->gerant()->create();
        $sale = Sale::factory()->cancelled()->create();

        $response = $this->actingAs($gerant)->get(route('gerant.sales.cancel.form', $sale));

        $response->assertRedirect(route('gerant.sales.show', $sale));
        $response->assertSessionHas('error');
    }

    // ==========================================
    // CANCEL ACTION TESTS
    // ==========================================

    public function test_cancel_updates_sale_status(): void
    {
        $gerant = User::factory()->gerant()->create();
        $sale = Sale::factory()->create(['status' => 'completed']);

        $response = $this->actingAs($gerant)->post(route('gerant.sales.cancel', $sale), [
            'cancel_reason' => 'Erreur de saisie du vendeur',
        ]);

        $response->assertRedirect(route('gerant.sales.show', $sale));
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'status' => 'cancelled',
            'cancelled_by' => $gerant->id,
            'cancel_reason' => 'Erreur de saisie du vendeur',
        ]);
    }

    public function test_cancel_sets_cancelled_at_timestamp(): void
    {
        $gerant = User::factory()->gerant()->create();
        $sale = Sale::factory()->create(['status' => 'completed']);

        $this->actingAs($gerant)->post(route('gerant.sales.cancel', $sale), [
            'cancel_reason' => 'Client a change d\'avis',
        ]);

        $sale->refresh();
        $this->assertNotNull($sale->cancelled_at);
        $this->assertTrue($sale->cancelled_at->isToday());
    }

    public function test_cancel_restores_product_stock(): void
    {
        $gerant = User::factory()->gerant()->create();
        $product = Product::factory()->create(['quantity' => 10]);
        $sale = Sale::factory()->create(['status' => 'completed']);
        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->actingAs($gerant)->post(route('gerant.sales.cancel', $sale), [
            'cancel_reason' => 'Annulation test',
        ]);

        $product->refresh();
        $this->assertEquals(13, $product->quantity);
    }

    public function test_cancel_restores_stock_for_multiple_items(): void
    {
        $gerant = User::factory()->gerant()->create();
        $product1 = Product::factory()->create(['quantity' => 10]);
        $product2 = Product::factory()->create(['quantity' => 20]);
        $sale = Sale::factory()->create(['status' => 'completed']);

        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);
        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $product2->id,
            'quantity' => 5,
        ]);

        $this->actingAs($gerant)->post(route('gerant.sales.cancel', $sale), [
            'cancel_reason' => 'Annulation multiple items',
        ]);

        $product1->refresh();
        $product2->refresh();
        $this->assertEquals(12, $product1->quantity);
        $this->assertEquals(25, $product2->quantity);
    }

    public function test_cancel_fails_if_already_cancelled(): void
    {
        $gerant = User::factory()->gerant()->create();
        $sale = Sale::factory()->cancelled()->create();

        $response = $this->actingAs($gerant)->post(route('gerant.sales.cancel', $sale), [
            'cancel_reason' => 'Tentative de double annulation',
        ]);

        $response->assertRedirect(route('gerant.sales.show', $sale));
        $response->assertSessionHas('error');
    }

    public function test_cancel_validation_fails_when_reason_is_missing(): void
    {
        $gerant = User::factory()->gerant()->create();
        $sale = Sale::factory()->create();

        $response = $this->actingAs($gerant)->post(route('gerant.sales.cancel', $sale), []);

        $response->assertSessionHasErrors('cancel_reason');
    }

    public function test_cancel_validation_fails_when_reason_is_too_short(): void
    {
        $gerant = User::factory()->gerant()->create();
        $sale = Sale::factory()->create();

        $response = $this->actingAs($gerant)->post(route('gerant.sales.cancel', $sale), [
            'cancel_reason' => 'court',
        ]);

        $response->assertSessionHasErrors('cancel_reason');
    }

    public function test_cancel_validation_fails_when_reason_is_too_long(): void
    {
        $gerant = User::factory()->gerant()->create();
        $sale = Sale::factory()->create();

        $response = $this->actingAs($gerant)->post(route('gerant.sales.cancel', $sale), [
            'cancel_reason' => str_repeat('a', 1001),
        ]);

        $response->assertSessionHasErrors('cancel_reason');
    }

    public function test_cancel_accepts_reason_at_min_length(): void
    {
        $gerant = User::factory()->gerant()->create();
        $sale = Sale::factory()->create();

        $response = $this->actingAs($gerant)->post(route('gerant.sales.cancel', $sale), [
            'cancel_reason' => str_repeat('a', 10),
        ]);

        $response->assertRedirect(route('gerant.sales.show', $sale));
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'status' => 'cancelled',
        ]);
    }

    // ==========================================
    // CANCELLED HISTORY TESTS
    // ==========================================

    public function test_cancelled_history_displays_only_cancelled_sales(): void
    {
        $gerant = User::factory()->gerant()->create();
        Sale::factory()->count(3)->create(['status' => 'completed']);
        Sale::factory()->count(2)->cancelled()->create();

        $response = $this->actingAs($gerant)->get(route('gerant.sales.cancelled'));

        $response->assertStatus(200);
        $response->assertViewHas('sales', function ($sales) {
            return $sales->count() === 2 && $sales->every(fn ($s) => $s->status === 'cancelled');
        });
    }

    public function test_cancelled_history_filter_by_cancelled_by(): void
    {
        $gerant1 = User::factory()->gerant()->create();
        $gerant2 = User::factory()->gerant()->create();

        Sale::factory()->cancelled()->create(['cancelled_by' => $gerant1->id]);
        Sale::factory()->count(2)->cancelled()->create(['cancelled_by' => $gerant2->id]);

        $response = $this->actingAs($gerant1)->get(route('gerant.sales.cancelled', [
            'cancelled_by' => $gerant1->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('sales', function ($sales) use ($gerant1) {
            return $sales->count() === 1 && $sales->first()->cancelled_by === $gerant1->id;
        });
    }

    public function test_cancelled_history_filter_by_date(): void
    {
        $gerant = User::factory()->gerant()->create();

        Sale::factory()->cancelled()->create(['cancelled_at' => now()->subDays(10)]);
        Sale::factory()->cancelled()->create(['cancelled_at' => now()->subDays(3)]);
        Sale::factory()->cancelled()->create(['cancelled_at' => now()]);

        $response = $this->actingAs($gerant)->get(route('gerant.sales.cancelled', [
            'date_from' => now()->subDays(5)->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('sales', function ($sales) {
            return $sales->count() === 2;
        });
    }

    public function test_cancelled_history_shows_summary_stats(): void
    {
        $gerant = User::factory()->gerant()->create();
        Sale::factory()->count(3)->cancelled()->create(['total' => 10000]);

        $response = $this->actingAs($gerant)->get(route('gerant.sales.cancelled'));

        $response->assertStatus(200);
        $response->assertViewHas('summary', function ($summary) {
            return $summary->total_cancelled === 3 && $summary->total_value == 30000;
        });
    }

    // ==========================================
    // EDGE CASES
    // ==========================================

    public function test_index_search_is_escaped_for_sql_injection(): void
    {
        $gerant = User::factory()->gerant()->create();

        $response = $this->actingAs($gerant)->get(route('gerant.sales.index', [
            'search' => "'; DROP TABLE sales; --",
        ]));

        $response->assertStatus(200);
        $this->assertDatabaseCount('sales', 0);
    }
}
