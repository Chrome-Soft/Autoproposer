<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App;


trait HasInteractionStat
{
    abstract public function interaction_items();

    public function getAllPresentAttribute()
    {
        return $this->getCountBy('present');
    }

    public function getAllViewAttribute()
    {
        return $this->getCountBy('view');
    }

    public function getViewRatio($present, $view)
    {
        if ($present == 0) return '0%';
        return $this->getRatio($present, $view);
    }

    public function getViewRatioAttribute()
    {
        $present = $this->getAllPresentAttribute();
        if ($present == 0) return '0%';

        return $this->getRatio($present, $this->getAllViewAttribute());
    }

    protected function getRatio($present, $view)
    {
        return number_format(($this->getAllViewAttribute() / $present) * 100, 0, '.', ' ') . '%';
    }

    public function getCountBy(string $type)
    {
        return $this->interaction_items()->with(['interaction' => function ($query) use ($type) {
                $query->where('type', '=', $type);
            }])
            ->get()
            ->where('interaction', '!=', null)
            ->count();
    }
}
