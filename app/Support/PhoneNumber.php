<?php

namespace App\Support;

final class PhoneNumber
{
    public static function normalize(?string $phone): string
    {
        return preg_replace('/\D+/', '', $phone ?? '') ?? '';
    }
}
