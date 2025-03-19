<?php

namespace App\Http\Controllers;

use App\Models\OrderDetail;
use DB;
use App\Models\product;
use App\Models\Order;
use Illuminate\Http\Request;
use LDAP\Result;
use PhpParser\Node\Stmt\TryCatch;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // Lấy danh sách đơn hàng
        $orders = Order::with([
            'orderDetail.product:id,name,price'
        ])
        ->select('id', 'status', 'total', 'order_date', 'order_time', 'customer_id')
        ->when(auth()->user()->role_id == 3, function ($query) {
            // Nếu là Customer, chỉ trả về đơn hàng của họ
            return $query->where('customer_id', auth()->user()->id);
        })
        ->get();
    
        return response(['data' => $orders], 200);
    }

    public function show($id)
    {
        // Tìm đơn hàng theo ID
        $order = Order::findOrFail($id);
    
        // Trả về thông tin đơn hàng cùng với chi tiết sản phẩm
        return response([
            'data' => $order->loadMissing([
                'orderDetail:order_id,price,product_id,quantity', // Chi tiết đơn hàng
                'orderDetail.product:id,name,price,description,image' // Thông tin sản phẩm
            ])
        ], 200);
    }
    public function store(Request $request) 
    {
        // Validate dữ liệu đầu vào
        $request->validate([
            'products' => 'required|array', // Danh sách sản phẩm
            'products.*.id' => 'required|exists:products,id', // ID sản phẩm phải tồn tại
            'products.*.quantity' => 'required|integer|min:1', // Số lượng phải là số nguyên dương
        ]);
    
        try {
            DB::beginTransaction();
    
            // Chuẩn bị dữ liệu cho đơn hàng
            $data = [];
            $data['order_date'] = date('Y-m-d');
            $data['order_time'] = date('H:i:s');
            $data['status'] = 'ordered';
            $data['customer_id'] = auth()->user()->id; // Lấy ID khách hàng từ người dùng hiện tại
            $data['total'] = 0; // Gán giá trị mặc định ban đầu cho total
    
            // Tạo đơn hàng
            $order = Order::create($data);
    
            // Xử lý chi tiết đơn hàng và tính tổng giá trị
            $total = 0;
            collect($request->products)->each(function ($product) use ($order, &$total) {
                $foodDrink = Product::findOrFail($product['id']);
                $subtotal = $foodDrink->price * $product['quantity'];
                $total += $subtotal;
    
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $product['id'],
                    'price' => $foodDrink->price,
                    'quantity' => $product['quantity'],
                ]);
            });
    
            // Cập nhật tổng giá trị đơn hàng
            $order->total = $total;
            $order->save();
    
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return response(['error' => $th->getMessage()], 500);
        }
    
        return response(['data' => $order->load('orderDetail.product')], 201);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->delete();
        return response()->json([
            'message' => 'Order deleted successfully',
            'data' => $order->loadMissing([
                'orderDetail:order_id,price,product_id,quantity', 
                'orderDetail.product:name,id', 
                'waitress:id,name', 
                'cashier:id,name'
            ])
        ]);
    }

    public function payment($id)
    {
        // Tìm đơn hàng theo ID
        $order = Order::findOrFail($id);
    
        // Kiểm tra trạng thái đơn hàng
        if ($order->status !== 'ordered') {
            return response()->json(['message' => 'Only orders with status "ordered" can be paid.'], 400);
        }
    
        // Xử lý logic thanh toán (giả sử thanh toán thành công)
        // tích hợp cổng thanh toán tại đây nếu cần
    
        // Cập nhật trạng thái đơn hàng thành "paid" hoặc "completed"
        $order->status = 'paid'; // Hoặc 'completed' nếu thanh toán đồng nghĩa với hoàn thành
        $order->save();
    
        return response()->json(['message' => 'Order payment successful.', 'data' => $order], 200);
    }
    public function orderReport(Request $request)
    {
        $user = auth()->user();
    
        // Nếu là Admin, trả về toàn bộ báo cáo đơn hàng
        if ($user->role_id == 1) {
            $orders = Order::with([
                'orderDetail.product:id,name,price', // Chi tiết sản phẩm trong đơn hàng
                'customer:id,name,email' // Thông tin khách hàng (tên và email)
            ])
            ->select('id', 'status', 'total', 'order_date', 'order_time', 'customer_id')
            ->get();
        }
    
        // Nếu là Seller, chỉ trả về báo cáo các đơn hàng liên quan đến sản phẩm của họ
        elseif ($user->role_id == 2) {
            $orders = Order::whereHas('orderDetail.product', function ($query) use ($user) {
                $query->where('seller_id', $user->id);
            })
            ->with([
                'orderDetail.product:id,name,price', // Chi tiết sản phẩm trong đơn hàng
                'customer:id,name,email' // Thông tin khách hàng (tên và email)
            ])
            ->select('id', 'status', 'total', 'order_date', 'order_time', 'customer_id')
            ->get();
        }
    
        return response()->json(['data' => $orders], 200);
    }

    public function topDishes()
    {
        // Lấy ra 5 sản phẩm bán chạy nhất
        $topDishes = OrderDetail::select('products.id', 'products.name', 'products.price', 'products.image')
            ->selectRaw('SUM(order_details.quantity) as total_sold') // Tính tổng số lượng bán
            ->join('products', 'order_details.product_id', '=', 'products.id') // Kết nối với bảng products
            ->groupBy('products.id', 'products.name', 'products.price', 'products.image') // Nhóm theo sản phẩm
            ->orderByDesc('total_sold') // Sắp xếp giảm dần theo số lượng bán
            ->limit(5) // Lấy ra 5 sản phẩm
            ->get();
    
        // Trả về dữ liệu
        return response()->json(['data' => $topDishes], 200);
    }

}
