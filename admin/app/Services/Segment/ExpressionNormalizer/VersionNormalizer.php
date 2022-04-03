<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\ExpressionNormalizer;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VersionNormalizer extends ExpressionNormalizer
{
    public function normalize()
    {
        $this->convertVersionToSemantic();

        $sum = collect(explode('.', $this->value))
            ->map(function ($x, $i) {
                if ($i == 0) return $x * 100000;        // major
                else if ($i == 1) return $x * 1000;     // minor
                else return $x;                         // patch
            })->sum();

        /**
         * TODO
         * - Ha valhol csak annyi van, hogy 9, akkor abból a substring függvények majos minro és patchnek is 9 et adnak vissza, tehát a végeredmény:
         *  - 9 * 100000 + 9 * 1000 + 9 lesz
         *  - 9 * 100000 helyett
         */

        $field = DB::raw("
            (
				(CONVERT(SUBSTRING_INDEX(SUBSTRING_INDEX(browser_version, '.', 1), '.', -1), UNSIGNED INTEGER) * 100000)
				+ (CONVERT(SUBSTRING_INDEX(SUBSTRING_INDEX(browser_version, '.', 2), '.', -1), UNSIGNED INTEGER) * 1000)
				+ (CONVERT(SUBSTRING_INDEX(SUBSTRING_INDEX(browser_version, '.', 3), '.', -1), UNSIGNED INTEGER))
			)
        ");
        $this->query->{$this->whereFunction}($field, $this->relation, $sum);
    }

    /**
     * Bérmilyen verziószámot szemantikus major.minor.patch formátumra konvertál
     */
    protected function convertVersionToSemantic()
    {
        $count = substr_count($this->value, '.');

        if ($count > 2) {
            $lastPos = 0;
            $positions = [];

            while (($lastPos = strpos($this->value, '.', $lastPos)) !== false) {
                $positions[] = $lastPos;
                $lastPos = $lastPos + 1;
            }

            $this->value = substr($this->value, '0', $positions[2]);
            return $this->value;
        }

        for ($i = 0; $i < 2 - $count; $i++)
            $this->value .= '.0';

        if (Str::endsWith($this->value, '.'))
            $this->value .= '0';

        return $this->value;
    }
}
