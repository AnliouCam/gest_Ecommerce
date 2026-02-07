<?php

namespace Tests\Feature\Vendeur;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReadOnlyTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // AUTHORIZATION TESTS
    // ==========================================

    public function test_unauthenticated_user_is_redirected_to_login_for_index(): void
    {
        $response = $this->get(route('ventes.products.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_show(): void
    {
        $product = Product::factory()->create();
        $response = $this->get(route('ventes.products.show', $product));
        $response->assertRedirect(route('login'));
    }

    public function test_vendeur_can_access_products_index(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $response = $this->actingAs($vendeur)->get(route('ventes.products.index'));
        $response->assertStatus(200);
    }

    public function test_vendeur_can_access_products_show(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $product = Product::factory()->create();
        $response = $this->actingAs($vendeur)->get(route('ventes.products.show', $product));
        $response->assertStatus(200);
    }

    public function test_gerant_can_access_products_index(): void
    {
        $gerant = User::factory()->gerant()->create();
        $response = $this->actingAs($gerant)->get(route('ventes.products.index'));
        $response->assertStatus(200);
    }

    public function test_gerant_can_access_products_show(): void
    {
        $gerant = User::factory()->gerant()->create();
        $product = Product::factory()->create();
        $response = $this->actingAs($gerant)->get(route('ventes.products.show', $product));
        $response->assertStatus(200);
    }

    // ==========================================
    // INDEX TESTS
    // ==========================================

    public function test_index_displays_list_of_products(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        Product::factory()->count(3)->create();

        $response = $this->actingAs($vendeur)->get(route('ventes.products.index'));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() === 3;
        });
    }

    public function test_index_search_by_name_works(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        Product::factory()->create(['name' => 'PC Portable HP']);
        Product::factory()->create(['name' => 'Souris Gaming']);

        $response = $this->actingAs($vendeur)->get(route('ventes.products.index', [
            'search' => 'PC Portable',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() === 1 && $products->first()->name === 'PC Portable HP';
        });
    }

    public function test_index_search_by_sku_works(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        Product::factory()->create(['sku' => 'PC-HP-001']);
        Product::factory()->create(['sku' => 'MOUSE-001']);

        $response = $this->actingAs($vendeur)->get(route('ventes.products.index', [
            'search' => 'PC-HP',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() === 1;
        });
    }

    public function test_index_filter_by_category(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $category1 = Category::factory()->create(['name' => 'Ordinateurs']);
        $category2 = Category::factory()->create(['name' => 'Accessoires']);

        Product::factory()->count(2)->create(['category_id' => $category1->id]);
        Product::factory()->create(['category_id' => $category2->id]);

        $response = $this->actingAs($vendeur)->get(route('ventes.products.index', [
            'category' => $category1->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() === 2;
        });
    }

    public function test_index_filter_by_stock_available(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        Product::factory()->create(['quantity' => 10]);
        Product::factory()->create(['quantity' => 0]);

        $response = $this->actingAs($vendeur)->get(route('ventes.products.index', [
            'stock' => 'available',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() === 1 && $products->first()->quantity > 0;
        });
    }

    public function test_index_filter_by_stock_out(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        Product::factory()->create(['quantity' => 10]);
        Product::factory()->create(['quantity' => 0]);

        $response = $this->actingAs($vendeur)->get(route('ventes.products.index', [
            'stock' => 'out',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() === 1 && $products->first()->quantity === 0;
        });
    }

    public function test_index_filter_by_stock_low(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        Product::factory()->create(['quantity' => 50, 'stock_alert' => 10]);
        Product::factory()->create(['quantity' => 3, 'stock_alert' => 5]);
        Product::factory()->create(['quantity' => 0, 'stock_alert' => 5]);

        $response = $this->actingAs($vendeur)->get(route('ventes.products.index', [
            'stock' => 'low',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() === 1 && $products->first()->quantity === 3;
        });
    }

    public function test_products_are_paginated(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $category = Category::factory()->create();
        Product::factory()->count(20)->create(['category_id' => $category->id]);

        $response = $this->actingAs($vendeur)->get(route('ventes.products.index'));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() === 15;
        });
    }

    public function test_products_are_ordered_by_name(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        Product::factory()->create(['name' => 'Zebra']);
        Product::factory()->create(['name' => 'Alpha']);
        Product::factory()->create(['name' => 'Beta']);

        $response = $this->actingAs($vendeur)->get(route('ventes.products.index'));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->first()->name === 'Alpha' && $products->last()->name === 'Zebra';
        });
    }

    public function test_index_displays_categories_for_filter(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        Category::factory()->count(3)->create();

        $response = $this->actingAs($vendeur)->get(route('ventes.products.index'));

        $response->assertStatus(200);
        $response->assertViewHas('categories', function ($categories) {
            return $categories->count() === 3;
        });
    }

    // ==========================================
    // SHOW TESTS
    // ==========================================

    public function test_show_displays_product_details(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $product = Product::factory()->create([
            'name' => 'PC Portable HP',
            'sku' => 'PC-HP-001',
            'sale_price' => 500000,
        ]);

        $response = $this->actingAs($vendeur)->get(route('ventes.products.show', $product));

        $response->assertStatus(200);
        $response->assertSee('PC Portable HP');
        $response->assertSee('PC-HP-001');
        $response->assertSee('500 000');
    }

    public function test_show_displays_category(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $category = Category::factory()->create(['name' => 'Ordinateurs']);
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($vendeur)->get(route('ventes.products.show', $product));

        $response->assertStatus(200);
        $response->assertSee('Ordinateurs');
    }

    public function test_show_displays_stock_info(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $product = Product::factory()->create(['quantity' => 25]);

        $response = $this->actingAs($vendeur)->get(route('ventes.products.show', $product));

        $response->assertStatus(200);
        $response->assertSee('25');
    }

    public function test_show_displays_max_discount(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $product = Product::factory()->create(['max_discount' => 15]);

        $response = $this->actingAs($vendeur)->get(route('ventes.products.show', $product));

        $response->assertStatus(200);
        $response->assertSee('15%');
    }

    public function test_show_displays_out_of_stock_alert(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $product = Product::factory()->outOfStock()->create();

        $response = $this->actingAs($vendeur)->get(route('ventes.products.show', $product));

        $response->assertStatus(200);
        $response->assertSee('rupture de stock');
    }

    public function test_show_displays_low_stock_alert(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $product = Product::factory()->create([
            'quantity' => 3,
            'stock_alert' => 5,
        ]);

        $response = $this->actingAs($vendeur)->get(route('ventes.products.show', $product));

        $response->assertStatus(200);
        $response->assertSee('Stock faible');
    }

    public function test_show_returns_404_for_nonexistent_product(): void
    {
        $vendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($vendeur)->get(route('ventes.products.show', 99999));

        $response->assertStatus(404);
    }

    // ==========================================
    // READ-ONLY VERIFICATION
    // ==========================================

    public function test_create_route_does_not_exist_for_vendeur(): void
    {
        $vendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($vendeur)->get('/ventes/products/create');

        $response->assertStatus(404);
    }

    public function test_store_route_is_not_allowed_for_vendeur(): void
    {
        $vendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($vendeur)->post('/ventes/products', [
            'name' => 'Test',
            'sku' => 'TEST-001',
        ]);

        // 405 Method Not Allowed - route exists but method not allowed
        $response->assertStatus(405);
    }

    public function test_edit_route_does_not_exist_for_vendeur(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($vendeur)->get("/ventes/products/{$product->id}/edit");

        $response->assertStatus(404);
    }

    public function test_update_route_is_not_allowed_for_vendeur(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($vendeur)->put("/ventes/products/{$product->id}", [
            'name' => 'Updated',
        ]);

        // 405 Method Not Allowed - route exists but method not allowed
        $response->assertStatus(405);
    }

    public function test_destroy_route_is_not_allowed_for_vendeur(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($vendeur)->delete("/ventes/products/{$product->id}");

        // 405 Method Not Allowed - route exists but method not allowed
        $response->assertStatus(405);
    }

    // ==========================================
    // SECURITY TESTS
    // ==========================================

    public function test_index_search_is_escaped_for_sql_injection(): void
    {
        $vendeur = User::factory()->vendeur()->create();

        $response = $this->actingAs($vendeur)->get(route('ventes.products.index', [
            'search' => "'; DROP TABLE products; --",
        ]));

        $response->assertStatus(200);
    }
}
