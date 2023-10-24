<?php

namespace App\Services\Database\Eloquent;

use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Illuminate\Support\Str;

/**
 * App\Services\Database\Eloquent\Pivot
 *
 * @method static \App\Services\Database\Eloquent\Builder|Model filter(?\App\Services\UrlQuery\ModelFilter $modelFilter = null, ?\App\Services\UrlQuery\UrlQuery $urlQuery = null)
 * @method static \App\Services\Database\Eloquent\Builder|Pivot newModelQuery()
 * @method static \App\Services\Database\Eloquent\Builder|Pivot newQuery()
 * @method static \App\Services\Database\Eloquent\Builder|Pivot query()
 * @method static \App\Services\Database\Eloquent\Builder|Pivot toRawSql()
 * @mixin \Eloquent
 */
class Pivot extends Model
{
    use AsPivot;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * {@inheritdoc}
     */
    public function getTable()
    {
        if (!isset($this->table)) {
            $this->setTable(str_replace(
                '\\',
                '',
                static::$snakeAttributes ?
                    Str::snake(Str::singular(class_basename($this))) :
                    Str::camel(Str::singular(class_basename($this)))
            ));
        }

        return $this->table;
    }
}
