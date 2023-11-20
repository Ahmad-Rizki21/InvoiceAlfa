<?php

namespace App\Rules;

use App\Models\Store;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueStoreCodeRule implements ValidationRule
{
    protected $distributionCenterId;

    protected $excludeId;

    public function __construct($distributionCenterId, $excludeId = null)
    {
        $this->distributionCenterId = $distributionCenterId;

        $this->excludeId = $excludeId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value || ! $this->distributionCenterId) {
            return;
        }

        $entry = Store::where('code', $value)
            ->where('distribution_center_id', $this->distributionCenterId);

        if ($this->excludeId) {
            $entry->where('id', '!=', $this->excludeId);
        }

        $entry =  $entry->count(['id']);

        if ($entry) {
            $fail(__('validation.unique', ['attribute' => $attribute]));
        }
    }
}
