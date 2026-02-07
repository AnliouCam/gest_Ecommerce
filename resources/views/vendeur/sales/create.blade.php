@extends('layouts.app')

@section('title', 'Nouvelle Vente')

@section('content')
<div x-data="posApp()" class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Nouvelle Vente</h1>
            <p class="mt-1 text-sm text-gray-500">Interface de vente au comptoir</p>
        </div>
        <a href="{{ route('ventes.sales.index') }}" class="text-gray-500 hover:text-primary-600 transition-colors">
            Voir mes ventes
        </a>
    </div>

    <form action="{{ route('ventes.sales.store') }}" method="POST" @submit="handleSubmit">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Product Search -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Search Box -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Rechercher un produit</h2>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" x-model="searchQuery" @input.debounce.300ms="searchProducts"
                            placeholder="Rechercher par nom ou SKU..."
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-lg">
                    </div>

                    <!-- Search Results -->
                    <div x-show="searchResults.length > 0" x-cloak class="mt-4 border border-gray-200 rounded-lg divide-y divide-gray-200 max-h-80 overflow-y-auto">
                        <template x-for="product in searchResults" :key="product.id">
                            <div class="p-3 hover:bg-gray-50 cursor-pointer flex items-center justify-between" @click="addToCart(product)">
                                <div class="flex items-center">
                                    <template x-if="product.image">
                                        <img :src="product.image" class="w-12 h-12 rounded-lg object-cover mr-3">
                                    </template>
                                    <template x-if="!product.image">
                                        <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center mr-3">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>
                                    </template>
                                    <div>
                                        <p class="font-medium text-gray-900" x-text="product.name"></p>
                                        <p class="text-sm text-gray-500">
                                            <span x-text="product.sku"></span> -
                                            <span class="text-primary-600 font-medium" x-text="formatPrice(product.price)"></span>
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="product.quantity > 5 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'">
                                        Stock: <span x-text="product.quantity"></span>
                                    </span>
                                    <button type="button" class="ml-2 p-1 text-primary-600 hover:text-primary-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="searchQuery.length >= 2 && searchResults.length === 0 && !searching" x-cloak class="mt-4 text-center py-4 text-gray-500">
                        Aucun produit trouve.
                    </div>

                    <div x-show="searching" x-cloak class="mt-4 text-center py-4 text-gray-500">
                        <svg class="animate-spin h-5 w-5 mx-auto text-primary-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Customer & Payment -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Client et Paiement</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Customer -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Client (optionnel)</label>
                            <div class="flex gap-2">
                                <select name="customer_id" x-model="customerId"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="">Vente anonyme</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                                    @endforeach
                                </select>
                                <button type="button" @click="showNewCustomerModal = true"
                                    class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors" title="Nouveau client">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mode de paiement *</label>
                            <select name="payment_method" x-model="paymentMethod" required
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('payment_method') border-red-500 @enderror">
                                <option value="">Selectionner...</option>
                                <option value="especes">Especes</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="carte">Carte bancaire</option>
                            </select>
                            @error('payment_method')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Cart -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        Panier
                        <span class="text-sm font-normal text-gray-500" x-show="cart.length > 0">
                            (<span x-text="cart.length"></span> article<span x-show="cart.length > 1">s</span>)
                        </span>
                    </h2>

                    <!-- Empty Cart -->
                    <div x-show="cart.length === 0" class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="mt-2">Panier vide</p>
                        <p class="text-sm">Recherchez et ajoutez des produits</p>
                    </div>

                    <!-- Cart Items -->
                    <div x-show="cart.length > 0" class="space-y-3 max-h-96 overflow-y-auto">
                        <template x-for="(item, index) in cart" :key="item.id">
                            <div class="border border-gray-200 rounded-lg p-3">
                                <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.id">
                                <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.qty">
                                <input type="hidden" :name="'items[' + index + '][discount]'" :value="item.discount">

                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 text-sm" x-text="item.name"></p>
                                        <p class="text-xs text-gray-500" x-text="formatPrice(item.price) + ' x ' + item.qty"></p>
                                    </div>
                                    <button type="button" @click="removeFromCart(index)" class="text-red-500 hover:text-red-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="mt-2 flex items-center gap-2">
                                    <button type="button" @click="updateQty(index, -1)"
                                        class="w-7 h-7 rounded bg-gray-100 hover:bg-gray-200 flex items-center justify-center"
                                        :disabled="item.qty <= 1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    <span class="w-8 text-center font-medium" x-text="item.qty"></span>
                                    <button type="button" @click="updateQty(index, 1)"
                                        class="w-7 h-7 rounded bg-gray-100 hover:bg-gray-200 flex items-center justify-center"
                                        :disabled="item.qty >= item.stock">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </button>
                                    <span class="text-xs text-gray-400 ml-2">max: <span x-text="item.stock"></span></span>
                                </div>

                                <!-- Discount -->
                                <div class="mt-2">
                                    <div class="flex items-center gap-2">
                                        <input type="number" x-model.number="item.discount" min="0" :max="getMaxDiscount(item)"
                                            @input="validateDiscount(index)"
                                            class="w-24 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-primary-500"
                                            placeholder="Remise">
                                        <span class="text-xs text-gray-500">F (max <span x-text="item.maxDiscountPercent"></span>%)</span>
                                    </div>
                                </div>

                                <div class="mt-2 text-right">
                                    <span class="font-medium text-primary-600" x-text="formatPrice(getItemTotal(item))"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Totals -->
                    <div x-show="cart.length > 0" class="mt-4 pt-4 border-t border-gray-200 space-y-2">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Sous-total:</span>
                            <span x-text="formatPrice(getSubtotal())"></span>
                        </div>
                        <div class="flex justify-between text-sm text-red-600">
                            <span>Remises:</span>
                            <span x-text="'-' + formatPrice(getTotalDiscount())"></span>
                        </div>
                        <div class="flex justify-between text-xl font-bold text-gray-900 pt-2 border-t border-gray-200">
                            <span>Total:</span>
                            <span class="text-primary-600" x-text="formatPrice(getTotal())"></span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6">
                        <button type="submit" :disabled="cart.length === 0 || !paymentMethod || submitting"
                            class="w-full py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center">
                            <svg x-show="submitting" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-text="submitting ? 'Enregistrement...' : 'Valider la vente'"></span>
                        </button>
                    </div>

                    @if($errors->any())
                        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <ul class="text-sm text-red-600 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </form>

    <!-- New Customer Modal -->
    <div x-show="showNewCustomerModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="showNewCustomerModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Nouveau client</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input type="text" x-model="newCustomer.name"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telephone *</label>
                        <input type="text" x-model="newCustomer.phone"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showNewCustomerModal = false"
                        class="px-4 py-2 text-gray-700 hover:text-gray-900">Annuler</button>
                    <button type="button" @click="createCustomer" :disabled="!newCustomer.name || !newCustomer.phone"
                        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50">
                        Creer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function posApp() {
    return {
        searchQuery: '',
        searchResults: [],
        searching: false,
        cart: [],
        customerId: '',
        paymentMethod: '',
        submitting: false,
        showNewCustomerModal: false,
        newCustomer: { name: '', phone: '' },

        async searchProducts() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }

            this.searching = true;
            try {
                const response = await fetch(`{{ route('ventes.sales.search-products') }}?q=${encodeURIComponent(this.searchQuery)}`);
                this.searchResults = await response.json();
            } catch (error) {
                console.error('Search error:', error);
                this.searchResults = [];
            }
            this.searching = false;
        },

        addToCart(product) {
            const existing = this.cart.find(item => item.id === product.id);
            if (existing) {
                if (existing.qty < existing.stock) {
                    existing.qty++;
                }
            } else {
                this.cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    qty: 1,
                    stock: product.quantity,
                    maxDiscountPercent: product.max_discount,
                    discount: 0
                });
            }
            this.searchQuery = '';
            this.searchResults = [];
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        updateQty(index, delta) {
            const item = this.cart[index];
            const newQty = item.qty + delta;
            if (newQty >= 1 && newQty <= item.stock) {
                item.qty = newQty;
                this.validateDiscount(index);
            }
        },

        getMaxDiscount(item) {
            return Math.floor(item.price * item.qty * item.maxDiscountPercent / 100);
        },

        validateDiscount(index) {
            const item = this.cart[index];
            const max = this.getMaxDiscount(item);
            if (item.discount > max) {
                item.discount = max;
            }
            if (item.discount < 0) {
                item.discount = 0;
            }
        },

        getItemTotal(item) {
            return (item.price * item.qty) - (item.discount || 0);
        },

        getSubtotal() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        },

        getTotalDiscount() {
            return this.cart.reduce((sum, item) => sum + (item.discount || 0), 0);
        },

        getTotal() {
            return this.getSubtotal() - this.getTotalDiscount();
        },

        formatPrice(amount) {
            return new Intl.NumberFormat('fr-FR').format(amount) + ' F';
        },

        handleSubmit(e) {
            if (this.cart.length === 0 || !this.paymentMethod) {
                e.preventDefault();
                return;
            }
            this.submitting = true;
        },

        async createCustomer() {
            if (!this.newCustomer.name || !this.newCustomer.phone) return;

            try {
                const response = await fetch(`{{ route('ventes.sales.quick-create-customer') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.newCustomer)
                });

                if (response.ok) {
                    const customer = await response.json();
                    // Add to select and select it
                    const select = document.querySelector('select[name="customer_id"]');
                    const option = new Option(`${customer.name} (${customer.phone})`, customer.id, true, true);
                    select.add(option);
                    this.customerId = customer.id;
                    this.showNewCustomerModal = false;
                    this.newCustomer = { name: '', phone: '' };
                }
            } catch (error) {
                console.error('Error creating customer:', error);
            }
        }
    };
}
</script>
@endsection
