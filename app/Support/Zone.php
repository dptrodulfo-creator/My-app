<?php

namespace App\Support;

/**
 * Small helper around the "zone" the person picked in the top bar.
 * The pick lives in the session, so it lasts until sign-out.
 */
class Zone
{
    /** Every zone the picker offers. */
    public static function all(): array
    {
        return config('runsheet.zones');
    }

    /** The zone saved in the session, or the default zone when nothing valid is saved. */
    public static function current(): string
    {
        $picked = session('zone');

        return in_array($picked, static::all(), true)
            ? $picked
            : config('runsheet.default_zone');
    }
}
