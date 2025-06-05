<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerInvoice extends Model
{
    use HasFactory;

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // U ovoj fakturi ima vise stavki koje se ponavljaju-kljuc je invoice_id u CustomerOutput
    public function outputs()
    {
        return $this->hasMany(CustomerOutput::class, 'invoice_id');
    }


}
