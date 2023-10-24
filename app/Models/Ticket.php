<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes, Concerns\HasCreatedBy;

    public const STATUS_OPEN = 1;
    public const STATUS_CLOSED = 2;
    public const ALL_STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_CLOSED,
    ];

    public const DOWN_TIME_CUSTOMER = 1;
    public const DOWN_TIME_PROVIDER = 2;
    public const ALL_DOWN_TIMES = [
        self::DOWN_TIME_CUSTOMER,
        self::DOWN_TIME_PROVIDER,
    ];

    protected $table = 'tickets';

    protected $fillable = [
        'title', 'content', 'customer_id', 'remote_location_id', 'status', 'closed_at', 'closed_by_id',
        'no', 'down_time_caused_by', 'closed_message',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($instance) {
            if (! $instance->no) {
                $instance->no = static::generateTicketNo();
            }
        });
    }

    public static function generateTicketNo()
    {
        $value = static::withTrashed()->latest('id')->pluck('id')->first();

        if (! $value) {
            $value = 0;
        }

        $value++;

        $now = Carbon::now();

        return 'T' . $now->format('ymd') . str_pad((string) $value, 5, '0', STR_PAD_LEFT);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id', 'customer');
    }

    public function remoteLocation()
    {
        return $this->belongsTo(RemoteLocation::class, 'remote_location_id', 'id', 'remoteLocation');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by_id', 'id', 'closedBy');
    }

    public function ticketNotes()
    {
        return $this->hasMany(TicketNote::class, 'ticket_id', 'id');
    }

    public function ticketTimers()
    {
        return $this->hasMany(TicketTimer::class, 'ticket_id', 'id');
    }

    public function latestTicketTimer()
    {
        return $this->hasOne(TicketTimer::class, 'ticket_id', 'id')->latest('id');
    }
}
