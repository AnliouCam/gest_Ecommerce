<?php

namespace Tests\Feature\Vendeur;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    private User $gerant;
    private User $vendeur;
    private User $otherVendeur;
    private Product $product;
    private Product $product2;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gerant = User::factory()->gerant()->create();
        $this->vendeur = User::factory()->vendeur()->create();
        $this->otherVendeur = User::factory()->vendeur()->create();

        $category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Produit Test',
            'sku' => 'SKU001',
            'sale_price' => 10000,
            'quantity' => 50,
            'max_discount' => 10,
        ]);
        $this->product2 = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Produit Deux',
            'sku' => 'SKU002',
            'sale_price' => 5000,
            'quantity' => 30,
            'max_discount' => 20,
        ]);

        $this->customer = Customer::factory()->create();
    }

    // ============================================================
    // TESTS D'AUTORISATION - NON AUTHENTIFIE
    // ============================================================

    public function test_unauthenticated_user_is_redirected_to_login_for_index(): void
    {
        $response = $this->get(route('ventes.sales.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_create(): void
    {
        $response = $this->get(route('ventes.sales.create'));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_store(): void
    {
        $response = $this->post(route('ventes.sales.store'), []);
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_show(): void
    {
        $sale = Sale::factory()->create(['user_id' => $this->vendeur->id]);
        $response = $this->get(route('ventes.sales.show', $sale));
        $response->assertRedirect(route('login'));
    }

    // ============================================================
    // TESTS D'AUTORISATION - VENDEUR
    // ============================================================

    public function test_vendeur_can_access_index(): void
    {
        $response = $this->actingAs($this->vendeur)->get(route('ventes.sales.index'));
        $response->assertStatus(200);
    }

    public function test_vendeur_can_access_create(): void
    {
        $response = $this->actingAs($this->vendeur)->get(route('ventes.sales.create'));
        $response->assertStatus(200);
    }

    public function test_vendeur_can_store_sale(): void
    {
        $response = $this->actingAs($this->vendeur)->post(route('ventes.sales.store'), [
            'payment_method' => 'especes',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2, 'discount' => 0],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales', [
            'user_id' => $this->vendeur->id,
            'payment_method' => 'especes',
            'status' => 'completed',
        ]);
    }

    public function test_vendeur_can_view_own_sale(): void
    {
        $sale = Sale::factory()->create(['user_id' => $this->vendeur->id]);

        $response = $this->actingAs($this->vendeur)->get(route('ventes.sales.show', $sale));
        $response->assertStatus(200);
    }

    public function test_vendeur_cannot_view_other_vendeur_sale(): void
    {
        $sale = Sale::factory()->create(['user_id' => $this->otherVendeur->id]);

        $response = $this->actingAs($this->vendeur)->get(route('ventes.sales.show', $sale));
        $response->assertStatus(403);
    }

    public function test_vendeur_sees_only_own_sales_in_index(): void
    {
        Sale::factory()->create(['user_id' => $this->vendeur->id]);
        Sale::factory()->create(['user_id' => $this->vendeur->id]);
        Sale::factory()->create(['user_id' => $this->otherVendeur->id]);

        $response = $this->actingAs($this->vendeur)->get(route('ventes.sales.index'));

        $response->assertStatus(200);
        $response->assertViewHas('sales', function ($sales) {
            return $sales->count() === 2;
        });
    }

    // ============================================================
    // TESTS D'AUTORISATION - GERANT
    // ============================================================

    public function test_gerant_can_access_index(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('ventes.sales.index'));
        $response->assertStatus(200);
    }

    public function test_gerant_can_access_create(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('ventes.sales.create'));
        $response->assertStatus(200);
    }

    public function test_gerant_can_view_any_sale(): void
    {
        $sale = Sale::factory()->create(['user_id' => $this->vendeur->id]);

        $response = $this->actingAs($this->gerant)->get(route('ventes.sales.show', $sale));
        $response->assertStatus(200);
    }

    public function test_gerant_sees_all_sales_in_index(): void
    {
        Sale::factory()->create(['user_id' => $this->vendeur->id]);
        Sale::factory()->create(['user_id' => $this->otherVendeur->id]);
        Sale::factory()->create(['user_id' => $this->gerant->id]);

        $response = $this->actingAs($this->gerant)->get(route('ventes.sales.index'));

        $response->assertStatus(200);
        $response->assertViewHas('sales', function ($sales) {
            return $sales->count() === 3;
        });
    }

    // ============================================================
    // TESTS CREATION VENTE - VALIDATION
    // ============================================================

    public function test_store_requires_payment_method(): void
    {
        $response = $this->actingAs($this->vendeur)->post(route('ventes.sales.store'), [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1, 'discount' => 0],
            ],
        ]);

        $response->assertSessionHasErrors('payment_method');
    }

    public function test_store_requires_valid_payment_method(): void
    {
        $response = $this->actingAs($this->vendeur)->post(route('ventes.sales.store'), [
            'payment_method' => 'bitcoin',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1, 'discount' => 0],
            ],
        ]);

        $response->assertSessionHasErrors('payment_method');
    }

    public function test_store_requires_at_least_one_item(): void
    {
        $response = $this->actingAs($this->vendeur)->post(route('ventes.sales.store'), [
            'payment_method' => 'especes',
            'items' => [],
        ]);

        $response->assertSessionHasErrors('items');
    }

    public function test_store_requires_valid_product_id(): void
    {
        $response = $this->actingAs($this->vendeur)->post(route('ventes.sales.store'), [
            'payment_method' => 'especes',
            'items' => [
                ['product_id' => 99999, 'quantity' => 1, 'discount' => 0],
            ],
        ]);

        $response->assertSessionHasErrors('items.0.product_id');
    }

    public function test_store_requires_quantity_min_1(): void
    {
        $response = $this->actingAs($this->vendeur)->post(route('ventes.sales.store'), [
            'payment_method' => 'especes',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 0, 'discount' => 0],
            ],
        ]);

        $response->assertSessionHasErrors('items.0.quantity');
    }

    // ============================================================
    // TESTS CREATION VENTE - STOCK
    // ============================================================

    public function test_store_fails_if_insufficient_stock(): void
    {
        $response = $this->actingAs($this->vendeur)->post(route('ventes.sales.store'), [
            'payment_method' => 'especes',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 100, 'discount' => 0],
            ],
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_store_decrements_product_stock(): void
    {
        $initialStock = $this->product->quantity;

        $this->actingAs($this->vendeur)->post(route('ventes.sales.store'), [
            'payment_method' => 'especes',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 5, 'discount' => 0],
            ],
        ]);

        $this->product->refresh();
        $this->assertEquals($initialStock - 5, $this->product->quantity);
    }

    // ============================================================
    // TESTS CREATION VENTE - REMISES
    // ============================================================

    public function test_store_fails_if_discount_exceeds_maximum(): void
    {
        $response = $this->actingAs($this->vendeur)->post(route('ventes.sales.store'), [
            'payment_method' => 'especes',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2, 'discount' => 3000],
            ],
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_store_accepts_valid_discount(): void
    {
        $response = $this->actingAs($this->vendeur)->post(route('ventes.sales.store'), [
            'payment_method' => 'especes',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2, 'discount' => 1500],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sale_items', [
            'product_id' => $this->product->id,
            'discount' => 1500,
        ]);
    }

    // ============================================================
    // TESTS CREATION VENTE - CALCULS
    // ============================================================

    public function test_store_calculates_total_correctly(): void
    {
        $this->actingAs($this->vendeur)->post(route('ventes.sales.store'), [
            'payment_method' => 'especes',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2, 'discount' => 1000],
                ['product_id' => $this->product2->id, 'quantity' => 3, 'discount' => 500],
            ],
        ]);

        $this->assertDatabaseHas('sales', [
            'user_id' => $this->vendeur->id,
            'total' => 33500,
            'discount' => 1500,
        ]);
    }

    public function test_store_creates_sale_items(): void
    {
        $this->actingAs($this->vendeur)->post(route('ventes.sales.store'), [
            'payment_method' => 'mobile_money',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2, 'discount' => 0],
                ['product_id' => $this->product2->id, 'quantity' => 1, 'discount' => 0],
            ],
        ]);

        $this->assertDatabaseHas('sale_items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 10000,
        ]);
        $this->assertDatabaseHas('sale_items', [
            'product_id' => $this->product2->id,
            'quantity' => 1,
            'unit_price' => 5000,
        ]);
    }

    public function test_store_with_customer(): void
    {
        $this->actingAs($this->vendeur)->post(route('ventes.sales.store'), [
            'customer_id' => $this->customer->id,
            'payment_method' => 'carte',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1, 'discount' => 0],
            ],
        ]);

        $this->assertDatabaseHas('sales', [
            'customer_id' => $this->customer->id,
        ]);
    }

    public function test_store_without_customer_is_anonymous(): void
    {
        $this->actingAs($this->vendeur)->post(route('ventes.sales.store'), [
            'payment_method' => 'especes',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1, 'discount' => 0],
            ],
        ]);

        $this->assertDatabaseHas('sales', [
            'customer_id' => null,
        ]);
    }

    // ============================================================
    // TESTS RECHERCHE PRODUITS (AJAX)
    // ============================================================

    public function test_search_products_requires_authentication(): void
    {
        $response = $this->getJson(route('ventes.sales.search-products', ['q' => 'test']));
        $response->assertStatus(401);
    }

    public function test_search_products_returns_empty_for_short_query(): void
    {
        $response = $this->actingAs($this->vendeur)
            ->getJson(route('ventes.sales.search-products', ['q' => 'a']));

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    public function test_search_products_finds_by_name(): void
    {
        $response = $this->actingAs($this->vendeur)
            ->getJson(route('ventes.sales.search-products', ['q' => 'Produit Test']));

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Produit Test']);
    }

    public function test_search_products_finds_by_sku(): void
    {
        $response = $this->actingAs($this->vendeur)
            ->getJson(route('ventes.sales.search-products', ['q' => 'SKU001']));

        $response->assertStatus(200);
        $response->assertJsonFragment(['sku' => 'SKU001']);
    }

    public function test_search_products_excludes_out_of_stock(): void
    {
        Product::factory()->create([
            'name' => 'Out Of Stock ZZZ',
            'quantity' => 0,
        ]);

        $response = $this->actingAs($this->vendeur)
            ->getJson(route('ventes.sales.search-products', ['q' => 'Out Of Stock ZZZ']));

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    public function test_search_products_returns_required_fields(): void
    {
        $response = $this->actingAs($this->vendeur)
            ->getJson(route('ventes.sales.search-products', ['q' => 'Produit Test']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'name', 'sku', 'price', 'quantity', 'max_discount', 'category'],
        ]);
    }

    // ============================================================
    // TESTS CREATION RAPIDE CLIENT (AJAX)
    // ============================================================

    public function test_quick_create_customer_requires_authentication(): void
    {
        $response = $this->postJson(route('ventes.sales.quick-create-customer'), [
            'name' => 'Nouveau Client',
            'phone' => '0600000000',
        ]);

        $response->assertStatus(401);
    }

    public function test_quick_create_customer_creates_customer(): void
    {
        $response = $this->actingAs($this->vendeur)
            ->postJson(route('ventes.sales.quick-create-customer'), [
                'name' => 'Client Rapide',
                'phone' => '0612345678',
            ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Client Rapide']);
        $this->assertDatabaseHas('customers', [
            'name' => 'Client Rapide',
            'phone' => '0612345678',
        ]);
    }

    public function test_quick_create_customer_requires_name(): void
    {
        $response = $this->actingAs($this->vendeur)
            ->postJson(route('ventes.sales.quick-create-customer'), [
                'phone' => '0612345678',
            ]);

        $response->assertStatus(422);
    }

    public function test_quick_create_customer_requires_phone(): void
    {
        $response = $this->actingAs($this->vendeur)
            ->postJson(route('ventes.sales.quick-create-customer'), [
                'name' => 'Client Rapide',
            ]);

        $response->assertStatus(422);
    }

    // ============================================================
    // TESTS FILTRES INDEX
    // ============================================================

    public function test_index_filters_by_status(): void
    {
        Sale::factory()->create(['user_id' => $this->vendeur->id, 'status' => 'completed']);
        Sale::factory()->cancelled()->create(['user_id' => $this->vendeur->id]);

        $response = $this->actingAs($this->vendeur)
            ->get(route('ventes.sales.index', ['status' => 'completed']));

        $response->assertStatus(200);
        $response->assertViewHas('sales', function ($sales) {
            return $sales->count() === 1 && $sales->first()->status === 'completed';
        });
    }

    public function test_index_filters_by_payment_method(): void
    {
        Sale::factory()->create(['user_id' => $this->vendeur->id, 'payment_method' => 'especes']);
        Sale::factory()->create(['user_id' => $this->vendeur->id, 'payment_method' => 'carte']);

        $response = $this->actingAs($this->vendeur)
            ->get(route('ventes.sales.index', ['payment_method' => 'especes']));

        $response->assertStatus(200);
        $response->assertViewHas('sales', function ($sales) {
            return $sales->count() === 1 && $sales->first()->payment_method === 'especes';
        });
    }

    public function test_index_filters_by_date_range(): void
    {
        Sale::factory()->create([
            'user_id' => $this->vendeur->id,
            'created_at' => '2024-01-15 10:00:00',
        ]);
        Sale::factory()->create([
            'user_id' => $this->vendeur->id,
            'created_at' => '2024-02-15 10:00:00',
        ]);

        $response = $this->actingAs($this->vendeur)
            ->get(route('ventes.sales.index', [
                'date_from' => '2024-01-01',
                'date_to' => '2024-01-31',
            ]));

        $response->assertStatus(200);
        $response->assertViewHas('sales', function ($sales) {
            return $sales->count() === 1;
        });
    }

    // ============================================================
    // TESTS AFFICHAGE SHOW
    // ============================================================

    public function test_show_displays_sale_details(): void
    {
        $sale = Sale::factory()->create([
            'user_id' => $this->vendeur->id,
            'customer_id' => $this->customer->id,
            'total' => 25000,
            'payment_method' => 'mobile_money',
        ]);
        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->vendeur)
            ->get(route('ventes.sales.show', $sale));

        $response->assertStatus(200);
        $response->assertSee($this->customer->name);
    }

    public function test_show_returns_404_for_nonexistent_sale(): void
    {
        $response = $this->actingAs($this->vendeur)
            ->get(route('ventes.sales.show', 99999));

        $response->assertStatus(404);
    }

    // ============================================================
    // TESTS SECURITE
    // ============================================================

    public function test_search_products_escapes_sql_injection(): void
    {
        $response = $this->actingAs($this->vendeur)
            ->getJson(route('ventes.sales.search-products', ['q' => "'; DROP TABLE products; --"]));

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', ['id' => $this->product->id]);
    }

    public function test_index_search_escapes_sql_injection(): void
    {
        $response = $this->actingAs($this->vendeur)
            ->get(route('ventes.sales.index', ['search' => "'; DROP TABLE sales; --"]));

        $response->assertStatus(200);
    }
}
