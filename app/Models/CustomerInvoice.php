<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerInvoice extends Model
{
    use HasFactory;

    // app/Models/CustomerInvoice.php

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function outputs()
    {
        return $this->hasMany(CustomerOutput::class, 'invoice_id');
    }


}
