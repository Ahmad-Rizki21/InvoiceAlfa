<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportCache extends Model
{
    use HasUlids;

    protected $table = 'import_caches';

    protected $fillable = [
        'id', 'import_path', 'import_type', 'content',
        'errors', 'created_at', 'updated_at',
    ];

    public $casts = [
        'content' => 'array',
        'errors' => 'array',
    ];
}
