<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grocery App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .cart-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        }

        .animate-bounce {
            animation: bounce 1s infinite;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="max-w-md mx-auto bg-white min-h-screen shadow-lg rounded-lg overflow-hidden pb-16">
        <!-- Header -->
        <div class="p-4 flex justify-center items-center">
            <div class="text-lg font-semibold text-center">Bakso Kuah Mba Ulfa</div>
        </div>

        <!-- Search Bar -->
        <div class="p-4">
            <form action="{{ route('search') }}" method="GET">
                <input type="text" name="query" placeholder="Search Menu"
                    class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500">
            </form>
        </div>

        <!-- Exclusive Offer -->
        <div class="p-4">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold">Daftar Menu</h2>
            </div>
            <div class="grid grid-cols-2 gap-3 p-3">
                @foreach ($products as $product)
                    <div
                        class="border rounded-lg p-3 text-center hover:shadow-lg transition duration-300 flex flex-col h-full bg-white">
                        <!-- Gambar Produk -->
                        <img src="{{ asset('storage/' . $product->image) }}"
                            class="w-full h-32 object-cover rounded-lg mb-2" alt="{{ $product->name }}">

                        <!-- Nama Produk -->
                        <div class="mt-1 font-semibold text-md text-gray-800">{{ $product->name }}</div>

                        <!-- Deskripsi Produk -->
                        <div class="text-xs text-gray-600 flex-grow mt-1">{{ $product->description }}</div>

                        <!-- Harga Produk -->
                        <div class="mt-2 text-lg font-bold text-green-600">Rp. {{ number_format($product->price, 0) }}
                        </div>

                        <!-- Tombol Tambah ke Keranjang -->
                        <button
                            class="bg-green-500 text-white rounded-full w-8 h-8 mt-2 hover:bg-green-600 transition duration-300 mx-auto flex items-center justify-center add-to-cart"
                            data-product-id="{{ $product->id }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Bottom Navigation -->
        <div class="fixed bottom-0 left-0 w-full bg-white shadow-md py-4 flex justify-around border-t rounded-t-2xl">
            <!-- Explore -->
            <a href="/"
                class="text-green-500 flex flex-col items-center text-sm hover:text-green-600 transition duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10l9-7 9 7v10a2 2 0 01-2 2H5a2 2 0 01-2-2V10z" />
                </svg>
                <span>Explore</span>
            </a>

            <!-- Cart -->
            <a href="{{ route('cart.view') }}"
                class="flex flex-col items-center text-sm hover:text-green-600 transition duration-300 relative cart-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1 4h11.6l-1-4M7 13h10" />
                </svg>
                <span>Cart</span>
                <span class="cart-count" id="cart-count">{{ session('cart') ? count(session('cart')) : 0 }}</span>
            </a>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.add-to-cart').click(function() {
                var productId = $(this).data('product-id'); // Ambil ID produk dari atribut data
                var $button = $(this); // Simpan referensi tombol yang diklik

                $.ajax({
                    url: '{{ route('cart.add', ':id') }}'.replace(':id',
                    productId), // Ganti :id dengan productId
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}' // Tambahkan CSRF token
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update cart count
                            var cartCount = $('#cart-count');
                            var currentCount = parseInt(cartCount.text());
                            cartCount.text(currentCount + 1);

                            // Tambahkan animasi bounce ke cart count
                            cartCount.addClass('animate-bounce');
                            setTimeout(function() {
                                cartCount.removeClass('animate-bounce');
                            }, 1000); // Hapus animasi setelah 1 detik

                            // Tambahkan animasi bounce ke ikon cart
                            var cartIcon = $('.cart-icon svg');
                            cartIcon.addClass('animate-bounce');
                            setTimeout(function() {
                                cartIcon.removeClass('animate-bounce');
                            }, 1000); // Hapus animasi setelah 1 detik
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error); // Tampilkan error di console
                        alert(
                            'Terjadi kesalahan saat menambahkan produk ke keranjang. Silakan coba lagi.');
                    }
                });
            });
        });
    </script>
</body>

</html>
