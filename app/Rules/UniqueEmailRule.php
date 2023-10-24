<?php

namespace App\Rules;

use App\Models\DistributionCenter;
use App\Models\Franchise;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueEmailRule implements ValidationRule
{
    protected $model;

    public function __construct($model = null)
    {
        $this->model = $model;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $message = __('validation.unique', ['attribute' => $attribute]);

        $models = [
            User::class,
            DistributionCenter::class,
            Franchise::class,
        ];

        foreach ($models as $model) {
            $exists = $model::withTrashed()->where('email', $value);

            if ($this->model && get_class($this->model) === $model) {
                $exists->where('id', '!=', $this->model->id);
            }

            if ($exists->count(['id'])) {
                $fail($message);

                return;
            }
        }
    }
}
