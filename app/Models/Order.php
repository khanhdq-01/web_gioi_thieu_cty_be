<?php

namespace App\Models;

use App\Models\OrderDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use function PHPUnit\Framework\returnArgument;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'table_no',
        'order_date', 
        'order_time',
        'status',
        'total',
        'waitress_id',
        'cashier_id'
    ];

    public function sumOderPrice(){
        $orderDetail = OrderDetail::where('order_id',$this->id)->select('price','qty')->get();
        $totalPerItem = collect($orderDetail)->map(function($item){
            return $item->price * $item->qty;
        });
        $sum = collect($totalPerItem)->sum();

        return $sum;
    }

    public function OrderDetail(): HasMany {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }
    
    public  function waitress(): BelongsTo {
        return $this->belongsTo(User::class, 'waitress_id', 'id');
    }

    public  function cashier(): BelongsTo {
        return $this->belongsTo(User::class, 'cashier_id', 'id');
    }
}
