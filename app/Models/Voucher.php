<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'budget_period_id',
        'ordinal',
        'code',
        'amount',
        'status',
        'is_claimed',
        'is_locked',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->code = self::generateCode(10);
        });
    }

    // GENERATE CODE
    public static function generateCode($length = 6)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function budgetPeriod()
    {
        return $this->belongsTo(BudgetPeriod::class, 'budget_period_id');
    }
}
