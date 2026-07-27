<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;


class Medicine_Category extends Model
{

    //
    use HasFactory, Notifiable, SoftDeletes;
    protected $guarded = [];
    protected $table = 'medicine_categories';

    public function medicines()
    {

        return $this->hasMany(Medicine::class, 'category_id');
    }
}
