<?php

declare(strict_types=1);

namespace App\Services\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;

class ApiResponse
{
    public function json(
        string $status = 'success',
        mixed $data = null,
        string $message = 'Ok',
        int $httpStatus = JsonResponse::HTTP_OK,
        array $meta = [],
        int $code = 0,
        array $errors = []
    ) {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data ?: new \stdClass(),
            'meta' => empty($meta) ? new \stdClass() : $meta,
            'code' => $code,
            'errors' => $errors,
        ], $httpStatus);
    }

    public function success(
        mixed $data = null,
        string $message = '',
        int $httpStatus = JsonResponse::HTTP_OK,
        array $meta = [],
        int $code = 0
    ) {
        if (! $data || (is_array($data) && ! count($data))) {
            $data = new \stdClass();
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data ?: new \stdClass(),
            'meta' => empty($meta) ? new \stdClass() : $meta,
            'code' => $code,
            'errors' => [],
        ], $httpStatus);
    }

    public function fail(
        mixed $data = null,
        string $message = '',
        int $httpStatus = JsonResponse::HTTP_BAD_REQUEST,
        array $meta = [],
        int $code = 0
    ) {
        if (! $data || (is_array($data) && ! count($data))) {
            $data = new \stdClass();
        }

        return response()->json([
            'status' => 'fail',
            'message' => $message,
            'data' => $data ?: new \stdClass(),
            'meta' => empty($meta) ? new \stdClass() : $meta,
            'code' => $code,
            'errors' => [],
        ], $httpStatus);
    }

    public function error(
        mixed $data = null,
        string $message = '',
        int $httpStatus = JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
        array $meta = [],
        int $code = 0
    ) {
        if (! $data || (is_array($data) && ! count($data))) {
            $data = new \stdClass();
        }

        return response()->json([
            'status' => 'fail',
            'message' => $message,
            'data' => $data ?: new \stdClass(),
            'meta' => empty($meta) ? new \stdClass() : $meta,
            'code' => $code,
            'errors' => [],
        ], $httpStatus);
    }

    public function notFound(string $message = '', int $code = 0, string $entity = '')
    {
        if (! $message && $entity) {
            $message = __(':entity could not be found', ['entity' => $entity]);
        }

        return response()->json([
            'status' => 'fail',
            'message' => $message ?: __('No data found.'),
            'data' => new \stdClass(),
            'meta' => new \stdClass(),
            'code' => $code,
            'errors' => [],
        ], JsonResponse::HTTP_NOT_FOUND);
    }

    public function inputError(array $errors, int $code = 0)
    {
        $errorMessage = null;

        if (count($errors)) {
            foreach ($errors as $error) {
                if (is_array($error)) {
                    foreach ($error as $v) {
                        if ($v) {
                            $errorMessage = $v;
                            break;
                        }
                    }
                } else if (is_string($error)) {
                    $errorMessage = $error;
                }

                if ($errorMessage) {
                    break;
                }
            }
        }

        return response()->json([
            'status' => 'fail',
            'message' =>  $errorMessage ?: __('The given data was invalid.'),
            'data' => new \stdClass(),
            'meta' => new \stdClass(),
            'code' => $code,
            'errors' => $errors,
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function serverError(string $message = '', int $code = 0)
    {
        return response()->json([
            'status' => 'error',
            'message' =>  App::environment('production') || empty($message) ? __('Internal server error. Please try again.') : $message,
            'data' => new \stdClass(),
            'meta' => new \stdClass(),
            'code' => $code,
            'errors' => [],
        ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function unauthenticated(string $message = '', int $code = 0)
    {
        return $this->fail(
            message: $message ?: __('You are not authorized.'),
            httpStatus: JsonResponse::HTTP_UNAUTHORIZED,
            code: $code
        );
    }

    public function unauthorized(string $message = '', int $code = 0)
    {
        return $this->fail(
            message: $message ?: __('Unauthorized. Please login to proceed.'),
            httpStatus: JsonResponse::HTTP_UNAUTHORIZED,
            code: $code
        );
    }

    public function forbidden(string $message = '', int $code = 0)
    {
        return $this->fail(
            message: $message ?: __('Forbidden'),
            httpStatus: JsonResponse::HTTP_FORBIDDEN,
            code: $code
        );
    }

    public function forbiddenAccess(int $code = 0, string $entity = 'record')
    {
        $message = __('You do not have the permission to access this :entity', ['entity' => $entity]);

        return $this->fail(
            message: $message,
            httpStatus: JsonResponse::HTTP_FORBIDDEN,
            code: $code
        );
    }

    public function forbiddenManage(int $code = 0, string $entity = 'record')
    {
        $message = __('You do not have the permission to manage this :entity', ['entity' => $entity]);

        return $this->fail(
            message: $message,
            httpStatus: JsonResponse::HTTP_FORBIDDEN,
            code: $code
        );
    }

    public function forbiddenAction(int $code = 0)
    {
        return $this->fail(
            message: __('You do not have the permission to perform this action.'),
            httpStatus: JsonResponse::HTTP_FORBIDDEN,
            code: $code
        );
    }

    public function forbiddenLogin(int $code = 0)
    {
        return $this->fail(
            message: __('You do not have the permission to login.'),
            httpStatus: JsonResponse::HTTP_FORBIDDEN,
            code: $code
        );
    }
}
