<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Models\Concerns\BelongsToBusiness;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id', 'service_id', 'customer_name', 'customer_phone',
        'customer_phone_normalized', 'customer_email', 'customer_notes',
        'appointment_date', 'start_time', 'end_time', 'status', 'checked_in_at',
        'completed_at', 'cancelled_at', 'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'status' => AppointmentStatus::class,
            'checked_in_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function startsAt(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->appointment_date->format('Y-m-d').' '.$this->start_time, $this->business->timezone);
    }
}
