<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Model extends Eloquent
{
    protected $guarded = ['id'];

    public function path($uri = ''): string
    {
        $url = "/{$this->getTable()}/{$this->{$this->getRouteKeyName()}}";
        return $uri ? "{$url}/{$uri}" : $url;
    }

    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
