<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class ApiException extends RuntimeException
{
    public function __construct(
        string $message,
        protected int $statusCode = 400,
        protected mixed $errors = null,
        protected ?string $errorCode = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrors(): mixed
    {
        return $this->errors;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public static function badRequest(string $message, mixed $errors = null, ?string $errorCode = null): self
    {
        return new self($message, 400, $errors, $errorCode);
    }

    public static function unauthorized(string $message = 'Unauthorized', mixed $errors = null, ?string $errorCode = null): self
    {
        return new self($message, 401, $errors, $errorCode);
    }

    public static function forbidden(string $message = 'Forbidden', mixed $errors = null, ?string $errorCode = null): self
    {
        return new self($message, 403, $errors, $errorCode);
    }

    public static function notFound(string $message = 'Data Not Found', mixed $errors = null, ?string $errorCode = null): self
    {
        return new self($message, 404, $errors, $errorCode);
    }

    public static function unprocessable(string $message = 'Validation Error', mixed $errors = null, ?string $errorCode = null): self
    {
        return new self($message, 422, $errors, $errorCode);
    }

    public static function conflict(string $message, mixed $errors = null, ?string $errorCode = null): self
    {
        return new self($message, 409, $errors, $errorCode);
    }
}
