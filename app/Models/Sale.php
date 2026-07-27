<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;


class Sale extends Model
{

    //
    use HasFactory, Notifiable, SoftDeletes;
    protected $guarded = [];

    public function pharmacists()
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }
    public function users()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sale_items()
    {
        return $this->hasMany(Sale_Item::class, 'sale_id');
    }

    public function sale_payments()
    {
        return $this->hasMany(Sale_Payment::class, 'sale_id');
    }
}
