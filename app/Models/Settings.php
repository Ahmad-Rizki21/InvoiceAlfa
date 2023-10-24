<?php

namespace App\Models;

use App\Enums\SettingKey;
use App\Services\Database\Eloquent\Model;
use Illuminate\Config\Repository;

class Settings extends Model
{
    protected $table = 'settings';

    protected $primaryKey = 'key';

    protected $keyType = 'int';

    public $incrementing = false;

    public $timestamps = false;

    public static $config;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key', 'value',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($instance) {
            static::$config = [];
        });
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = json_encode(['_' => $value]);
    }

    public function getValueAttribute()
    {
        if (isset($this->attributes['value'])) {
            $result = json_decode($this->attributes['value'], true);

            if (is_array($result) && isset($result['_'])) {
                return $result['_'];
            }
        }

        return null;
    }

    public static function data()
    {
        $data = collect([]);

        foreach (static::get() as $value) {
            $data[$value->key] = $value->value;
        }

        return $data;
    }

    public static function config()
    {
        if (!static::$config) {
            $data = new Repository();

            foreach (static::data() as $key => $value) {
                $data->set($key, $value);
            }

            static::$config = $data;
        }

        return static::$config;
    }

    public static function getValue($key, $default = null)
    {
        if ($key instanceof SettingKey) {
            $key = $key->value;
        }

        return static::config()->get($key, $default);
    }

    public static function setValue($key, $value, $persist = false)
    {
        if ($key instanceof SettingKey) {
            $key = $key->value;
        }

        $result = static::config()->set($key, $value);

        if ($persist) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return $result;
    }
}
