<?php

declare(strict_types=1);

namespace Sirv\Exception;

/**
 * Exception thrown when request validation fails.
 */
class ValidationException extends SirvException
{
    protected array $validationErrors;

    public function __construct(string $message = '', array $validationErrors = [])
    {
        parent::__construct($message, 400);
        $this->validationErrors = $validationErrors;
    }

    /**
     * Get the validation errors.
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}
