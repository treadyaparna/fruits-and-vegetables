<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class ItemException extends Exception
{
    public function __construct(string $message = '')
    {
        parent::__construct("Error item: {$message}", JsonResponse::HTTP_BAD_REQUEST);
    }
}
