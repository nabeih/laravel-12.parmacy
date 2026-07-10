<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;


class PurchaseItem extends Model
{

    //
    use HasFactory, Notifiable, SoftDeletes;
    protected $guarded = [];

    public function purchases()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function medicines()
    {

        return $this->belongsTo(Medicine::class);
    }
    public function batches()
    {
        return $this->hasOne(Batch::class);
    }
}
