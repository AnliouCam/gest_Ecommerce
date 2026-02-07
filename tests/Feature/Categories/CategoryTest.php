<?php

namespace Tests\Feature\Categories;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
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
        $response = $this->get(route('gerant.categories.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_create(): void
    {
        $response = $this->get(route('gerant.categories.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_store(): void
    {
        $response = $this->post(route('gerant.categories.store'), []);

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_edit(): void
    {
        $category = Category::factory()->create();

        $response = $this->get(route('gerant.categories.edit', $category));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_update(): void
    {
        $category = Category::factory()->create();

        $response = $this->put(route('gerant.categories.update', $category), []);

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_destroy(): void
    {
        $category = Category::factory()->create();

        $response = $this->delete(route('gerant.categories.destroy', $category));

        $response->assertRedirect(route('login'));
    }

    public function test_vendeur_receives_403_for_index(): void
    {
        $response = $this->actingAs($this->vendeur)->get(route('gerant.categories.index'));

        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_create(): void
    {
        $response = $this->actingAs($this->vendeur)->get(route('gerant.categories.create'));

        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_store(): void
    {
        $response = $this->actingAs($this->vendeur)->post(route('gerant.categories.store'), [
            'name' => 'Test Category',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('categories', ['name' => 'Test Category']);
    }

    public function test_vendeur_receives_403_for_edit(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->vendeur)->get(route('gerant.categories.edit', $category));

        $response->assertStatus(403);
    }

    public function test_vendeur_receives_403_for_update(): void
    {
        $category = Category::factory()->create(['name' => 'Original Name']);

        $response = $this->actingAs($this->vendeur)->put(route('gerant.categories.update', $category), [
            'name' => 'Modified Name',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Original Name']);
    }

    public function test_vendeur_receives_403_for_destroy(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->vendeur)->delete(route('gerant.categories.destroy', $category));

        $response->assertStatus(403);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_gerant_can_access_categories_index(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.categories.index'));

        $response->assertStatus(200);
        $response->assertViewIs('categories.index');
    }

    public function test_gerant_can_access_categories_create(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.categories.create'));

        $response->assertStatus(200);
        $response->assertViewIs('categories.create');
    }

    // ============================================================
    // TESTS INDEX
    // ============================================================

    public function test_index_displays_list_of_categories(): void
    {
        $categories = Category::factory()->count(5)->create();

        $response = $this->actingAs($this->gerant)->get(route('gerant.categories.index'));

        $response->assertStatus(200);
        foreach ($categories as $category) {
            $response->assertSee($category->name);
        }
    }

    public function test_index_search_by_name_works(): void
    {
        $targetCategory = Category::factory()->create(['name' => 'Electronique']);
        $otherCategory = Category::factory()->create(['name' => 'Vetements']);

        $response = $this->actingAs($this->gerant)->get(route('gerant.categories.index', [
            'search' => 'Electro',
        ]));

        $response->assertStatus(200);
        $response->assertSee($targetCategory->name);
        $response->assertDontSee($otherCategory->name);
    }

    public function test_index_search_escapes_special_characters(): void
    {
        $category = Category::factory()->create(['name' => 'Test 100% Category']);

        $response = $this->actingAs($this->gerant)->get(route('gerant.categories.index', [
            'search' => '100%',
        ]));

        $response->assertStatus(200);
    }

    public function test_index_displays_product_count_per_category(): void
    {
        $category = Category::factory()->create(['name' => 'Telephones']);
        Product::factory()->count(3)->for($category)->create();

        $response = $this->actingAs($this->gerant)->get(route('gerant.categories.index'));

        $response->assertStatus(200);
        $response->assertViewHas('categories', function ($categories) use ($category) {
            $cat = $categories->firstWhere('id', $category->id);
            return $cat && $cat->products_count === 3;
        });
    }

    public function test_index_displays_zero_products_for_empty_category(): void
    {
        $category = Category::factory()->create(['name' => 'Empty Category']);

        $response = $this->actingAs($this->gerant)->get(route('gerant.categories.index'));

        $response->assertStatus(200);
        $response->assertViewHas('categories', function ($categories) use ($category) {
            $cat = $categories->firstWhere('id', $category->id);
            return $cat && $cat->products_count === 0;
        });
    }

    // ============================================================
    // TESTS CREATE / STORE
    // ============================================================

    public function test_create_displays_form(): void
    {
        $response = $this->actingAs($this->gerant)->get(route('gerant.categories.create'));

        $response->assertStatus(200);
        $response->assertViewIs('categories.create');
    }

    public function test_store_can_create_category_with_valid_data(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.categories.store'), [
            'name' => 'Nouvelle Categorie',
        ]);

        $response->assertRedirect(route('gerant.categories.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('categories', ['name' => 'Nouvelle Categorie']);
    }

    public function test_store_validation_fails_when_name_is_missing(): void
    {
        $response = $this->actingAs($this->gerant)->post(route('gerant.categories.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_store_validation_fails_when_name_is_not_unique(): void
    {
        Category::factory()->create(['name' => 'Existing Category']);

        $response = $this->actingAs($this->gerant)->post(route('gerant.categories.store'), [
            'name' => 'Existing Category',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_store_validation_fails_when_name_exceeds_max_length(): void
    {
        $longName = str_repeat('a', 256);

        $response = $this->actingAs($this->gerant)->post(route('gerant.categories.store'), [
            'name' => $longName,
        ]);

        $response->assertSessionHasErrors('name');
    }

    // ============================================================
    // TESTS EDIT / UPDATE
    // ============================================================

    public function test_edit_displays_form_with_existing_data(): void
    {
        $category = Category::factory()->create(['name' => 'Test Category']);

        $response = $this->actingAs($this->gerant)->get(route('gerant.categories.edit', $category));

        $response->assertStatus(200);
        $response->assertViewIs('categories.edit');
        $response->assertViewHas('category', $category);
        $response->assertSee('Test Category');
    }

    public function test_update_can_modify_category_with_valid_data(): void
    {
        $category = Category::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->gerant)->put(route('gerant.categories.update', $category), [
            'name' => 'New Name',
        ]);

        $response->assertRedirect(route('gerant.categories.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'New Name']);
    }

    public function test_update_allows_keeping_same_name(): void
    {
        $category = Category::factory()->create(['name' => 'Same Name']);

        $response = $this->actingAs($this->gerant)->put(route('gerant.categories.update', $category), [
            'name' => 'Same Name',
        ]);

        $response->assertRedirect(route('gerant.categories.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Same Name']);
    }

    public function test_update_validation_fails_when_name_is_duplicate_of_another_category(): void
    {
        $category1 = Category::factory()->create(['name' => 'Category One']);
        $category2 = Category::factory()->create(['name' => 'Category Two']);

        $response = $this->actingAs($this->gerant)->put(route('gerant.categories.update', $category1), [
            'name' => 'Category Two',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseHas('categories', ['id' => $category1->id, 'name' => 'Category One']);
    }

    public function test_update_validation_fails_when_name_is_missing(): void
    {
        $category = Category::factory()->create(['name' => 'Original Name']);

        $response = $this->actingAs($this->gerant)->put(route('gerant.categories.update', $category), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Original Name']);
    }

    public function test_update_returns_404_for_nonexistent_category(): void
    {
        $response = $this->actingAs($this->gerant)->put(route('gerant.categories.update', 99999), [
            'name' => 'Some Name',
        ]);

        $response->assertStatus(404);
    }

    // ============================================================
    // TESTS DESTROY
    // ============================================================

    public function test_destroy_can_delete_category_without_products(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->gerant)->delete(route('gerant.categories.destroy', $category));

        $response->assertRedirect(route('gerant.categories.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_destroy_cannot_delete_category_with_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->create();

        $response = $this->actingAs($this->gerant)->delete(route('gerant.categories.destroy', $category));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_destroy_cannot_delete_category_with_multiple_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(5)->for($category)->create();

        $response = $this->actingAs($this->gerant)->delete(route('gerant.categories.destroy', $category));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $this->assertDatabaseCount('products', 5);
    }

    public function test_destroy_returns_404_for_nonexistent_category(): void
    {
        $response = $this->actingAs($this->gerant)->delete(route('gerant.categories.destroy', 99999));

        $response->assertStatus(404);
    }

    // ============================================================
    // TESTS SUPPLEMENTAIRES
    // ============================================================

    public function test_categories_are_ordered_by_name(): void
    {
        Category::factory()->create(['name' => 'Zebra']);
        Category::factory()->create(['name' => 'Alpha']);
        Category::factory()->create(['name' => 'Middle']);

        $response = $this->actingAs($this->gerant)->get(route('gerant.categories.index'));

        $response->assertStatus(200);
        $response->assertViewHas('categories', function ($categories) {
            $names = $categories->pluck('name')->toArray();
            return $names[0] === 'Alpha' && $names[1] === 'Middle' && $names[2] === 'Zebra';
        });
    }

    public function test_categories_are_paginated(): void
    {
        // Create categories directly without factory (factory is limited to 12 predefined names)
        for ($i = 1; $i <= 20; $i++) {
            Category::create(['name' => "Test Category {$i}"]);
        }

        $response = $this->actingAs($this->gerant)->get(route('gerant.categories.index'));

        $response->assertStatus(200);
        $response->assertViewHas('categories');
    }
}
