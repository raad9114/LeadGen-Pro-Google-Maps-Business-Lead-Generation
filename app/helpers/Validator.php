<?php
/**
 * Input Validation Helper
 */

class Validator
{
    private array $errors = [];

    /**
     * Validate required field
     */
    public function required(string $field, mixed $value, ?string $label = null): self
    {
        $label = $label ?? $field;
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->errors[$field] = "{$label} is required.";
        }
        return $this;
    }

    /**
     * Validate email format
     */
    public function email(string $field, mixed $value, ?string $label = null): self
    {
        $label = $label ?? $field;
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "{$label} must be a valid email.";
        }
        return $this;
    }

    /**
     * Validate minimum length
     */
    public function minLength(string $field, mixed $value, int $min, ?string $label = null): self
    {
        $label = $label ?? $field;
        if (!empty($value) && mb_strlen($value) < $min) {
            $this->errors[$field] = "{$label} must be at least {$min} characters.";
        }
        return $this;
    }

    /**
     * Validate maximum length
     */
    public function maxLength(string $field, mixed $value, int $max, ?string $label = null): self
    {
        $label = $label ?? $field;
        if (!empty($value) && mb_strlen($value) > $max) {
            $this->errors[$field] = "{$label} must not exceed {$max} characters.";
        }
        return $this;
    }

    /**
     * Validate numeric
     */
    public function numeric(string $field, mixed $value, ?string $label = null): self
    {
        $label = $label ?? $field;
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field] = "{$label} must be a number.";
        }
        return $this;
    }

    /**
     * Validate integer
     */
    public function integer(string $field, mixed $value, ?string $label = null): self
    {
        $label = $label ?? $field;
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->errors[$field] = "{$label} must be an integer.";
        }
        return $this;
    }

    /**
     * Validate value is in allowed list
     */
    public function in(string $field, mixed $value, array $allowed, ?string $label = null): self
    {
        $label = $label ?? $field;
        if (!empty($value) && !in_array($value, $allowed, true)) {
            $this->errors[$field] = "{$label} is not a valid option.";
        }
        return $this;
    }

    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get all errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get first error message
     */
    public function firstError(): ?string
    {
        return !empty($this->errors) ? array_values($this->errors)[0] : null;
    }

    /**
     * Sanitize string input
     */
    public static function sanitize(mixed $value): string
    {
        if ($value === null) return '';
        return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize integer input
     */
    public static function sanitizeInt(mixed $value): int
    {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Escape output for HTML
     */
    public static function e(mixed $value): string
    {
        if ($value === null) return '';
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
