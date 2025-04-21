<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sellout extends Model
{
    protected $fillable = [
        'invoice',
        'date',
        'amount',
        'sales_id',
        'image',
    ];

    public function sales()
    {
        return $this->belongsTo(Sales::class);
    }

    public function list()
    {
        return $this->hasMany(SelloutList::class);
    }
}
