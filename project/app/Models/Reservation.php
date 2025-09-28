<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'flight_number',
        'departure_time',
        'status',
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'status' => 'string',
    ];

    public function passengers()
    {
        return $this->hasMany(Passenger::class);
    }
}
