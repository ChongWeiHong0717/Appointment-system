<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Models\Service;
use App\Support\PhoneNumber;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $phone = fake()->numerify('01#-### ####');

        return [
            'service_id' => Service::factory(),
            'business_id' => fn (array $attributes) => Service::findOrFail($attributes['service_id'])->business_id,
            'customer_name' => fake()->name(),
            'customer_phone' => $phone,
            'customer_phone_normalized' => PhoneNumber::normalize($phone),
            'customer_email' => fake()->safeEmail(),
            'appointment_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => AppointmentStatus::Booked,
        ];
    }
}
