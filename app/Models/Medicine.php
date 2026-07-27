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
    protected $table = 'medicines';

    public function dosageForm()
    {

        return $this->belongsTo(Dosage_Form::class);
    }

    public function category()
    {
        return $this->belongsTo(Medicine_Category::class, 'category_id');
    }

    public function manufacturer()
    {

        return $this->belongsTo(Manufacturer::class);
    }

    public function activeIngredients()
    {
        return $this->belongsToMany(Active_Ingredient::class, 'medicine_active_ingredients', 'medicine_id', 'active_ingredient_id')
            ->withPivot('strength_value', 'strength_unit');
    }
}
