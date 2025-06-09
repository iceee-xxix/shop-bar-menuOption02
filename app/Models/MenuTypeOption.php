<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuTypeOption extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function option()
    {
        return $this->hasMany(MenuOption::class, 'menu_type_option_id');
    }
}
