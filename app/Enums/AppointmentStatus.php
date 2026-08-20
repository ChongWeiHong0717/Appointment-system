<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Booked = 'booked';
    case CheckedIn = 'checked_in';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Booked => 'Booked',
            self::CheckedIn => 'Checked in',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::NoShow => 'No show',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Booked => 'bg-sky-50 text-sky-700 ring-sky-600/20',
            self::CheckedIn => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            self::Completed => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            self::Cancelled => 'bg-rose-50 text-rose-700 ring-rose-600/20',
            self::NoShow => 'bg-slate-100 text-slate-700 ring-slate-600/20',
        };
    }

    public function canCheckIn(): bool
    {
        return $this === self::Booked;
    }

    public function canComplete(): bool
    {
        return $this === self::CheckedIn;
    }
}
