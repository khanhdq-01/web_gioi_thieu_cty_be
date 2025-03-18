<?php

namespace App\Http\Controllers;

use App\Models\OrderDetail;
use DB;
use App\Models\Item;
use App\Models\Order;
use Illuminate\Http\Request;
use LDAP\Result;
use PhpParser\Node\Stmt\TryCatch;

class OrderController extends Controller
{
    public function index(){
        $orders= Order::select('id', 'customer_name', 'table_no', 'order_date', 'order_time', 'status', 'total', 'waitress_id', 'cashier_id')->with(['waitress:id,name', 'cashier:id,name'])->get();
        return response(['data' => $orders]);
    }

    public function show( $id)  {

       $order = Order::findOrFail($id);
       return  response(['data' => $order->loadMissing(['orderDetail:order_id,price,item_id,qty', 'orderDetail.item:name,id', 'waitress:id,name', 'cashier:id,name'])]);
        
    }
    public function store(Request $request) 
    {

        $request->validate([
            'customer_name'=>'required|max:100',
            'table_no' => 'required|max:6',
        ]);

        try{
            DB::beginTransaction();
            
            $data = $request->only(['customer_name', 'table_no']);
            $data['order_date'] = date('Y-m-d');
            $data['order_time'] = date('H:i:s');
            $data['status'] = 'ordered';
            $data['total'] = 1000;
            $data['waitress_id'] = auth()->user()->id;
            $data['items'] = $request->items;

            $order = Order::create($data);

            collect($data['items'])->map(function($item) use($order) {
                $foodDrink = Item::where('id', $item['id'])->first();
                OrderDetail::create([
                    'order_id' => $order->id,
                    'item_id' =>$item['id'],
                    'price' => $foodDrink->price,
                    'qty' => $item['qty']
            ]);
            });

            //fix total
            $order->total = $order->sumOderPrice();
            $order->save();

            DB::commit();
        }
        catch(\Throwable $th){
            DB::rollBack();
            return response($th);
        }
        return response(['data'=> $order]);
        // return $data;
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
                'orderDetail:order_id,price,item_id,qty', 
                'orderDetail.item:name,id', 
                'waitress:id,name', 
                'cashier:id,name'
            ])
        ]);
    }

    public function setAsDone($id){
         $order = Order::findOrFail($id);
           
         if($order->status != 'ordered') {
            return response('thay đổi trạng thái đơn hàng thành thực hiện thành công', 404);
         }
         $order->status = 'done';
         $order->save();

         return response(['data' => $order->loadMissing(['orderDetail:order_id,price,item_id,qty', 'orderDetail.item:name,id', 'waitress:id,name', 'cashier:id,name'])]);

    }

    public function payment($id){
        $order = Order::findOrFail($id);
       
        if($order->status != 'done') {
           return response('payment cannot done because the status is not ordered ', 404);
        }
        $order->status = 'paid';
        $order->cashier_id = auth()->user()->id;
        $order->save();

        return response(['data' => $order->loadMissing(['orderDetail:order_id,price,item_id,qty', 'orderDetail.item:name,id', 'waitress:id,name', 'cashier:id,name'])]);
   }

   public function orderReport(Request $request)
   {

    $data =Order::whereMonth('order_date', $request->month);
    $orders= $data
    ->select('id', 'customer_name', 'table_no', 'order_date', 'order_time', 'status', 'total', 'waitress_id', 'cashier_id')
    ->with(['waitress:id,name', 'cashier:id,name'])
    ->get();

    $orderCount =$data->count();
    $maxPayment = $data->max('total');
    $minPayment = $data->min('total');

    $result = [
        'orderCount' => $orderCount,
        'maxPayment' => $maxPayment,
        'minPayment' => $minPayment,
        'orders' => $orders
    ];

    return response(['data' => $result]);
   }

    public function topDishes()
    {
        $topDishes = OrderDetail::select('items.id', 'items.name', 'items.price', 'items.image')
        ->selectRaw('SUM(order_details.qty) as total_sold')
        ->join('items', 'order_details.item_id', '=', 'items.id') // Sửa 'order_detail' thành 'order_details'
        ->groupBy('items.id', 'items.name', 'items.price', 'items.image')
        ->orderByDesc('total_sold')
        ->limit(3)
        ->get();
    
    
        return response()->json(['data' => $topDishes]);
    }

}
