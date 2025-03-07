<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Midtrans\Config;
use Midtrans\Snap;

class CartController extends Controller
{
    // Tambah produk ke keranjang
    public function addToCart(Request $request, $productId)
    {
        $cart = session()->get('cart', []); // Ambil data keranjang dari session
        $product = Product::find($productId); // Ambil produk dari database

        // Cek apakah produk sudah ada di keranjang
        if (isset($cart[$productId])) {
            // Jika sudah ada, tambahkan quantity
            $cart[$productId]['quantity'] += $request->quantity ?? 1;
        } else {
            // Jika belum ada, tambahkan produk baru ke keranjang
            $cart[$productId] = [
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->image,
                'quantity' => $request->quantity ?? 1,
            ];
        }

        session()->put('cart', $cart); // Simpan data keranjang ke session
        return response()->json(['success' => true]); // Return success response
    }

    // Tampilkan halaman keranjang
    public function viewCart()
    {
        $cart = session()->get('cart', []); // Ambil data keranjang dari session
        return view('carts.index', compact('cart'));
    }

    // Hapus produk dari keranjang
    public function removeFromCart($productId)
    {
        $cart = session()->get('cart', []); // Ambil data keranjang dari session

        if (isset($cart[$productId])) {
            unset($cart[$productId]); // Hapus produk dari keranjang
            session()->put('cart', $cart); // Simpan kembali data keranjang ke session
        }

        return redirect()->back()->with('success', 'Produk berhasil dihapus dari keranjang!');
    }

    // Update quantity produk di keranjang
    public function updateCart(Request $request, $productId)
    {
        $cart = session()->get('cart', []); // Ambil data keranjang dari session

        if (isset($cart[$productId])) {
            if ($request->action == 'increase') {
                $cart[$productId]['quantity'] += 1; // Tambah quantity
            } elseif ($request->action == 'decrease' && $cart[$productId]['quantity'] > 1) {
                $cart[$productId]['quantity'] -= 1; // Kurangi quantity
            }
            session()->put('cart', $cart); // Simpan kembali data keranjang ke session

            // Hitung total harga
            $totalAmount = array_sum(array_map(function ($item) {
                return $item['price'] * $item['quantity'];
            }, $cart));

            return response()->json([
                'success' => true,
                'quantity' => $cart[$productId]['quantity'],
                'total_amount' => $totalAmount // Kirim total harga yang diperbarui
            ]);
        }

        return response()->json(['success' => false]);
    }

    // Checkout process
    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_customer' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return response()->json(['error' => 'Keranjang Anda kosong.'], 400);
        }

        // Create a new order
        $order = Order::create([
            'name_customer' => $request->input('name_customer'),
            'address' => $request->input('address'),
            'total_price' => array_sum(array_map(function ($item) {
                return $item['price'] * $item['quantity'];
            }, $cart)),
            'status' => 'pending',
        ]);

        // Create order items
        $productList = "";
        foreach ($cart as $productId => $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
            $productList .= "{$item['name']} (Qty: {$item['quantity']}) - Rp. " . number_format($item['price'] * $item['quantity'], 0) . "\n";
        }

        // Clear the cart
        session()->forget('cart');

        // Prepare WhatsApp message
        $message = "Data Pembeli:\nNama: {$request->input('name_customer')}\nAlamat: {$request->input('address')}\n\nList Produk:\n{$productList}\nTotal Bayar: Rp. " . number_format($order->total_price, 0) . "\n\nTerimakasih 🙏";

        // Return WhatsApp URL
        $whatsappUrl = "https://wa.me/6281221788707?text=" . urlencode($message); // Replace with seller's WhatsApp number

        // Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        // Prepare Midtrans transaction details
        $transactionDetails = [
            'order_id' => $order->id,
            'gross_amount' => $order->total_price,
        ];

        $itemDetails = [];
        foreach ($cart as $productId => $item) {
            $itemDetails[] = [
                'id' => $productId,
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'name' => $item['name'],
            ];
        }

        $customerDetails = [
            'first_name' => $request->input('name_customer'),
            'last_name' => $request->input('name_customer'),
            'address' => $request->input('address'),
        ];

        $transaction = [
            'transaction_details' => $transactionDetails,
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
        ];

        try {

            $snapToken = Snap::getSnapToken($transaction);
            return response()->json(['snap_token' => $snapToken, 'whatsapp_url' => $whatsappUrl]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCartCount()
    {
        $cart = session()->get('cart', []);
        return response()->json(['count' => count($cart)]);
    }
}
