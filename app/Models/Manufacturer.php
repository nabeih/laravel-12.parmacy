<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;


class Manufacturer extends Model
{

    //
    use HasFactory, Notifiable, SoftDeletes;
    protected $guarded = [];

    public function medicines()
    {
        return $this->hasMany(Medicine::class, 'manufacturer_id');
    }
}
