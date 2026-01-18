<?php

namespace Tests\Feature\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $gerant;
    private User $vendeur;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gerant = User::factory()->gerant()->create();
        $this->vendeur = User::factory()->vendeur()->create();
        $this->category = Category::factory()->create(['name' => 'Electronique']);

        Storage::fake('public');
    }

    // ============================================================
    // TESTS D'ACCES (Seul le gerant peut acceder aux routes products)
    // ============================================================

    public function test_unauthenticated_user_cannot_access_products_index(): void
    {
        $response = $this->get(route('gerant.products.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_access_products_create(): void
    {
        $response = $this->get(route('gerant.products.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_store_product(): void
    {
        $response = $this->post(route('gerant.products.store'), []);

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_access_product_show(): void
    {
        $product = Product::factory()->for($this->category)->create();

        $response = $this->get(route('gerant.products.show', $product));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_access_product_edit(): void
    {
        $product = Product::factory()->for($this->category)->create();

        $response = $this->get(route('gerant.products.edit', $product));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_update_product(): void
    {
        $product = Product::factory()->for($this->category)->create();

        $response = $this->put(route('gerant.products.update', $product), []);

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_delete_product(): void
    {
        $product = Product::factory()->for($this->category)->create();

        $response = $this->delete(route('gerant.products.destroy', $product));

        $response->assertRedirect(route('login'));
    }

    public function test_vendeur_cannot_access_products_index(): void
    {
        $response = $this->actingAs($this->vendeur)->get(route('gerant.products.index'));

        $response->assertStatus(403);
    }

    public function test_vendeur_cannot_access_products_create(): void
    {
        $response = $this->actingAs($this->vendeur)->get(route('gerant.products.create'));

        $response->assertStatus(403);
    }

    public function test_vendeur_cannot_store_product(): void
    {
        $response = $this->actingAs($this->vendeur)->post(route('gerant.products.store'), [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'category_id' => $this->category->id,
            'purchase_price' => 10000,
            'sale_price' => 15000,
            'quantity' => 10,
            'max_discount' => 10,
            'stock_alert' => 5,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('products', ['sku' => 'TEST-001']);
    }

    public function test_vendeur_cannot_access_product_show(): void
    {
        $product = Product::factory()->for($this->category)->create();

        $response = $this->actingAs($this->vendeur)->get(route('gerant.products.show', $product));

        $response->assertStatus(403);
    }

    public function test_vendeur_cannot_access_product_edit(): void
    {
        $product = Product::factory()->for($this->category)->create();

        $response = $this->actingAs($this->vendeur)->get(route('gerant.products.edit', $product));

        $response->assertStatus(403);
    }

    public function test_vendeur_cannot_update_product(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'name' => 'Original Name',
        ]);

        $response = $this->actingAs($this->vendeur)->put(route('gerant.products.update', $product), [
            'name' => 'Modified Name',
            'sku' => $product->sku,
            'category_id' => $this->category->id,
            'purchase_price' => 10000,
            'sale_price' => 15000,
            'quantity' => 10,
            'max_discount' => 10,
            'stock_alert' => 5,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Original Name']);
    }

    public function test_vendeur_cannot_delete_product(): void
    {
        $product = Product::factory()->for($this->category)->create();

        $response = $this->actingAs($this->vendeur)->delete(route('gerant.products.destroy', $product));

        $response->assertStatus(403);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_gerant_can_access_products_index(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
    }

    public function test_gerant_can_access_products_create(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.products.create'));

        $response->assertStatus(200);
        $response->assertViewIs('products.create');
    }

    // ============================================================
    // TESTS DE LISTE DES PRODUITS (INDEX) AVEC FILTRES
    // ============================================================

    public function test_products_index_displays_all_products(): void
    {
        $products = Product::factory()->count(5)->for($this->category)->create();

        $response = $this->actingAs($this->gerant)->get(route('gerant.products.index'));

        $response->assertStatus(200);
        foreach ($products as $product) {
            $response->assertSee($product->name);
        }
    }

    public function test_products_index_filter_by_search_name(): void
    {
        $targetProduct = Product::factory()->for($this->category)->create([
            'name' => 'Laptop Dell Inspiron',
            'sku' => 'DELL-001',
        ]);
        $otherProduct = Product::factory()->for($this->category)->create([
            'name' => 'Souris Logitech',
            'sku' => 'LOG-001',
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.products.index', [
            'search' => 'Dell',
        ]));

        $response->assertStatus(200);
        $response->assertSee($targetProduct->name);
        $response->assertDontSee($otherProduct->name);
    }

    public function test_products_index_filter_by_search_sku(): void
    {
        $targetProduct = Product::factory()->for($this->category)->create([
            'name' => 'Laptop Dell',
            'sku' => 'UNIQUE-SKU-123',
        ]);
        $otherProduct = Product::factory()->for($this->category)->create([
            'name' => 'Souris Logitech',
            'sku' => 'LOG-001',
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.products.index', [
            'search' => 'UNIQUE-SKU',
        ]));

        $response->assertStatus(200);
        $response->assertSee($targetProduct->name);
        $response->assertDontSee($otherProduct->name);
    }

    public function test_products_index_filter_by_category(): void
    {
        $category1 = Category::factory()->create(['name' => 'Ordinateurs']);
        $category2 = Category::factory()->create(['name' => 'Accessoires']);

        $product1 = Product::factory()->for($category1)->create(['name' => 'PC Asus']);
        $product2 = Product::factory()->for($category2)->create(['name' => 'Clavier USB']);

        $response = $this->actingAs($this->gerant)->get(route('gerant.products.index', [
            'category' => $category1->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee($product1->name);
        $response->assertDontSee($product2->name);
    }

    public function test_products_index_filter_by_low_stock(): void
    {
        $lowStockProduct = Product::factory()->for($this->category)->create([
            'name' => 'Low Stock Product',
            'quantity' => 2,
            'stock_alert' => 5,
        ]);
        $normalStockProduct = Product::factory()->for($this->category)->create([
            'name' => 'Normal Stock Product',
            'quantity' => 50,
            'stock_alert' => 5,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.products.index', [
            'stock' => 'low',
        ]));

        $response->assertStatus(200);
        $response->assertSee($lowStockProduct->name);
        $response->assertDontSee($normalStockProduct->name);
    }

    public function test_products_index_filter_by_out_of_stock(): void
    {
        $outOfStockProduct = Product::factory()->for($this->category)->create([
            'name' => 'Out of Stock Product',
            'quantity' => 0,
        ]);
        $inStockProduct = Product::factory()->for($this->category)->create([
            'name' => 'In Stock Product',
            'quantity' => 10,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.products.index', [
            'stock' => 'out',
        ]));

        $response->assertStatus(200);
        $response->assertSee($outOfStockProduct->name);
        $response->assertDontSee($inStockProduct->name);
    }

    public function test_products_index_combined_filters(): void
    {
        $category1 = Category::factory()->create(['name' => 'Telephones']);

        $matchingProduct = Product::factory()->for($category1)->create([
            'name' => 'iPhone 15 Pro',
            'quantity' => 2,
            'stock_alert' => 5,
        ]);
        $wrongCategory = Product::factory()->for($this->category)->create([
            'name' => 'iPhone 14',
            'quantity' => 2,
            'stock_alert' => 5,
        ]);
        $wrongStock = Product::factory()->for($category1)->create([
            'name' => 'Samsung Galaxy',
            'quantity' => 50,
            'stock_alert' => 5,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.products.index', [
            'search' => 'iPhone',
            'category' => $category1->id,
            'stock' => 'low',
        ]));

        $response->assertStatus(200);
        $response->assertSee($matchingProduct->name);
        $response->assertDontSee($wrongCategory->name);
        $response->assertDontSee($wrongStock->name);
    }

    public function test_products_index_search_escapes_special_characters(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'name' => 'Test Product 100%',
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.products.index', [
            'search' => '100%',
        ]));

        $response->assertStatus(200);
    }

    // ============================================================
    // TESTS DE CREATION DE PRODUIT
    // ============================================================

    public function test_gerant_can_create_product_with_valid_data(): void
    {
        $productData = [
            'name' => 'Nouveau Laptop HP',
            'sku' => 'HP-LAPTOP-001',
            'category_id' => $this->category->id,
            'purchase_price' => 500000,
            'sale_price' => 650000,
            'quantity' => 10,
            'max_discount' => 15,
            'stock_alert' => 3,
        ];

        $response = $this->actingAs($this->gerant)->post(route('gerant.products.store'), $productData);

        $response->assertRedirect(route('gerant.products.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('products', [
            'name' => 'Nouveau Laptop HP',
            'sku' => 'HP-LAPTOP-001',
            'category_id' => $this->category->id,
        ]);
    }

    public function test_gerant_can_create_product_with_image(): void
    {
        $productData = [
            'name' => 'Product With Image',
            'sku' => 'IMG-001',
            'category_id' => $this->category->id,
            'purchase_price' => 100000,
            'sale_price' => 150000,
            'quantity' => 5,
            'max_discount' => 10,
            'stock_alert' => 2,
            'image' => UploadedFile::fake()->image('product.jpg', 640, 480),
        ];

        $response = $this->actingAs($this->gerant)->post(route('gerant.products.store'), $productData);

        $response->assertRedirect(route('gerant.products.index'));
        $this->assertDatabaseHas('products', ['sku' => 'IMG-001']);

        $product = Product::where('sku', 'IMG-001')->first();
        $this->assertNotNull($product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_product_creation_fails_without_name(): void
    {
        $productData = [
            'sku' => 'TEST-001',
            'category_id' => $this->category->id,
            'purchase_price' => 100000,
            'sale_price' => 150000,
            'quantity' => 5,
            'max_discount' => 10,
            'stock_alert' => 2,
        ];

        $response = $this->actingAs($this->gerant)->post(route('gerant.products.store'), $productData);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('products', ['sku' => 'TEST-001']);
    }

    public function test_product_creation_fails_without_sku(): void
    {
        $productData = [
            'name' => 'Test Product',
            'category_id' => $this->category->id,
            'purchase_price' => 100000,
            'sale_price' => 150000,
            'quantity' => 5,
            'max_discount' => 10,
            'stock_alert' => 2,
        ];

        $response = $this->actingAs($this->gerant)->post(route('gerant.products.store'), $productData);

        $response->assertSessionHasErrors('sku');
    }

    public function test_product_creation_fails_with_duplicate_sku(): void
    {
        Product::factory()->for($this->category)->create(['sku' => 'EXISTING-SKU']);

        $productData = [
            'name' => 'New Product',
            'sku' => 'EXISTING-SKU',
            'category_id' => $this->category->id,
            'purchase_price' => 100000,
            'sale_price' => 150000,
            'quantity' => 5,
            'max_discount' => 10,
            'stock_alert' => 2,
        ];

        $response = $this->actingAs($this->gerant)->post(route('gerant.products.store'), $productData);

        $response->assertSessionHasErrors('sku');
    }

    public function test_product_creation_fails_with_invalid_category(): void
    {
        $productData = [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'category_id' => 99999,
            'purchase_price' => 100000,
            'sale_price' => 150000,
            'quantity' => 5,
            'max_discount' => 10,
            'stock_alert' => 2,
        ];

        $response = $this->actingAs($this->gerant)->post(route('gerant.products.store'), $productData);

        $response->assertSessionHasErrors('category_id');
    }

    public function test_product_creation_fails_when_sale_price_less_than_purchase_price(): void
    {
        $productData = [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'category_id' => $this->category->id,
            'purchase_price' => 150000,
            'sale_price' => 100000,
            'quantity' => 5,
            'max_discount' => 10,
            'stock_alert' => 2,
        ];

        $response = $this->actingAs($this->gerant)->post(route('gerant.products.store'), $productData);

        $response->assertSessionHasErrors('sale_price');
    }

    public function test_product_creation_fails_with_negative_quantity(): void
    {
        $productData = [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'category_id' => $this->category->id,
            'purchase_price' => 100000,
            'sale_price' => 150000,
            'quantity' => -5,
            'max_discount' => 10,
            'stock_alert' => 2,
        ];

        $response = $this->actingAs($this->gerant)->post(route('gerant.products.store'), $productData);

        $response->assertSessionHasErrors('quantity');
    }

    public function test_product_creation_fails_with_discount_over_20_percent(): void
    {
        $productData = [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'category_id' => $this->category->id,
            'purchase_price' => 100000,
            'sale_price' => 150000,
            'quantity' => 5,
            'max_discount' => 25,
            'stock_alert' => 2,
        ];

        $response = $this->actingAs($this->gerant)->post(route('gerant.products.store'), $productData);

        $response->assertSessionHasErrors('max_discount');
    }

    public function test_product_creation_fails_with_invalid_image_type(): void
    {
        $productData = [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'category_id' => $this->category->id,
            'purchase_price' => 100000,
            'sale_price' => 150000,
            'quantity' => 5,
            'max_discount' => 10,
            'stock_alert' => 2,
            'image' => UploadedFile::fake()->create('document.pdf', 1000),
        ];

        $response = $this->actingAs($this->gerant)->post(route('gerant.products.store'), $productData);

        $response->assertSessionHasErrors('image');
    }

    // ============================================================
    // TESTS D'EDITION DE PRODUIT
    // ============================================================

    public function test_gerant_can_access_edit_page(): void
    {
        $product = Product::factory()->for($this->category)->create();

        $response = $this->actingAs($this->gerant)->get(route('gerant.products.edit', $product));

        $response->assertStatus(200);
        $response->assertViewIs('products.edit');
        $response->assertViewHas('product', $product);
    }

    public function test_gerant_can_update_product_with_valid_data(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'name' => 'Old Name',
            'sale_price' => 150000,
        ]);

        $response = $this->actingAs($this->gerant)->put(route('gerant.products.update', $product), [
            'name' => 'Updated Name',
            'sku' => $product->sku,
            'category_id' => $this->category->id,
            'purchase_price' => 100000,
            'sale_price' => 180000,
            'quantity' => 20,
            'max_discount' => 15,
            'stock_alert' => 5,
        ]);

        $response->assertRedirect(route('gerant.products.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'sale_price' => 180000,
        ]);
    }

    public function test_gerant_can_update_product_image(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'image' => 'products/old-image.jpg',
        ]);

        Storage::disk('public')->put('products/old-image.jpg', 'old content');

        $response = $this->actingAs($this->gerant)->put(route('gerant.products.update', $product), [
            'name' => $product->name,
            'sku' => $product->sku,
            'category_id' => $this->category->id,
            'purchase_price' => $product->purchase_price,
            'sale_price' => $product->sale_price,
            'quantity' => $product->quantity,
            'max_discount' => $product->max_discount,
            'stock_alert' => $product->stock_alert,
            'image' => UploadedFile::fake()->image('new-image.jpg'),
        ]);

        $response->assertRedirect(route('gerant.products.index'));
        Storage::disk('public')->assertMissing('products/old-image.jpg');

        $product->refresh();
        $this->assertNotNull($product->image);
        $this->assertNotEquals('products/old-image.jpg', $product->image);
    }

    public function test_product_update_allows_same_sku(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'sku' => 'MY-SKU',
        ]);

        $response = $this->actingAs($this->gerant)->put(route('gerant.products.update', $product), [
            'name' => 'Updated Name',
            'sku' => 'MY-SKU',
            'category_id' => $this->category->id,
            'purchase_price' => 100000,
            'sale_price' => 150000,
            'quantity' => 10,
            'max_discount' => 10,
            'stock_alert' => 5,
        ]);

        $response->assertRedirect(route('gerant.products.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_product_update_fails_with_another_products_sku(): void
    {
        $product1 = Product::factory()->for($this->category)->create(['sku' => 'SKU-001']);
        $product2 = Product::factory()->for($this->category)->create(['sku' => 'SKU-002']);

        $response = $this->actingAs($this->gerant)->put(route('gerant.products.update', $product1), [
            'name' => 'Updated Name',
            'sku' => 'SKU-002',
            'category_id' => $this->category->id,
            'purchase_price' => 100000,
            'sale_price' => 150000,
            'quantity' => 10,
            'max_discount' => 10,
            'stock_alert' => 5,
        ]);

        $response->assertSessionHasErrors('sku');
    }

    public function test_product_update_validation_rules_apply(): void
    {
        $product = Product::factory()->for($this->category)->create();

        $response = $this->actingAs($this->gerant)->put(route('gerant.products.update', $product), [
            'name' => '',
            'sku' => $product->sku,
            'category_id' => $this->category->id,
            'purchase_price' => 100000,
            'sale_price' => 50000,
            'quantity' => -1,
            'max_discount' => 30,
            'stock_alert' => -1,
        ]);

        $response->assertSessionHasErrors(['name', 'sale_price', 'quantity', 'max_discount', 'stock_alert']);
    }

    // ============================================================
    // TESTS DE SUPPRESSION DE PRODUIT
    // ============================================================

    public function test_gerant_can_delete_product_without_sales(): void
    {
        $product = Product::factory()->for($this->category)->create();

        $response = $this->actingAs($this->gerant)->delete(route('gerant.products.destroy', $product));

        $response->assertRedirect(route('gerant.products.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_gerant_cannot_delete_product_with_associated_sales(): void
    {
        $product = Product::factory()->for($this->category)->create();

        $sale = Sale::factory()->create();
        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($this->gerant)->delete(route('gerant.products.destroy', $product));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_deleting_product_removes_associated_image(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'image' => 'products/test-image.jpg',
        ]);

        Storage::disk('public')->put('products/test-image.jpg', 'test content');
        Storage::disk('public')->assertExists('products/test-image.jpg');

        $response = $this->actingAs($this->gerant)->delete(route('gerant.products.destroy', $product));

        $response->assertRedirect(route('gerant.products.index'));
        Storage::disk('public')->assertMissing('products/test-image.jpg');
    }

    // ============================================================
    // TESTS DE LA VUE DETAIL (SHOW)
    // ============================================================

    public function test_gerant_can_view_product_details(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'name' => 'Test Product Details',
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.products.show', $product));

        $response->assertStatus(200);
        $response->assertViewIs('products.show');
        $response->assertSee($product->name);
    }

    public function test_product_show_displays_category(): void
    {
        $category = Category::factory()->create(['name' => 'Special Category']);
        $product = Product::factory()->for($category)->create();

        $response = $this->actingAs($this->gerant)->get(route('gerant.products.show', $product));

        $response->assertStatus(200);
        $response->assertSee('Special Category');
    }

    public function test_product_show_displays_statistics(): void
    {
        $product = Product::factory()->for($this->category)->create();

        $sale = Sale::factory()->create();
        SaleItem::factory()->count(3)->create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->gerant)->get(route('gerant.products.show', $product));

        $response->assertStatus(200);
        $response->assertViewHas('stats');
    }

    public function test_product_show_returns_404_for_nonexistent_product(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.products.show', 99999));

        $response->assertStatus(404);
    }

    // ============================================================
    // TESTS SUPPLEMENTAIRES
    // ============================================================

    public function test_products_are_paginated(): void
    {
        Product::factory()->count(20)->for($this->category)->create();

        $response = $this->actingAs($this->gerant)->get(route('gerant.products.index'));

        $response->assertStatus(200);
        $response->assertViewHas('products');
    }

    public function test_categories_are_passed_to_index_view(): void
    {
        Category::factory()->count(5)->create();

        $response = $this->actingAs($this->gerant)->get(route('gerant.products.index'));

        $response->assertStatus(200);
        $response->assertViewHas('categories');
    }

    public function test_categories_are_passed_to_create_view(): void
    {
        Category::factory()->count(5)->create();

        $response = $this->actingAs($this->gerant)->get(route('gerant.products.create'));

        $response->assertStatus(200);
        $response->assertViewHas('categories');
    }

    public function test_categories_are_passed_to_edit_view(): void
    {
        $product = Product::factory()->for($this->category)->create();
        Category::factory()->count(5)->create();

        $response = $this->actingAs($this->gerant)->get(route('gerant.products.edit', $product));

        $response->assertStatus(200);
        $response->assertViewHas('categories');
    }
}
