<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
        'user_id'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function venue()
    {
        return $this->hasOne(Venue::class);
    }

    public function favorited()
    {
        return (bool) Favorite::where('user_id', Auth::id())
            ->where('service_id', $this->id)
            ->first();
    }

    public function type()
    {
        return $this->belongsTo(ServiceType::class);

    }

    public function foods()
    {
        return $this->hasMany(Food::class);
    }

    public function musics()
    {
        return $this->hasMany(Music::class);
    }

    public function reviews(){
        return $this->hasMany(Review::class);
    }
    protected $casts = [
        'available_day' => 'array',
    ];
}
