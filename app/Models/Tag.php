<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $primaryKey = 'no';

    protected $fillable = [
        'tag_en',
        'tag_ko',
        'update_admin',
        'hit',
    ];
}
