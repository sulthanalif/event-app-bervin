<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dealer extends Model
{
    use SoftDeletes;

    protected $table = 'dealers';

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'status',
    ];
}
