<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\ProductImport\Exceptions;

use Throwable;

class CreateProductException extends \Exception {
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
