<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    protected $fillable = [
        'code',
        'name',
        'status',
        'dealer_id',
    ];

    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }
}
