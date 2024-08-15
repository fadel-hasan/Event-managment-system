<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable =[
        'booking_id',
        'amount',
        'payment_method_id',
        'paymentData'
    ];
}
