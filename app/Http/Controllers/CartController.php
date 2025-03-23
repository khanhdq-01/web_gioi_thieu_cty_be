<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;

class CartController extends Controller
{
    // Thêm sản phẩm vào giỏ hàng
    public function addToCart(Request $request)
    {
        $userId = auth()->id(); // Lấy ID người dùng đã đăng nhập
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1); // Mặc định số lượng là 1

        // Kiểm tra nếu sản phẩm đã tồn tại trong giỏ hàng
        $cartItem = Cart::where('user_id', $userId)
                        ->where('product_id', $productId)
                        ->first();

        if ($cartItem) {
            // Nếu sản phẩm đã tồn tại, tăng số lượng
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            // Nếu sản phẩm chưa tồn tại, thêm mới
            $cartItem = Cart::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        return response()->json([
            'message' => 'Product added to cart successfully!',
            'cart_item' => $cartItem,
        ]);
    }

    // Hiển thị danh sách sản phẩm trong giỏ hàng
    public function viewCart()
    {
        $userId = auth()->id(); // Lấy ID người dùng đã đăng nhập

        // Lấy danh sách sản phẩm trong giỏ hàng của người dùng
        $cart = Cart::with('product')->where('user_id', $userId)->get();

        if ($cart->isEmpty()) {
            return response()->json([
                'message' => 'Your cart is empty!',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'message' => 'Cart retrieved successfully!',
            'data' => $cart,
        ]);
    }

    // Đặt hàng từ giỏ hàng
    public function placeOrder()
    {
        $userId = auth()->id(); // Lấy ID người dùng đã đăng nhập
    
        // Lấy giỏ hàng của người dùng
        $cartItems = Cart::with('product')->where('user_id', $userId)->get();
    
        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Your cart is empty!',
            ], 400);
        }
    
        // Tính tổng giá trị đơn hàng
        $totalPrice = $cartItems->sum(function ($cartItem) {
            return $cartItem->product->price * $cartItem->quantity;
        });
    
        try {
            // Bắt đầu transaction
            \DB::beginTransaction();
    
            // Tạo đơn hàng
            $order = Order::create([
                'customer_id' => $userId,
                'total' => $totalPrice,
                'status' => 'pending', // Trạng thái mặc định là "pending"
                'order_date' => now()->toDateString(),
                'order_time' => now()->toTimeString(),
            ]);
    
            // Thêm sản phẩm vào bảng OrderDetail
            foreach ($cartItems as $cartItem) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                ]);
            }
    
            // Xóa giỏ hàng sau khi đặt hàng
            Cart::where('user_id', $userId)->delete();
    
            // Commit transaction
            \DB::commit();
    
            return response()->json([
                'message' => 'Order placed successfully!',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            \DB::rollBack();
    
            return response()->json([
                'message' => 'Failed to place order!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}