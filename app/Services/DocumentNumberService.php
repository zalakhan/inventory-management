<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Generate the next document number and increment the counter atomically.
     */
    public function next(string $prefixKey, string $nextKey, int $padLength = 5): string
    {
        return DB::transaction(function () use ($prefixKey, $nextKey, $padLength) {
            $prefix = (string) Setting::getValue($prefixKey, '');
            $next = (int) Setting::getValue($nextKey, 1);

            $number = $prefix.str_pad((string) $next, $padLength, '0', STR_PAD_LEFT);

            Setting::query()->where('key', $nextKey)->update([
                'value' => (string) ($next + 1),
            ]);

            return $number;
        });
    }
}
