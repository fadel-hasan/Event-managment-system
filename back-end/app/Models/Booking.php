<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
      'user_id',
      'service_id',
      'bookingDate',
      'status'
    ];

    public function payment()
    {
        return $this->hasOne(Payment::class, 'booking_id', 'id');
    }

    public function service(){
        return $this->belongsTo(Service::class);
    }
}
