<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessHour extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = ['business_id', 'day_of_week', 'period_index', 'is_closed', 'opens_at', 'closes_at'];

    protected function casts(): array
    {
        return ['day_of_week' => 'integer', 'period_index' => 'integer', 'is_closed' => 'boolean'];
    }
}
