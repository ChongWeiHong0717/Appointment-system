<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SlugService
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public function forBusiness(string $modelClass, Business $business, string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'item';
        $slug = $base;
        $suffix = 2;

        while ($modelClass::query()
            ->withTrashed()
            ->where('business_id', $business->id)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
