<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class NoItemException extends Exception
{
    public function __construct(string $message = '')
    {
        parent::__construct("No item found. {$message}", JsonResponse::HTTP_BAD_REQUEST);
    }
}
