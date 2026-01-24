<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display a listing of sales.
     */
    public function index(Request $request)
    {
        $query = Sale::with(['user:id,name', 'customer:id,name,phone', 'items']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $sales = $query->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $users = User::orderBy('name')->get(['id', 'name']);

        // Summary stats
        $summaryQuery = Sale::query();
        if ($request->filled('date_from')) {
            $summaryQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $summaryQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $summary = $summaryQuery->selectRaw("
            COUNT(*) as total_sales,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
            SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END) as total_revenue,
            SUM(CASE WHEN status = 'completed' THEN discount ELSE 0 END) as total_discounts
        ")->first();

        return view('sales.index', compact('sales', 'users', 'summary'));
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale)
    {
        $sale->load(['user', 'customer', 'items.product', 'cancelledBy']);

        return view('sales.show', compact('sale'));
    }

    /**
     * Show the cancellation form.
     */
    public function cancelForm(Sale $sale)
    {
        if ($sale->isCancelled()) {
            return redirect()->route('gerant.sales.show', $sale)
                ->with('error', 'Cette vente est deja annulee.');
        }

        $sale->load(['user', 'customer', 'items.product']);

        return view('sales.cancel', compact('sale'));
    }

    /**
     * Cancel a sale with reason and stock restoration.
     */
    public function cancel(Request $request, Sale $sale)
    {
        if ($sale->isCancelled()) {
            return redirect()->route('gerant.sales.show', $sale)
                ->with('error', 'Cette vente est deja annulee.');
        }

        $validated = $request->validate([
            'cancel_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'cancel_reason.required' => 'Le motif d\'annulation est obligatoire.',
            'cancel_reason.min' => 'Le motif doit contenir au moins 10 caracteres.',
            'cancel_reason.max' => 'Le motif ne peut pas depasser 1000 caracteres.',
        ]);

        DB::transaction(function () use ($sale, $validated) {
            // Restore stock for each item
            foreach ($sale->items as $item) {
                if ($item->product) {
                    $item->product->increment('quantity', $item->quantity);
                }
            }

            // Update sale status
            $sale->update([
                'status' => 'cancelled',
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now(),
                'cancel_reason' => $validated['cancel_reason'],
            ]);
        });

        return redirect()->route('gerant.sales.show', $sale)
            ->with('success', 'Vente annulee avec succes. Le stock a ete restaure.');
    }

    /**
     * Display cancelled sales history (for reports).
     */
    public function cancelledHistory(Request $request)
    {
        $query = Sale::with(['user:id,name', 'customer:id,name', 'cancelledBy:id,name'])
            ->where('status', 'cancelled');

        if ($request->filled('cancelled_by')) {
            $query->where('cancelled_by', $request->cancelled_by);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('cancelled_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('cancelled_at', '<=', $request->date_to);
        }

        $sales = $query->orderByDesc('cancelled_at')
            ->paginate(15)
            ->withQueryString();

        $users = User::where('role', 'gerant')->orderBy('name')->get(['id', 'name']);

        // Summary
        $summary = Sale::where('status', 'cancelled')
            ->selectRaw('
                COUNT(*) as total_cancelled,
                SUM(total) as total_value,
                COUNT(DISTINCT cancelled_by) as unique_cancellers
            ')->first();

        return view('sales.cancelled-history', compact('sales', 'users', 'summary'));
    }
}
