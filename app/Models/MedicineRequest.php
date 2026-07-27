<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class MedicineRequest extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $guarded = [];

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function category()
    {
        return $this->belongsTo(Medicine_Category::class, 'category_id');
    }

    public function dosageForm()
    {
        return $this->belongsTo(Dosage_Form::class, 'dosage_form_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function activeIngredients()
    {
        return $this->belongsToMany(Active_Ingredient::class, 'medicine_request_active_ingredients', 'medicine_request_id', 'active_ingredient_id')
            ->withPivot('strength_value', 'strength_unit');
    }
}
