<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class InvalidItemTypeException extends Exception
{
    public function __construct(string $message = '')
    {
        parent::__construct("Invalid item type provided. {$message}", JsonResponse::HTTP_BAD_REQUEST);
    }
}
