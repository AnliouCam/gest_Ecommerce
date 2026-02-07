<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display the POS interface for creating a new sale.
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone']);

        return view('vendeur.sales.create', compact('customers'));
    }

    /**
     * Search products for the POS interface (AJAX).
     */
    public function searchProducts(Request $request)
    {
        $search = $request->get('q', '');

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $search = str_replace(['%', '_'], ['\\%', '\\_'], $search);

        $products = Product::with('category:id,name')
            ->where('quantity', '>', 0)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'sku', 'sale_price', 'quantity', 'max_discount', 'category_id', 'image']);

        return response()->json($products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->sale_price,
                'quantity' => $product->quantity,
                'max_discount' => $product->max_discount,
                'category' => $product->category->name ?? 'N/A',
                'image' => $product->image ? asset('storage/' . $product->image) : null,
            ];
        }));
    }

    /**
     * Store a newly created sale.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'payment_method' => ['required', 'in:especes,mobile_money,carte'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ], [
            'payment_method.required' => 'Le mode de paiement est obligatoire.',
            'payment_method.in' => 'Mode de paiement invalide.',
            'items.required' => 'Ajoutez au moins un produit au panier.',
            'items.min' => 'Ajoutez au moins un produit au panier.',
            'items.*.product_id.required' => 'Produit invalide.',
            'items.*.product_id.exists' => 'Produit introuvable.',
            'items.*.quantity.required' => 'La quantite est obligatoire.',
            'items.*.quantity.min' => 'La quantite doit etre au moins 1.',
        ]);

        // Create sale in transaction with lock to prevent race conditions
        try {
            $sale = DB::transaction(function () use ($validated) {
                $errors = [];
                $products = [];
                $total = 0;
                $totalDiscount = 0;

                // Lock products and verify stock/discounts
                foreach ($validated['items'] as $index => $item) {
                    $product = Product::lockForUpdate()->find($item['product_id']);
                    $products[$item['product_id']] = $product;

                    if ($product->quantity < $item['quantity']) {
                        $errors["items.{$index}.quantity"] = "Stock insuffisant pour {$product->name}. Disponible: {$product->quantity}";
                    }

                    $discount = $item['discount'] ?? 0;
                    $maxDiscountAmount = ($product->sale_price * $item['quantity']) * ($product->max_discount / 100);

                    if ($discount > $maxDiscountAmount) {
                        $errors["items.{$index}.discount"] = "Remise trop elevee pour {$product->name}. Maximum: " . number_format($maxDiscountAmount, 0) . " F";
                    }

                    $total += $product->sale_price * $item['quantity'];
                    $totalDiscount += $discount;
                }

                if (!empty($errors)) {
                    throw new \Illuminate\Validation\ValidationException(
                        \Illuminate\Support\Facades\Validator::make([], []),
                        redirect()->back()->withErrors($errors)->withInput()
                    );
                }

                // Create the sale
                $sale = Sale::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $validated['customer_id'] ?? null,
                    'total' => $total - $totalDiscount,
                    'discount' => $totalDiscount,
                    'payment_method' => $validated['payment_method'],
                    'status' => 'completed',
                ]);

                // Create sale items and update stock
                foreach ($validated['items'] as $item) {
                    $product = $products[$item['product_id']];

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $product->sale_price,
                        'discount' => $item['discount'] ?? 0,
                    ]);

                    // Decrement stock
                    $product->decrement('quantity', $item['quantity']);
                }

                return $sale;
            });

            return redirect()->route('ventes.sales.show', $sale)
                ->with('success', 'Vente enregistree avec succes.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        }
    }

    /**
     * Display a listing of sales for the current user (or all for gerant).
     */
    public function index(Request $request)
    {
        $query = Sale::with(['customer:id,name,phone', 'items']);

        // Vendeur sees only their own sales, gerant sees all
        if (Auth::user()->isVendeur()) {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        // Summary stats for current user (or all for gerant)
        $summaryQuery = Sale::query();
        if (Auth::user()->isVendeur()) {
            $summaryQuery->where('user_id', Auth::id());
        }

        $summary = $summaryQuery->selectRaw("
            COUNT(*) as total_sales,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
            SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END) as total_revenue
        ")->first();

        return view('vendeur.sales.index', compact('sales', 'summary'));
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale)
    {
        // Vendeur can only see their own sales
        if (Auth::user()->isVendeur() && $sale->user_id !== Auth::id()) {
            abort(403);
        }

        $sale->load(['user', 'customer', 'items.product', 'cancelledBy']);

        return view('vendeur.sales.show', compact('sale'));
    }

    /**
     * Quick create customer from POS (AJAX).
     */
    public function quickCreateCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50', 'unique:customers,phone'],
        ], [
            'phone.unique' => 'Ce numero de telephone existe deja.',
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
        ]);
    }
}
