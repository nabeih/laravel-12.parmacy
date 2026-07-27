<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class PharmacyRequest extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $guarded = [];

    public function pharmacist()
    {
        return $this->belongsTo(Pharmacist::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }
}
