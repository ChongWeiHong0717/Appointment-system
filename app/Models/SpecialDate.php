<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialDate extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = ['business_id', 'date', 'is_closed', 'opens_at', 'closes_at', 'note'];

    protected function casts(): array
    {
        return ['date' => 'date', 'is_closed' => 'boolean'];
    }
}
