<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;


class Sale_Payment extends Model
{

    //
    use HasFactory, Notifiable,SoftDeletes;
    protected $table = 'sale_payments';
    protected $guarded = [];

    public function sales()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }
}
