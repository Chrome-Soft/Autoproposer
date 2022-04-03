<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\ExpressionNormalizer;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreatedAtNormalizer extends ExpressionNormalizer
{
    const DATE = 'DATE';
    const DATETIME = 'DATETIME';
    const TIME = 'TIME';

    public function normalize()
    {
        $date = new Carbon($this->value);

        switch ($this->getFormat($date)) {
            case static::DATETIME:
                $this->query->{$this->whereFunction}(DB::raw("DATE_FORMAT({$this->field}, '%Y-%m-%d %H:%i')"), $this->relation, $this->value);
                break;
            case static::DATE:
                $this->query->{$this->whereFunction}(DB::raw("DATE_FORMAT({$this->field}, '%Y-%m-%d')"), $this->relation, $this->value);
                break;
            case static::TIME:
                $time = (new Carbon($this->value))->format('H:i');
                $this->query->{$this->whereFunction}(DB::raw("DATE_FORMAT({$this->field}, '%H:%i')"), $this->relation, $time);
                break;
        }
    }

    protected function getFormat(Carbon $date)
    {
        if ($date->format('Y-m-d') == $this->value) return static::DATE;
        if ($date->format('Y-m-d H:i') == $this->value
            || $date->format('Y-m-d H:i:s') == $this->value) return static::DATETIME;

        return static::TIME;
    }
}
