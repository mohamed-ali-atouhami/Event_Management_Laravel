<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    protected $fillable = [
        'registration_id', 'qr_code'
    ];
    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}
