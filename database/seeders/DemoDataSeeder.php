<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\Business;
use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use App\Models\WebsiteSetting;
use App\Support\PhoneNumber;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            User::factory()->platformAdmin()->create([
                'name' => 'Platform Administrator',
                'email' => 'platform@bookwise.test',
                'password' => 'password',
            ]);

            $happyPaws = $this->createHappyPaws();
            $glowBeauty = $this->createGlowBeauty();

            $this->seedHours($happyPaws, [0]);
            $this->seedHours($glowBeauty, [1]);
            $this->seedAppointments($happyPaws, 'happy');
            $this->seedAppointments($glowBeauty, 'glow');
        });
    }

    private function createHappyPaws(): Business
    {
        $business = Business::create([
            'name' => 'Happy Paws Grooming',
            'slug' => 'happy-paws',
            'phone' => '+60 12-345 6789',
            'whatsapp' => '+60 12-345 6789',
            'email' => 'hello@happypaws.test',
            'address' => '18 Jalan Setia, Petaling Jaya, Selangor',
            'registration_number' => '202601234567',
            'description' => 'Gentle, attentive grooming in a calm and welcoming space.',
            'social_links' => ['instagram' => 'https://instagram.com/'],
            'timezone' => 'Asia/Kuala_Lumpur',
            'booking_interval_minutes' => 30,
        ]);

        WebsiteSetting::create([
            'business_id' => $business->id,
            'hero_heading' => 'Fresh coats. Happy companions.',
            'hero_subtitle' => 'Thoughtful grooming tailored to every companion, delivered by a team that treats comfort as part of the service.',
            'hero_cta_text' => 'Book a grooming visit',
            'about_heading' => 'Care you can feel good about',
            'about_body' => 'We pair experienced hands with a calm, unhurried approach. Every appointment is shaped around your companion’s needs and comfort.',
            'why_choose_us' => ['Gentle handling', 'Experienced groomers', 'Clear, caring communication'],
            'primary_color' => '#0f766e',
            'accent_color' => '#f59e0b',
            'button_style' => 'pill',
            'meta_title' => 'Happy Paws Grooming',
            'meta_description' => 'Professional, gentle grooming appointments in Petaling Jaya.',
        ]);

        User::factory()->for($business)->create([
            'name' => 'Happy Paws Admin',
            'email' => 'admin@happypaws.test',
            'role' => UserRole::BusinessAdmin,
            'password' => 'password',
        ]);

        $this->createCatalog($business, [
            'Dog' => [
                ['Full Grooming', 80, 90, 'A complete bath, coat trim, nail care and finishing service.'],
                ['Basic Grooming', 55, 60, 'Bath, blow dry, brushing and essential tidy-up.'],
                ['Nail Trimming', 20, 30, 'Careful nail trimming for a comfortable stride.'],
            ],
            'Cat' => [
                ['Full Grooming', 95, 90, 'A patient, low-stress full grooming experience.'],
                ['Nail Trimming', 25, 30, 'Gentle nail care in a calm setting.'],
            ],
        ]);

        return $business;
    }

    private function createGlowBeauty(): Business
    {
        $business = Business::create([
            'name' => 'Glow Beauty Studio',
            'slug' => 'glow-beauty',
            'phone' => '+60 3-7788 2200',
            'whatsapp' => '+60 11-2200 8899',
            'email' => 'hello@glowbeauty.test',
            'address' => '32 Jalan Telawi, Bangsar, Kuala Lumpur',
            'registration_number' => '202609876543',
            'description' => 'Considered skin and body treatments for a confident, restored you.',
            'social_links' => ['instagram' => 'https://instagram.com/'],
            'timezone' => 'Asia/Kuala_Lumpur',
            'booking_interval_minutes' => 30,
        ]);

        WebsiteSetting::create([
            'business_id' => $business->id,
            'hero_heading' => 'Your time to restore and glow.',
            'hero_subtitle' => 'Personalised skin and body rituals in a refined studio designed around your comfort.',
            'hero_cta_text' => 'Reserve your treatment',
            'about_heading' => 'Treatments with intention',
            'about_body' => 'Our therapists combine careful consultation with proven techniques so every treatment feels personal, purposeful, and deeply restorative.',
            'why_choose_us' => ['Personal consultations', 'Premium formulations', 'A calm private setting'],
            'primary_color' => '#7c3aed',
            'accent_color' => '#ec4899',
            'button_style' => 'rounded',
            'meta_title' => 'Glow Beauty Studio',
            'meta_description' => 'Personalised facial and massage appointments in Bangsar.',
        ]);

        User::factory()->for($business)->create([
            'name' => 'Glow Beauty Admin',
            'email' => 'admin@glowbeauty.test',
            'role' => UserRole::BusinessAdmin,
            'password' => 'password',
        ]);

        $this->createCatalog($business, [
            'Facial' => [
                ['Hydrating Facial', 160, 60, 'A replenishing treatment for soft, luminous skin.'],
                ['Acne Treatment', 190, 75, 'A focused treatment that calms and clarifies congested skin.'],
                ['Deep Cleansing', 150, 60, 'Thorough cleansing, gentle exfoliation and hydration.'],
            ],
            'Massage' => [
                ['60 Minute Massage', 180, 60, 'A restorative full-body massage tailored to your preferred pressure.'],
                ['90 Minute Massage', 250, 90, 'An extended treatment for deeper rest and release.'],
            ],
        ]);

        return $business;
    }

    private function createCatalog(Business $business, array $catalog): void
    {
        foreach ($catalog as $categoryName => $services) {
            $category = Category::create([
                'business_id' => $business->id,
                'name' => $categoryName,
                'slug' => Str::slug($categoryName),
                'description' => "Explore our {$categoryName} services.",
                'is_active' => true,
                'sort_order' => array_search($categoryName, array_keys($catalog), true),
            ]);

            foreach ($services as $index => [$name, $price, $duration, $description]) {
                Service::create([
                    'business_id' => $business->id,
                    'category_id' => $category->id,
                    'name' => $name,
                    'slug' => Str::slug($category->name.'-'.$name),
                    'description' => $description,
                    'price' => $price,
                    'duration_minutes' => $duration,
                    'is_active' => true,
                    'sort_order' => $index,
                ]);
            }
        }
    }

    private function seedHours(Business $business, array $closedDays): void
    {
        foreach (range(0, 6) as $day) {
            $closed = in_array($day, $closedDays, true);
            $business->businessHours()->create([
                'day_of_week' => $day,
                'is_closed' => $closed,
                'opens_at' => $closed ? null : '09:00',
                'closes_at' => $closed ? null : '18:00',
            ]);
        }

        $business->specialDates()->create([
            'date' => now($business->timezone)->addMonth()->startOfMonth()->toDateString(),
            'is_closed' => true,
            'note' => 'Public holiday',
        ]);
    }

    private function seedAppointments(Business $business, string $prefix): void
    {
        $services = $business->services()->orderBy('id')->take(4)->get();
        $today = CarbonImmutable::now($business->timezone)->startOfDay();
        $examples = [
            [$today, '09:30', AppointmentStatus::Booked, 'Alicia Tan', '012-345 6789'],
            [$today, '11:30', AppointmentStatus::CheckedIn, 'Daniel Lee', '017 222 8899'],
            [$today, '14:00', AppointmentStatus::Completed, 'Mei Ling', '016-808 1212'],
            [$today->addDay(), '10:00', AppointmentStatus::Booked, 'Harith Ismail', '019-442 3100'],
        ];

        foreach ($examples as $index => [$date, $time, $status, $name, $phone]) {
            $service = $services[$index % $services->count()];
            $start = CarbonImmutable::createFromFormat('H:i', $time, $business->timezone);
            $timestamps = match ($status) {
                AppointmentStatus::CheckedIn => ['checked_in_at' => $today->setTimeFrom($start)->addMinutes(2)],
                AppointmentStatus::Completed => [
                    'checked_in_at' => $today->setTimeFrom($start),
                    'completed_at' => $today->setTimeFrom($start)->addMinutes($service->duration_minutes),
                ],
                default => [],
            };

            Appointment::create([
                'business_id' => $business->id,
                'service_id' => $service->id,
                'customer_name' => $name,
                'customer_phone' => $phone,
                'customer_phone_normalized' => PhoneNumber::normalize($phone),
                'customer_email' => "{$prefix}.customer{$index}@example.test",
                'appointment_date' => $date->toDateString(),
                'start_time' => $start->format('H:i:s'),
                'end_time' => $start->addMinutes($service->duration_minutes)->format('H:i:s'),
                'status' => $status,
                'customer_notes' => $index === 0 ? 'Please call if the appointment needs to move.' : null,
                ...$timestamps,
            ]);
        }
    }
}
