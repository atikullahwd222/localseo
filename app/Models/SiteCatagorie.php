<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteCatagorie extends Model
{
    use HasFactory;

    protected $fillable = [
        'cat_name',
        'cat_Description',
    ];
}
