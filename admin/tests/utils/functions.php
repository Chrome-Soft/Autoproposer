<?php

function create(string $class, int $count = 1, array $attributes = [])
{
    $xs = factory("App\\{$class}", $count)->create($attributes);
    if ($count == 1) return $xs->first();

    return $xs;
}

function make(string $class, int $count = 1, array $attributes = [])
{
    $xs = factory("App\\{$class}", $count)->make($attributes);

    if ($count == 1) return $xs->first();

    return $xs;
}