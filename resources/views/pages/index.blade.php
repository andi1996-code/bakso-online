<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakso Ulfa</title>
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

        <!-- Banner -->
        <div class="p-4">
            <div class="flex justify-between items-center pb-4">
                <h2 class="text-lg font-semibold">Lokasi</h2>
            </div>
            <a href="https://www.google.com/maps/dir/?api=1&destination=-4.896812,105.204187&travelmode=driving"
                target="_blank">
                <div id="map" class="w-full h-64 rounded-lg shadow-md relative">
                    <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-center py-2">
                        Klik untuk membuka rute di Google Maps
                    </div>
                </div>
            </a>
            <script>
                function initMap() {
                    var sellerLocation = {
                        lat: -4.896867,
                        lng: 105.204121
                    }; // Koordinat lokasi penjual
                    var map = new google.maps.Map(document.getElementById('map'), {
                        zoom: 15, // Level zoom
                        center: sellerLocation // Pusat peta
                    });
                    var marker = new google.maps.Marker({
                        position: sellerLocation, // Posisi marker
                        map: map // Peta yang digunakan
                    });
                }
            </script>
            <script async defer
                src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDTv3XxmfCMqlw7E2AP_gErOnIX7v9GhvI&callback=initMap"></script>
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

                        <div class="flex justify-between items-center mt-2">
                            <div class="text-md font-bold text-green-600">Rp.
                                {{ number_format($product->price, 0) }}
                            </div>

                            <!-- Tombol Tambah ke Keranjang -->
                            <button
                                class="bg-green-500 text-white rounded-md w-8 h-8 hover:bg-green-600 transition duration-300 flex items-center justify-center add-to-cart"
                                data-product-id="{{ $product->id }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>

                        <!-- Harga Produk -->

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
                class="flex flex-col items-center text-sm hover:text-green-600 transition duration-300 relative">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1 4h11.6l-1-4M7 13h10" />
                </svg>
                <span>Keranjang</span>
                <span class="cart-count absolute top-0 right-0" id="cart-count">{{ session('cart') ? count(session('cart')) : 0 }}</span>
            </a>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.add-to-cart').click(function() {
                var productId = $(this).data('product-id');
                $.ajax({
                    url: '{{ route('cart.add', ':id') }}'.replace(':id', productId),
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            var cartCount = $('#cart-count');
                            var currentCount = parseInt(cartCount.text());
                            cartCount.text(currentCount + 1);

                            // Tambahkan animasi bounce
                            cartCount.addClass('animate-bounce');
                            setTimeout(function() {
                                cartCount.removeClass('animate-bounce');
                            }, 1000); // Hapus animasi setelah 1 detik
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error); // Tampilkan error di console
                    }
                });
            });
        });
    </script>
</body>

</html>
