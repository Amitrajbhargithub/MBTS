<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerKyc extends Model
{
    use HasFactory;

    protected $table = 'customer_kyc';

    const DOCUMENT_TYPES = [
        'aadhar_front',
        'aadhar_back',
        'pancard_front',
        'selfie',
    ];

    protected $fillable = [
        'customer_id',
        'document_type',
        'file_path',
        'original_name',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'customer_id');
    }
}
