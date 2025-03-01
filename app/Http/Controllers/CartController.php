<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order; // Assuming you have an Order model
use App\Models\OrderItem; // Assuming you have an OrderItem model

class CartController extends Controller
{
    // Tambah produk ke keranjang
    public function addToCart(Request $request, $productId)
    {
        $product = Product::findOrFail($productId); // Cari produk berdasarkan ID
        $cart = session()->get('cart', []); // Ambil data keranjang dari session

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
        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang!');
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
            $cart[$productId]['quantity'] = $request->quantity; // Update quantity
            session()->put('cart', $cart); // Simpan kembali data keranjang ke session
        }

        return redirect()->back()->with('success', 'Keranjang berhasil diperbarui!');
    }

    // Checkout process
    public function checkout(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->back()->with('error', 'Keranjang Anda kosong.');
        }

        // Create a new order
        $order = Order::create([
            'name_customer' => $request->input('name_customer'),
            'address' => $request->input('address'),
            'total_price' => array_sum(array_map(function($item) { return $item['price'] * $item['quantity']; }, $cart)),
            'status' => 'pending',
        ]);

        // Create order items
        foreach ($cart as $productId => $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        // Clear the cart
        session()->forget('cart');

        return redirect()->route('cart.view')->with('success', 'Transaksi berhasil! Pesanan Anda telah diterima.');
    }
}
