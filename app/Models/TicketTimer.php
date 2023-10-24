<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Database\Eloquent\Model;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

class TicketTimer extends Model
{
    use Concerns\HasCreatedBy;

    public const STATUS_START = 1;
    public const STATUS_START_REPAIR = 2;
    public const STATUS_STOP = 3;
    public const ALL_STATUSES = [
        self::STATUS_START,
        self::STATUS_START_REPAIR,
        self::STATUS_STOP,
    ];

    public const TIMER_OPEN = 1;
    public const TIMER_PENDING = 2;
    public const TIMER_START = 3;
    public const TIMER_COMPLETE = 4;
    public const ALL_TIMERS = [
        self::TIMER_OPEN,
        self::TIMER_PENDING,
        self::TIMER_START,
        self::TIMER_COMPLETE,
    ];

    protected $table = 'ticket_timers';

    protected $fillable = [
        'ticket_id', 'progress_message', 'started_at', 'ended_at', 'status',
        'created_at', 'updated_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id', 'ticket');
    }

    public static function calculateTimer(Ticket $ticket = null, DateTimeInterface $ticketCreatedAt = null, $timerStatus = [], $createdById = null)
    {
        if (! is_array($timerStatus)) {
            $timerStatus = [$timerStatus];
        }

        $now = config('app.timezone') === 'UTC' ? 'UTC_TIMESTAMP()' : 'NOW()';
        $table = (new static())->getTable();
        $ticketTable = (new Ticket())->getTable();

        $entry = DB::table($table)
            ->select(DB::raw('SUM(TIMESTAMPDIFF(SECOND, started_at, IF(ended_at IS NULL, '.$now.', ended_at))) as result'))
            ->whereIn($table.'.status', $timerStatus)
            ->join($ticketTable, $ticketTable.'.id', '=', $table.'.ticket_id')
            ->whereNull($ticketTable.'.deleted_at');

        if ($ticket) {
            $entry->where('ticket_id', $ticket->id);
        }

        if ($ticketCreatedAt) {
            $entry->whereRaw('DATE('.$ticketTable.'.created_at) = "'.$ticketCreatedAt->format('Y-m-d').'"');
        }

        $entry = $entry->first();

        return $entry->result ?? 0;
    }

    public static function calculateTotalTimer(Ticket $ticket = null, DateTimeInterface $ticketCreatedAt = null, $timerStatus = [], $createdById = null, DateTimeInterface $period = null)
    {
        if (! is_array($timerStatus)) {
            $timerStatus = [$timerStatus];
        }

        $sqlNow = config('app.timezone') === 'UTC' ? 'UTC_TIMESTAMP()' : 'NOW()';
        $ticketModel = new Ticket();
        $ticketTable = $ticketModel->getTable();
        $ticketTimerModel = new static();

        $query = TicketTimer::addSelect(DB::raw('SUM(TIMESTAMPDIFF(SECOND, started_at, IF(ended_at IS NULL, ' . $sqlNow . ', ended_at))) as result'))
            ->whereIn($ticketTimerModel->qualifyColumn('status'), $timerStatus)
            ->join($ticketTable, $ticketTimerModel->qualifyColumn('ticket_id'), '=', $ticketModel->qualifyColumn('id'))
            ->whereNull($ticketModel->qualifyColumn('deleted_at'));

        if ($ticket) {
            $query->where($ticketTimerModel->qualifyColumn('ticket_id'), $ticket->id);
        }

        if ($createdById) {
            $query->where($ticketModel->qualifyColumn('created_by_id'), $createdById);
        }

        if ($ticketCreatedAt) {
            $query->whereRaw('DATE('.$ticketModel->qualifyColumn('created_at').') = DATE("'.$ticketCreatedAt->format('Y-m-d').'")');
        }

        if ($period) {
            $query->whereMonth($ticketModel->qualifyColumn('created_at'), (int) $period->month);
            $query->whereYear($ticketModel->qualifyColumn('created_at'), $period->year);
        }

        $entry = $query->first();

        return $entry->result ?? 0;
    }

    public static function calculateAverageTimer($timerStatus, $createdById = null, DateTimeInterface $period = null)
    {
        if (! is_array($timerStatus)) {
            $timerStatus = [$timerStatus];
        }

        $sqlNow = config('app.timezone') === 'UTC' ? 'UTC_TIMESTAMP()' : 'NOW()';
        $ticketModel = new Ticket();
        $ticketTable = $ticketModel->getTable();
        $ticketTimerModel = new static();

        $sql = TicketTimer::addSelect(DB::raw('SUM(TIMESTAMPDIFF(SECOND, started_at, IF(ended_at IS NULL, ' . $sqlNow . ', ended_at))) as result'))
            ->whereIn($ticketTimerModel->qualifyColumn('status'), $timerStatus)
            ->join($ticketTable, $ticketTimerModel->qualifyColumn('ticket_id'), '=', $ticketModel->qualifyColumn('id'))
            ->whereNull($ticketModel->qualifyColumn('deleted_at'))
            ->groupBy($ticketTimerModel->qualifyColumn('ticket_id'));

        if ($createdById) {
            $sql->where($ticketModel->qualifyColumn('created_by_id'), $createdById);
        }

        if ($period) {
            $sql->whereMonth($ticketModel->qualifyColumn('created_at'), (int) $period->month);
            $sql->whereYear($ticketModel->qualifyColumn('created_at'), $period->year);
        }

        $sql = $sql->toRawSql();

        $sql = "
            SELECT AVG(a.result) result
            FROM (
                {$sql}
            ) a
            LIMIT 1
        ";

        $entry = DB::select($sql);

        return (float) ($entry[0]->result ?? 0);
    }

    public static function calculateMedianTimer($timerStatus, $createdById = null, DateTimeInterface $period = null)
    {
        if (! is_array($timerStatus)) {
            $timerStatus = [$timerStatus];
        }

        $sqlNow = config('app.timezone') === 'UTC' ? 'UTC_TIMESTAMP()' : 'NOW()';
        $ticketModel = new Ticket();
        $ticketTable = $ticketModel->getTable();
        $ticketTimerModel = new static();

        $sql = TicketTimer::addSelect(DB::raw('SUM(TIMESTAMPDIFF(SECOND, started_at, IF(ended_at IS NULL, ' . $sqlNow . ', ended_at))) as val'))
                    ->whereIn($ticketTimerModel->qualifyColumn('status'), $timerStatus)
                    ->join($ticketTable, $ticketTimerModel->qualifyColumn('ticket_id'), '=', $ticketModel->qualifyColumn('id'))
                    ->whereNull($ticketModel->qualifyColumn('deleted_at'))
                    ->groupBy($ticketTimerModel->qualifyColumn('ticket_id'));


        if ($createdById) {
            $sql->where($ticketModel->qualifyColumn('created_by_id'), $createdById);
        }

        if ($period) {
            $sql->whereMonth($ticketModel->qualifyColumn('created_at'), (int) $period->month);
            $sql->whereYear($ticketModel->qualifyColumn('created_at'), $period->year);
        }

        $sql = $sql->toRawSql();

        $sql = "
            SELECT x.val from ({$sql}) x, ({$sql}) y
            GROUP BY x.val
            HAVING SUM(SIGN(1-SIGN(y.val-x.val)))/COUNT(*) > .5
            LIMIT 1
        ";

        $entry = DB::select($sql);

        return (float) ($entry[0]->val ?? 0);
    }
}
