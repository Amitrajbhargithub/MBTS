<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'payu_id',
        'amount',
        'plan_info',
        'customer_email',
        'customer_phone',
        'customer_name',
        'status',
    ];
}
