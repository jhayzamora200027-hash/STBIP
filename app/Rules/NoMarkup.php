<?php

namespace App\Rules;

use App\Support\InputValueGuard;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoMarkup implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!InputValueGuard::containsForbiddenMarkup($value)) {
            return;
        }

        $fail(InputValueGuard::violationMessage($attribute));
    }
}