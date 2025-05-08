<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EfakturaLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sef_status',
        'sef_response',
        'sent_at',
    ];

    public function invoice()
    {
        return $this->belongsTo(CustomerInvoice::class, 'invoice_id');
    }


}
