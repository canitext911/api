<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Psap extends Model
{
    protected $table      = 'psap';
    public    $timestamps = true;
    protected $casts      = [
        'meta' => 'json'
    ];

    protected $guarded = [];
}
