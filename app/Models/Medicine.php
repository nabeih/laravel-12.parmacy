<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;


class Medicine extends Model
{

    //
    use HasFactory, Notifiable, SoftDeletes;
    protected $guarded = [];

    public function dosage_forms()
    {

        return $this->belongsTo(Dosage_Form::class);
    }

    public function medicine_categories()
    {
        return $this->belongsTo(Medicine_Category::class, 'category_id');
    }

    public function manufacturers()
    {

        return $this->belongsTo(Manufacturer::class);
    }

    public function active_ingredients()
    {
        return $this->belongsToMany(Active_Ingredient::class, 'medicine_active_ingredients', 'medicine_id', 'active_ingredient_id')
            ->withPivot('strength_value', 'strength_unit');
    }
}
