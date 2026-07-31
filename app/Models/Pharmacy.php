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
        return $this->belongsTo(Pharmacist::class, 'pharmacist_id');
    }

    public function assignments()
    {
        return $this->hasMany(PharmacyAssignment::class)->orderByDesc('started_at');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function pharmacyRequest()
    {
        return $this->hasOne(PharmacyRequest::class, 'pharmacy_id')->latestOfMany();
    }
}
