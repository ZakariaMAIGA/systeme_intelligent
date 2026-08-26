<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Desk extends Model
{
    protected $guarded = [];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function currentTicket()
    {
        return $this->hasOne(Ticket::class)->whereIn('status', ['appele', 'en_cours']);
    }
}
