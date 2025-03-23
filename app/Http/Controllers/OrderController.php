<?php

namespace App\Http\Controllers;

use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // Lấy danh sách đơn hàng
    public function index(Request $request)
    {
        try {
            $orders = Order::with([
                'orderDetail.product:id,name,price', // Lấy thông tin sản phẩm trong đơn hàng
                'customer:id,name' // Lấy thông tin khách hàng
            ])
            ->select('id', 'status', 'total', 'order_date', 'order_time', 'customer_id')
            ->where('customer_id', auth()->id()) // Chỉ lấy đơn hàng của người dùng hiện tại
            ->get();
    
            return response()->json(['data' => $orders], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching orders: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    // Báo cáo đơn hàng
    public function orderReport(Request $request)
{
    $user = auth()->user();

    // Lấy các tham số lọc từ request
    $month = $request->input('month'); // Tháng
    $day = $request->input('day');     // Ngày
    $year = $request->input('year');   // Năm

    // Query cơ bản
    $query = Order::query();

    // Lọc theo vai trò người dùng
    if ($user->role_id == 1) {
        $query->with([
            'orderDetail.product:id,name,price',
            'customer:id,name,email'
        ]);
    } elseif ($user->role_id == 2) {
        $query->whereHas('orderDetail.product', function ($query) use ($user) {
            $query->where('seller_id', $user->id);
        })
        ->with([
            'orderDetail.product:id,name,price',
            'customer:id,name,email'
        ]);
    }

    // Lọc theo tháng, ngày, năm
    if ($month) {
        $query->whereMonth('order_date', $month);
    }
    if ($day) {
        $query->whereDay('order_date', $day);
    }
    if ($year) {
        $query->whereYear('order_date', $year);
    }

    // Lấy danh sách đơn hàng
    $orders = $query->select('id', 'status', 'total', 'order_date', 'order_time', 'customer_id')->get();

    return response()->json(['data' => $orders], 200);
}

    // Top sản phẩm bán chạy
    public function topDishes()
    {
        $topDishes = OrderDetail::select('products.id', 'products.name', 'products.price', 'products.image')
            ->selectRaw('SUM(order_details.quantity) as total_sold')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->groupBy('products.id', 'products.name', 'products.price', 'products.image')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        return response()->json(['data' => $topDishes], 200);
    }
}