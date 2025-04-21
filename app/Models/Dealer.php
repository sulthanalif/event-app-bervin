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

    public function sales()
    {
        return $this->hasMany(Sales::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_dealer')
            ->where('status', true)
            ->whereHas('roles', function ($query) {
                $query->where('name', '=', 'bse');
            });
    }

    public function budgetPeriod()
    {
        return $this->hasMany(BudgetPeriod::class);
    }
}
