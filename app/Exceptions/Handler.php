<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($this->shouldReturnJson($request, $exception)) {
            return response()->json([
                'status' => 'fail',
                'message' => $exception->getMessage(),
                'code' => 0,
                'data' => new \stdClass(),
                'meta' => new \stdClass(),
            ], 401);
        }

        return redirect()->guest($exception->redirectTo() ?? '/');
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Validation\ValidationException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'status' => 'fail',
            'message' => $exception->getMessage(),
            'code' => 0,
            'data' => new \stdClass(),
            'meta' => new \stdClass(),
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    /**
     * Convert the given exception to an array.
     *
     * @param  \Throwable  $e
     * @return array
     */
    protected function convertExceptionToArray(Throwable $e)
    {
        $isHttpException = $this->isHttpException($e);
        $statusCode = $isHttpException ? $e->getStatusCode() : 500;
        $status = $statusCode >= 500 ? 'error' : 'fail';
        $message = 'Internal server error occurred.';
        $debug = config('app.debug');

        if ($debug || $isHttpException) {
            $msg = $e->getMessage();

            if (empty($msg) && $isHttpException) {
                $msg = SymfonyResponse::$statusTexts[$statusCode] ?? '';
            }

            $message = $msg;
        }

        if ($e instanceof InvoiceImportRowException) {
            $message = $e->getMessage();
        }

        return $debug ? [
            'status' => $status,
            'message' => $message,
            'data' => new \stdClass(),
            'code' => 0,
            'debug' => [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->map(function ($trace) {
                    return Arr::except($trace, ['args']);
                })->all(),
            ],
        ] : [
            'status' => $status,
            'message' => $message,
            'code' => 0,
            'data' => new \stdClass(),
            'meta' => new \stdClass(),
        ];
    }
}
