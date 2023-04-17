<?php


namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    const MESSAGE_RESPONSE_ERROR = "Internal server error";

    function getApiResponse(array|string $data, $status = 200, string $codeError = "generic_error"): JsonResponse
    {
        $response = ["status" => $status];

        if ($status == 200) {
            $response["result"] = $data;
        } else {
            $response["error"] = [
                "code" => $codeError,
                "message" => ($status == 500 && is_env_production()) ? self::MESSAGE_RESPONSE_ERROR : $data
            ];
        }

        return response()->json($response)->setStatusCode($status);
    }

    function successResponse(array|string $data = []): JsonResponse
    {
        return $this->getApiResponse($data, 200);
    }

    function badRequestResponse(array|string $data = [], string $codeError = "client_error"): JsonResponse
    {
        return $this->getApiResponse($data, 400, $codeError);
    }

    function notFoundResponse(array|string $data = [], string $codeError = "not_found"): JsonResponse
    {
        return $this->getApiResponse($data, 404, $codeError);
    }


    function internalErrorResponse(array|string $data = [], string $codeError = "server_error"): JsonResponse
    {
        return $this->getApiResponse($data, 500);
    }

    function handleInvoke(callable $invoke): JsonResponse
    {
        try {
            return $this->successResponse($invoke());
        } catch (\Throwable $e) {
            return $this->internalErrorResponse((is_env_debug()) ? $e->getMessage() : "Internal server error");
        }
    }

}
