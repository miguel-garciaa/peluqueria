<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentRequest extends Model
{
    protected $fillable = [
        'full_name',
        'phone',
        'service_id',
        'professional_id',
        'requested_date',
        'time_slot',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'requested_date' => 'date',
        ];
    }
}
