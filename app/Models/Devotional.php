<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devotional extends Model
{
    protected $fillable = [
        'month',
        'day',
        'reference_old_testament',
        'content_old_testament',
        'reference_new_testament',
        'content_new_testament',
    ];
}
