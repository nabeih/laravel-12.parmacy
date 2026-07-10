<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;


class Pharmacy extends Model
{

    //
    use HasFactory, Notifiable, SoftDeletes;
    protected $guarded = [];

    public function pharmacists()
    {
        return $this->belongsTo(Pharmacist::class);
    }
}
