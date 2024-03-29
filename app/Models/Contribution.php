<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contribution extends Model
{

    protected $fillable = [
        'github',
        'referer',
        'count',
    ];

    protected $dates = ['created_at', 'updated_at'];

}
