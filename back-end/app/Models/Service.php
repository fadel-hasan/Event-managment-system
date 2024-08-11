<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
      'photograph',
        'food',
        'music',
        'price',
        'start_time',
        'end_time',
        'available_day',
        'type_id',
        'status',
        ''
    ];
    public function venue()
    {
        return $this->hasOne(Venue::class);
    }




    protected $casts = [
        'available_day' => 'array',
    ];
}
