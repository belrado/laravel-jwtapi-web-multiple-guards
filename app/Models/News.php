<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $primaryKey = 'no';

    protected $fillable = [
        'subject_ko',
        'subject_en',
        'contents_ko',
        'contents_en',
        'update_admin',
        'use',
        'service',
        'service_date',
        'hit',
    ];

    protected $hidden = [
        'multiple_insert_check_id',
    ];
}
