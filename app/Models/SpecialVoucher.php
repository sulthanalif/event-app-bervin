<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpecialVoucher extends Model
{
    use SoftDeletes;

    protected $table = 'special_vouchers';

    protected $fillable = [
        'product_id',
        'amount',
        'status',
        'start_date',
        'end_date',
        'is_claimed',
        'is_locked',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
