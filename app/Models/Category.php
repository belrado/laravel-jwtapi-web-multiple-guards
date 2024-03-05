<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $primaryKey = 'no';

    protected $fillable = [
        'cate_en',
        'cate_ko',
        'update_admin',
        'hit'
    ];
}
