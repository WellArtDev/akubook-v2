<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'bank_name',
        'account_number',
        'account_holder',
        'account_id',
        'opening_balance',
        'is_active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'account_id' => 'integer',
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
