<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'logo_path', 'phone', 'whatsapp', 'email', 'address',
        'registration_number', 'description', 'social_links', 'timezone',
        'booking_interval_minutes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'is_active' => 'boolean',
            'booking_interval_minutes' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }

    public function workerAbsences(): HasMany
    {
        return $this->hasMany(WorkerAbsence::class);
    }

    public function businessHours(): HasMany
    {
        return $this->hasMany(BusinessHour::class);
    }

    public function specialDates(): HasMany
    {
        return $this->hasMany(SpecialDate::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function websiteSetting(): HasOne
    {
        return $this->hasOne(WebsiteSetting::class);
    }
}
