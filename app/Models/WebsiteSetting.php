<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $attributes = [
        'hero_cta_text' => 'Book an appointment',
        'about_heading' => 'About us',
        'primary_color' => '#0f766e',
        'accent_color' => '#f59e0b',
        'button_style' => 'rounded',
    ];

    protected $fillable = [
        'business_id', 'hero_heading', 'hero_subtitle', 'hero_image_path', 'hero_cta_text',
        'about_heading', 'about_body', 'why_choose_us', 'primary_color', 'accent_color',
        'button_style', 'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return ['why_choose_us' => 'array'];
    }
}
