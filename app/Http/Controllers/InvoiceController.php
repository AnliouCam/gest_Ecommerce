<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * Generate invoice number from sale ID.
     * Format: FAC-YYYY-MM-XXXXX (e.g., FAC-2026-01-00001)
     */
    private function generateInvoiceNumber(Sale $sale): string
    {
        $yearMonth = $sale->created_at->format('Y-m');
        $number = str_pad($sale->id, 5, '0', STR_PAD_LEFT);

        return "FAC-{$yearMonth}-{$number}";
    }

    /**
     * Get authorized sale with eager loading.
     * Throws 403 if user doesn't have access.
     */
    private function getAuthorizedSale(Sale $sale): Sale
    {
        $user = Auth::user();

        // Check authorization
        $canAccess = $user->isGerant() || $sale->user_id === $user->id;

        if (!$canAccess) {
            abort(403);
        }

        // Eager load relations
        $sale->load(['user', 'customer', 'items.product', 'cancelledBy']);

        return $sale;
    }

    /**
     * Generate the PDF for a sale.
     */
    private function generatePdf(Sale $sale)
    {
        $invoiceNumber = $this->generateInvoiceNumber($sale);

        return [
            'pdf' => Pdf::loadView('invoices.template', [
                'sale' => $sale,
                'invoiceNumber' => $invoiceNumber,
            ]),
            'filename' => "facture-{$invoiceNumber}.pdf",
        ];
    }

    /**
     * Display the invoice PDF in browser.
     */
    public function show(Sale $sale)
    {
        $sale = $this->getAuthorizedSale($sale);
        $result = $this->generatePdf($sale);

        return $result['pdf']->stream($result['filename']);
    }

    /**
     * Download the invoice PDF.
     */
    public function download(Sale $sale)
    {
        $sale = $this->getAuthorizedSale($sale);
        $result = $this->generatePdf($sale);

        return $result['pdf']->download($result['filename']);
    }
}
