<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;


class Batch extends Model
{

    //
    use HasFactory, Notifiable, SoftDeletes;
    protected $guarded = [];

    public function purchase_items()
    {
        return $this->belongsTo(PurchaseItem::class, 'purchase_item_id');
    }

    public function medicines()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function pharmacists()
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }
}
