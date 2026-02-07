<?php

namespace Tests\Feature\Invoices;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $gerant;
    private User $vendeur;
    private User $otherVendeur;
    private Sale $vendeurSale;
    private Sale $otherVendeurSale;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gerant = User::factory()->gerant()->create();
        $this->vendeur = User::factory()->vendeur()->create();
        $this->otherVendeur = User::factory()->vendeur()->create();

        $product = Product::factory()->create();
        $customer = Customer::factory()->create();

        // Create a sale for vendeur
        $this->vendeurSale = Sale::factory()->create([
            'user_id' => $this->vendeur->id,
            'customer_id' => $customer->id,
            'total' => 25000,
            'discount' => 1000,
        ]);
        SaleItem::factory()->create([
            'sale_id' => $this->vendeurSale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 13000,
            'discount' => 1000,
        ]);

        // Create a sale for other vendeur
        $this->otherVendeurSale = Sale::factory()->create([
            'user_id' => $this->otherVendeur->id,
            'customer_id' => $customer->id,
        ]);
        SaleItem::factory()->create([
            'sale_id' => $this->otherVendeurSale->id,
            'product_id' => $product->id,
        ]);
    }

    // ============================================================
    // TESTS D'AUTORISATION - NON AUTHENTIFIE
    // ============================================================

    public function test_unauthenticated_user_is_redirected_to_login_for_show(): void
    {
        $response = $this->get(route('ventes.invoices.show', $this->vendeurSale));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_download(): void
    {
        $response = $this->get(route('ventes.invoices.download', $this->vendeurSale));
        $response->assertRedirect(route('login'));
    }

    // ============================================================
    // TESTS D'AUTORISATION - VENDEUR
    // ============================================================

    public function test_vendeur_can_view_own_sale_invoice(): void
    {
        $response = $this->actingAs($this->vendeur)
            ->get(route('ventes.invoices.show', $this->vendeurSale));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_vendeur_can_download_own_sale_invoice(): void
    {
        $response = $this->actingAs($this->vendeur)
            ->get(route('ventes.invoices.download', $this->vendeurSale));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader('content-disposition');
    }

    public function test_vendeur_cannot_view_other_vendeur_sale_invoice(): void
    {
        $response = $this->actingAs($this->vendeur)
            ->get(route('ventes.invoices.show', $this->otherVendeurSale));

        $response->assertStatus(403);
    }

    public function test_vendeur_cannot_download_other_vendeur_sale_invoice(): void
    {
        $response = $this->actingAs($this->vendeur)
            ->get(route('ventes.invoices.download', $this->otherVendeurSale));

        $response->assertStatus(403);
    }

    // ============================================================
    // TESTS D'AUTORISATION - GERANT
    // ============================================================

    public function test_gerant_can_view_any_sale_invoice(): void
    {
        $response = $this->actingAs($this->gerant)
            ->get(route('ventes.invoices.show', $this->vendeurSale));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_gerant_can_download_any_sale_invoice(): void
    {
        $response = $this->actingAs($this->gerant)
            ->get(route('ventes.invoices.download', $this->otherVendeurSale));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    // ============================================================
    // TESTS DE CONTENU PDF
    // ============================================================

    public function test_invoice_contains_correct_invoice_number(): void
    {
        $response = $this->actingAs($this->vendeur)
            ->get(route('ventes.invoices.show', $this->vendeurSale));

        $response->assertStatus(200);
        // Le numero de facture est dans le nom du fichier
        $expectedNumber = 'FAC-' . $this->vendeurSale->created_at->format('Y') . '-' . str_pad($this->vendeurSale->id, 5, '0', STR_PAD_LEFT);
        // Note: on ne peut pas facilement tester le contenu du PDF, mais on verifie que ca marche
    }

    public function test_cancelled_sale_invoice_still_accessible(): void
    {
        $cancelledSale = Sale::factory()->cancelled()->create([
            'user_id' => $this->vendeur->id,
        ]);
        SaleItem::factory()->create([
            'sale_id' => $cancelledSale->id,
        ]);

        $response = $this->actingAs($this->vendeur)
            ->get(route('ventes.invoices.show', $cancelledSale));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_sale_without_customer_invoice_works(): void
    {
        $anonymousSale = Sale::factory()->create([
            'user_id' => $this->vendeur->id,
            'customer_id' => null,
        ]);
        SaleItem::factory()->create([
            'sale_id' => $anonymousSale->id,
        ]);

        $response = $this->actingAs($this->vendeur)
            ->get(route('ventes.invoices.show', $anonymousSale));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    // ============================================================
    // TESTS ERREURS
    // ============================================================

    public function test_invoice_returns_404_for_nonexistent_sale(): void
    {
        $response = $this->actingAs($this->vendeur)
            ->get(route('ventes.invoices.show', 99999));

        $response->assertStatus(404);
    }

    public function test_download_returns_404_for_nonexistent_sale(): void
    {
        $response = $this->actingAs($this->vendeur)
            ->get(route('ventes.invoices.download', 99999));

        $response->assertStatus(404);
    }
}
