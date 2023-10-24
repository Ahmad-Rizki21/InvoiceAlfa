<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidUsernameRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        preg_match('/^[a-zA-Z0-9_\.]+$/', $value, $matches);

        if (count($matches) <= 0) {
            $fail(__(':entity can only contain alphanumeric characters, underscores, and periods', ['entity' => __('Username')]));
        }
    }
}
