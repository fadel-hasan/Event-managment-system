<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    use HasFactory;

    protected $table = 'service_type';

    public function services()
    {
        return $this->hasMany(Service::class);

    }
    protected $fillable = [
      'image',
      'name'
    ];
}
