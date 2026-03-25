<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler
{
    /**
     * Map of exception classes to their handler methods
     */
    public static array $handlers = [
        AuthenticationException::class => 'handleAuthenticationException',
        AccessDeniedHttpException::class => 'handleAuthenticationException',
        AuthorizationException::class => 'handleAuthorizationException',
        ValidationException::class => 'handleValidationException',
        ModelNotFoundException::class => 'handleNotFoundException',
        NotFoundHttpException::class => 'handleNotFoundException',
        MethodNotAllowedHttpException::class => 'handleMethodNotAllowedException',
        HttpException::class => 'handleHttpException',
        QueryException::class => 'handleQueryException',
        UniqueConstraintViolationException::class => 'handleUniqueConstraintViolationException',
        ForbiddenException::class => 'handleForbiddenException',
        CustomException::class => 'handleBadRequestException',

    ];

    /**
     * Handle authentication exceptions
     */
    public function handleAuthenticationException(
        AuthenticationException|AccessDeniedHttpException $e,
        Request $request
    ): JsonResponse {

        return response()->format(null, $e->getMessage(), 401, false);
    }

    public function handleUniqueConstraintViolationException(
        UniqueConstraintViolationException $e,
        Request $request
    ): JsonResponse {
        $this->logException($e, 'record_already_exists');

        return response()->format(null, 'messages.record_already_exists', 400, false);
    }

    public function handleForbiddenException(
        ForbiddenException $e,
        Request $request
    ): JsonResponse {
        return response()->format(null, $e->getMessage(), 403, false);
    }

    public function handleBadRequestException(
        CustomException $e,
        Request $request
    ): JsonResponse {
        return response()->format(null, $e->getMessage(), 400, false);
    }

    /**
     * Handle authorization exceptions
     */
    public function handleAuthorizationException(
        AuthorizationException $e,
        Request $request
    ): JsonResponse {
        // $this->logException($e, 'Authorization failed');
        return response()->format(null, 'messages.unauthorized_action', 403, false);
    }

    /**
     * Handle validation exceptions
     */
    public function handleValidationException(
        ValidationException $e,
        Request $request
    ): JsonResponse {
        return response()->format($e->validator->getMessageBag(), 'messages.validation_error', 422, false);
    }

    /**
     * Handle not found exceptions
     */
    public function handleNotFoundException(
        ModelNotFoundException|NotFoundHttpException $e,
        Request $request
    ): JsonResponse {
        $this->logException($e, 'Resource not found');


        // $message = $e instanceof ModelNotFoundException
        //     ? 'messages.model_not_found'
        //     : "The requested endpoint '{$request->getRequestUri()}' was not found.";

        $message = __('messages.model_not_found');
        return response()->format(null, $message, 404, false);
    }

    /**
     * Handle method not allowed exceptions
     */
    public function handleMethodNotAllowedException(
        MethodNotAllowedHttpException $e,
        Request $request
    ): JsonResponse {
        return response()->format(null, 'messages.method_not_allowed', 405, false);
    }

    /**
     * Handle general HTTP exceptions
     */
    public function handleHttpException(HttpException $e, Request $request): JsonResponse
    {
        $this->logException($e, 'HTTP exception occurred');

        return response()->format(
            null,
            $e->getMessage() ?: 'An HTTP error occurred.',
            $e->getStatusCode(),
            false
        );
    }

    /**
     * Handle database query exceptions
     */
    public function handleQueryException(QueryException $e, Request $request): JsonResponse
    {
        $this->logException($e, 'Database query failed', ['sql' => $e->getSql()]);


        // Handle specific database constraint violations
        $errorCode = $e->errorInfo[1] ?? null;

        switch ($errorCode) {
            case 1451: // Foreign key constraint violation
                return response()->format(
                    [
                        'error' => [
                            'type' => $this->getExceptionType($e),
                            'status' => 409,
                            'message' => 'Cannot delete this resource because it is referenced by other records.',
                            'timestamp' => now()->toISOString(),
                        ]
                    ],
                    'Cannot delete this resource because it is referenced by other records.',
                    409,
                    false
                );

            case 1062: // Duplicate entry
                return response()->format(
                    [
                        'error' => [
                            'type' => $this->getExceptionType($e),
                            'status' => 409,
                            'message' => 'A record with this information already exists.',
                            'timestamp' => now()->toISOString(),
                        ]
                    ],
                    'A record with this information already exists.',
                    409,
                    false
                );

            default:
                return response()->format([
                    'error' => [
                        'type' => $this->getExceptionType($e),
                        'status' => 500,
                        'message' => 'A database error occurred. Please try again later.',
                        'timestamp' => now()->toISOString(),
                    ]
                ], 'A database error occurred. Please try again later.', 500, false);
        }
    }

    /**
     * Extract a clean exception type name
     */
    private function getExceptionType(Throwable $e): string
    {
        $className = basename(str_replace('\\', '/', get_class($e)));
        return $className;
    }

    /**
     * Log exception with context
     */
    private function logException(Throwable $e, string $message, array $context = []): void
    {
        $logContext = array_merge([
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip' => request()->ip(),
        ], $context);

        Log::warning($message, $logContext);
    }
}
