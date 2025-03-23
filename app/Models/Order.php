<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'total',
        'status',
        'order_date',
        'order_time',
    ];

    // Định nghĩa quan hệ với model User (khách hàng)
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // Định nghĩa quan hệ với OrderDetail
    public function orderDetail()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }
}