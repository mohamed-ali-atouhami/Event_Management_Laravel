<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'date', 'time', 'location','image', 'organizer_id', 'ticket_price','status','rejection_reason'
    ];
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }       
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'event_categories');
    }
    

}
