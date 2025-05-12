<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersCategories extends Model
{
    use HasFactory;

    public function categories()
    {
        return $this->belongsTo(Categories_member::class, 'categories_id');
    }
}
