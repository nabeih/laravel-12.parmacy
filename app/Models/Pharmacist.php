<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;


class Pharmacist extends Model
{

    //
    use HasFactory, Notifiable, SoftDeletes;
    protected $guarded = [];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function pharmacies()
    {
        return $this->hasOne(Pharmacy::class, 'pharmacist_id');
    }

    public function assignments()
    {
        return $this->hasMany(PharmacyAssignment::class)->orderByDesc('started_at');
    }
}
