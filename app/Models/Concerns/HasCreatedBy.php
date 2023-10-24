<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait HasCreatedBy
{
    public static function bootHasCreatedBy()
    {
        static::creating(function ($model) {
            if (! $model->created_by_id) {
                $model->created_by_id = Auth::id();
            }
        });
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id', 'id', 'createdBy');
    }
}
