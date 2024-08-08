<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * API Response Wrapper
 */
class ApiResponse
{
    /**
     * API Response with code and message
     *
     * @param int $code
     * @param string|null $message
     * @return JsonResponse
     */
    public static final function response(int $code = JsonResponse::HTTP_OK, string $message = null): JsonResponse
    {
        return new JsonResponse(array(
            'code' => $code,
            'message' => empty($message) ? 'Ok' : $message
        ), $code);
    }

    /**
     * Success API Response
     *
     * @param string $message
     * @return JsonResponse
     */
    public static final function responseOk(string $message = 'Error'): JsonResponse
    {
        return self::response(JsonResponse::HTTP_OK, $message);
    }

    /**
     * Error API Response
     *
     * @param string $message
     * @return JsonResponse
     */
    public static final function responseError(
        string $errorMessage = 'Error',
               $errorCode = JsonResponse::HTTP_BAD_REQUEST
    ): JsonResponse
    {
        return self::response($errorCode, $errorMessage);
    }

    /**
     * Create API Response
     *
     * @param $data
     * @return JsonResponse
     */
    public static final function responseCreated($data): JsonResponse
    {
        return new JsonResponse($data);
    }
}
