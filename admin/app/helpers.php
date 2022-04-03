<?php

if (!function_exists('either')) {
    function either($value, $optional) {
        if ($value) {
            if (is_callable($value)) {
                return $value();
            }

            return $value;
        }

        if (is_callable($optional)) {
            return $optional();
        }

        return $optional;
    }
}