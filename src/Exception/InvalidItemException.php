<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class InvalidItemException extends Exception
{
    public function __construct(string $message = '')
    {
        parent::__construct("Invalid item provided. {$message}", JsonResponse::HTTP_BAD_REQUEST);
    }
}
