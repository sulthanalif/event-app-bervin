<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherClaim extends Model
{
    protected $fillable = [
        'voucher_id',
        'user_id',
        'sellout_id',
        'sales_id',
        'date',
        'amount',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        $this->belongsTo(Sales::class);
    }

    public function sellout()
    {
        return $this->belongsTo(Sellout::class);
    }
}
