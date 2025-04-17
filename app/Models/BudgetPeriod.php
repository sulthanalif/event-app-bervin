<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetPeriod extends Model
{
    use SoftDeletes;

    protected $table = 'budget_periods';

    protected $fillable = [
        'dealer_id',
        'start_date',
        'end_date',
        'budget',
        'status',
    ];

    public function dealer()
    {
        return $this->belongsTo(Dealer::class, 'dealer_id');
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'budget_period_id');
    }
}
