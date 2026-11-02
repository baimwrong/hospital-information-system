<?php

namespace App\Services;

use App\Models\Sequence;
use Illuminate\Support\Facades\DB;

class SequenceGenerator
{
    /**
     * Generate the next atomic sequence number for a given prefix and year/key.
     * Guaranteed collision-free across concurrent worker processes.
     */
    public static function nextSequence(string $key, callable $currentMaxResolver): int
    {
        return DB::transaction(function () use ($key, $currentMaxResolver): int {
            $seq = Sequence::where('key', $key)->lockForUpdate()->first();

            if (! $seq) {
                $initialMax = (int) $currentMaxResolver();
                $seq = Sequence::create([
                    'key' => $key,
                    'current_value' => $initialMax,
                ]);
            }

            $next = $seq->current_value + 1;
            $seq->current_value = $next;
            $seq->save();

            return $next;
        });
    }

    /**
     * Standardized Hospital Reference Number Generator (e.g. MRN-2026-0001, INV-2026-0001).
     */
    public static function generate(string $prefix, string $table, string $column, int $padLength = 4, bool $includeYear = true): string
    {
        $year = date('Y');
        $fullPrefix = $includeYear ? "{$prefix}-{$year}-" : "{$prefix}-";
        $key = $includeYear ? strtolower("{$prefix}_{$year}") : strtolower($prefix);

        $nextSeq = static::nextSequence($key, function () use ($table, $column, $fullPrefix): int {
            $pattern = "{$fullPrefix}%";
            $last = DB::table($table)
                ->where($column, 'like', $pattern)
                ->orderByRaw("LENGTH({$column}) DESC, {$column} DESC")
                ->value($column);

            if ($last && preg_match('/(\d+)$/', $last, $matches)) {
                return (int) $matches[1];
            }

            return 0;
        });

        return sprintf('%s%0'.$padLength.'d', $fullPrefix, $nextSeq);
    }
}
