<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-md mx-auto bg-white min-h-screen shadow-lg rounded-lg overflow-hidden pb-16">
        <!-- Header -->
        <div class="p-4 flex justify-center items-center border-b">
            <div class="text-lg font-semibold text-center">Keranjang Belanja</div>
        </div>

        <!-- Daftar Produk di Keranjang -->
        <div class="p-4" id="cart-items">
            @if (empty($cart))
                <p class="text-center text-gray-600">Keranjang Anda kosong.</p>
            @else
                @foreach ($cart as $productId => $item)
                    <div class="border rounded-lg p-3 mb-3 flex items-center justify-between" data-product-id="{{ $productId }}">
                        <div>
                            <div class="font-semibold">{{ $item['name'] }}</div>
                            <div class="text-sm text-gray-600">Rp. {{ number_format($item['price'], 0) }}</div>
                            <div class="text-sm text-gray-600 flex items-center">
                                Jumlah:
                                <div class="flex items-center ml-2">
                                    <button class="text-gray-500 hover:text-gray-700 update-cart" data-action="decrease">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                        </svg>
                                    </button>
                                    <span class="mx-2 quantity">{{ $item['quantity'] }}</span>
                                    <button class="text-gray-500 hover:text-gray-700 update-cart" data-action="increase">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <form action="{{ route('cart.remove', $productId) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </form>
                    </div>
                @endforeach
                <!-- Total Belanja -->
                <div class="border-t pt-4 mt-4">
                    <div class="flex justify-between items-center">
                        <div class="text-lg font-semibold">Total Belanja</div>
                        <div class="text-lg font-bold text-green-600" id="total-amount">
                            Rp. {{ number_format(array_sum(array_map(function($item) { return $item['price'] * $item['quantity']; }, $cart)), 0) }}
                        </div>
                    </div>
                    <form action="{{ route('cart.checkout') }}" method="POST">
                        @csrf
                        <div class="mt-4">
                            <label for="name_customer" class="block text-sm font-medium text-gray-700">Nama Pelanggan</label>
                            <input type="text" name="name_customer" id="name_customer" required class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                        </div>
                        <div class="mt-4">
                            <label for="address" class="block text-sm font-medium text-gray-700">Alamat</label>
                            <textarea name="address" id="address" required class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"></textarea>
                        </div>
                        <button type="submit" class="bg-green-500 text-white rounded-lg w-full py-3 mt-4 hover:bg-green-600 transition duration-300">
                            Checkout
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- Bottom Navigation -->
        <div class="fixed bottom-0 left-0 w-full bg-white shadow-md py-4 flex justify-around border-t rounded-t-2xl">
            <button class="flex flex-col items-center text-sm hover:text-green-600 transition duration-300">
                <a href="/" class="flex flex-col items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10l9-7 9 7v10a2 2 0 01-2 2H5a2 2 0 01-2-2V10z" />
                    </svg>
                    <span>Explore</span>
                </a>
            </button>
            <button class="text-green-500 flex flex-col items-center text-sm hover:text-green-600 transition duration-300">
                <a href="{{ route('cart.view') }}" class="flex flex-col items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1 4h11.6l-1-4M7 13h10" />
                    </svg>
                    <span>Cart</span>
                </a>
            </button>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('.update-cart').click(function() {
                var productId = $(this).closest('div[data-product-id]').data('product-id');
                var action = $(this).data('action');
                $.ajax({
                    url: '{{ route("cart.update", ":id") }}'.replace(':id', productId),
                    method: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        action: action
                    },
                    success: function(response) {
                        if (response.success) {
                            var quantityElement = $('div[data-product-id="' + productId + '"] .quantity');
                            var newQuantity = response.quantity;
                            quantityElement.text(newQuantity);

                            // Update total amount
                            var totalAmount = 0;
                            $('#cart-items .border').each(function() {
                                var price = parseFloat($(this).find('.text-sm.text-gray-600').text().replace('Rp. ', '').replace(',', ''));
                                var quantity = parseInt($(this).find('.quantity').text());
                                totalAmount += price * quantity;
                            });
                            $('#total-amount').text('Rp. ' + totalAmount.toFixed(0).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
