<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SelloutList extends Model
{
    protected $fillable = [
        'sellout_id',
        'product_id',
        'qty',
        'sub_total',
    ];

    public function sellout()
    {
        return $this->belongsTo(Sellout::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }


}
