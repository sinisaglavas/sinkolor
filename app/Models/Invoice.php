<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
<<<<<<< HEAD
=======

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
>>>>>>> e4bbb5e (kreiranje kupaca)
}
