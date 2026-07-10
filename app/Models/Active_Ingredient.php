<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;


class Active_Ingredient extends Model
{

    //
    use HasFactory, Notifiable,SoftDeletes;
    protected $guarded = [];
    public function medicines()
    {
        return $this->belongsToMany(Medicine::class, 'medicine_active_ingredients')
            ->withPivot('strength_value', 'strength_unit');
    }
}
