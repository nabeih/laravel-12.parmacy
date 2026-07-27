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

    const LOW_STOCK_THRESHOLD = 10;

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

    /**
     * Expired stock must never be sellable — flip is_active off for any batch
     * whose expiry date has passed. Safe to call before any read that decides
     * what's available for sale.
     */
    public static function deactivateExpired(): void
    {
        static::where('is_active', true)
            ->whereDate('expiry_date', '<', now()->toDateString())
            ->update(['is_active' => false]);
    }
}
