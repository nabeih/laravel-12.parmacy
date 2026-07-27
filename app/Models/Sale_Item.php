<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;


class Sale_Item extends Model
{

    //
    use HasFactory, Notifiable;
    protected $table = 'sale_items';
    protected $guarded = [];

    public function sales()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function batches()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }
    public function medicines()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }
}
