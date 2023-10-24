<?php

namespace App\Http\Controllers;

use App\Exports\AvailabilityExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Http\Resources\ReportResource;
use App\Models\RemoteLocation;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketTimer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\MaritalStatusExport;
use App\Models\Customer;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFactory;
use Exception;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $urlQuery = $this->urlQuery($request);
        $searches = $urlQuery->searches();
        $limit = $urlQuery->limit();
        $sorts = $urlQuery->sorts();
        $page = $urlQuery->page();

        $entries = $this->getData($request);

        // $remoteLocationModel = new RemoteLocation();
        // $ticketModel = new Ticket();
        // $ticketTimerModel = new TicketTimer();
        // $remoteLocationTable = $remoteLocationModel->getTable();
        // $ticketTable = $ticketModel->getTable();
        // $ticketTimerTable = $ticketTimerModel->getTable();

        // $currentTotalDays = Carbon::now()->daysInMonth;

        // $sql = "
        //     SELECT {$remoteLocationTable}.*,
        //         COUNT({$ticketTable}.id) total_tickets,
        //         {$currentTotalDays} as current_total_days
        //     FROM {$remoteLocationTable}
        //     LEFT OUTER JOIN {$ticketTable} ON {$remoteLocationTable}.id = {$ticketTable}.remoteLocation_id
        //         AND {$ticketTable}.deleted_at IS NULL
        //     WHERE {$remoteLocationTable}.deleted_at IS NULL
        //     AND {$remoteLocationTable}.status = 1
        //     GROUP BY {$remoteLocationTable}.id
        // ";

        // $entries = DB::select($sql);

        // dd($entries);

        $items = $entries->items();

        $items = $this->transformItems($request, $items);

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'reports' => ReportResource::collection($items),
            ],
            'meta' => [
                'searches' => $searches->original(),
                'total' => $entries->total(),
                'sorts' => $sorts->original(),
                'limit' => $limit,
                'page' => $page,
                'has_more_page' => $entries->hasMorePages(),
            ],
        ]);
    }

    public function transformItems(Request $request, $items)
    {
        $applicableMonth = $request->applicable_month ? Carbon::parse($request->applicable_month) : Carbon::now();

        $generateTimerSql = function ($item, $status, $downtimeBy = null) use ($applicableMonth) {
            $ticketModel = new Ticket();
            $ticketTable = $ticketModel->getTable();
            $ticketTimerModel = new TicketTimer();

            $sqlNow = config('app.timezone') === 'UTC' ? 'UTC_TIMESTAMP()' : 'NOW()';

            if (!is_array($status)) {
                $status = [$status];
            }

            $query = TicketTimer::addSelect(DB::raw('SUM(TIMESTAMPDIFF(SECOND, started_at, IF(ended_at IS NULL, ' . $sqlNow . ', ended_at))) as result'))
            ->whereIn($ticketTimerModel->qualifyColumn('status'), $status)
                ->join($ticketTable, $ticketModel->qualifyColumn('id'), '=', $ticketTimerModel->qualifyColumn('ticket_id'))
                ->whereMonth($ticketModel->qualifyColumn('created_at'), (int) $applicableMonth->month)
                ->whereYear($ticketModel->qualifyColumn('created_at'), (int) $applicableMonth->year)
                ->whereNull($ticketModel->qualifyColumn('deleted_at'))
                ->where($ticketModel->qualifyColumn('status'), Ticket::STATUS_CLOSED)
                ->where($ticketModel->qualifyColumn('remote_location_id'), $item->id);

            if ($downtimeBy) {
                $query->where($ticketModel->qualifyColumn('down_time_caused_by'), $downtimeBy);
            }

            return $query->first()->result ?? 0;
        };

        foreach ($items as $i => $item) {
            $totalTickets = Ticket::where('remote_location_id', $item->id)
                ->where('status', Ticket::STATUS_CLOSED)
                ->whereMonth('created_at', (int) $applicableMonth->month)
                ->whereYear('created_at', (int) $applicableMonth->year)
                ->count();

            $items[$i]->setAttribute('down_time_provider', $generateTimerSql($item, [TicketTimer::TIMER_OPEN, TicketTimer::TIMER_PENDING, TicketTimer::TIMER_START], Ticket::DOWN_TIME_PROVIDER));
            $items[$i]->setAttribute('down_time_customer', $generateTimerSql($item, [TicketTimer::TIMER_OPEN, TicketTimer::TIMER_PENDING, TicketTimer::TIMER_START], Ticket::DOWN_TIME_CUSTOMER));
            $items[$i]->setAttribute('mttr', $generateTimerSql($item, [TicketTimer::TIMER_OPEN, TicketTimer::TIMER_START], Ticket::DOWN_TIME_PROVIDER));
            $items[$i]->setAttribute('total_tickets', $totalTickets);
            $items[$i]->setAttribute('open_clock', $generateTimerSql($item, TicketTimer::TIMER_OPEN));
            $items[$i]->setAttribute('pending_clock', $generateTimerSql($item, TicketTimer::TIMER_PENDING));
            $items[$i]->setAttribute('start_clock', $generateTimerSql($item, TicketTimer::TIMER_START));
        }

        return $items;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {
        $urlQuery = $this->urlQuery($request);
        $limit = $urlQuery->limit();
        $page = $urlQuery->page();

        // dd($this->getQuery($request)->toRawSql());

        return $this->getQuery($request)->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllData(Request $request)
    {
        $urlQuery = $this->urlQuery($request);
        $limit = $urlQuery->limit();
        $page = $urlQuery->page();

        // dd($this->getQuery($request)->toRawSql());

        return $this->transformItems($request, $this->getQuery($request)->get());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getQuery(Request $request)
    {
        $urlQuery = $this->urlQuery($request);
        $searches = $urlQuery->searches();
        $trashed = $urlQuery->trashed();
        $sorts = $urlQuery->sorts();
        $includes = $urlQuery->includes();
        $excludes = $urlQuery->excludes();

        $query = RemoteLocation::with('customer');
        $model = $query->getModel();
        // $table = $model->getTable();
        $customerModel = new Customer();
        $customerTable = $customerModel->getTable();
        $ticketModel = new Ticket();
        // $ticketTable = $ticketModel->getTable();
        $ticketTimerModel = new TicketTimer();
        // $ticketTimerTable = $ticketTimerModel->getTable();
        $applicableMonth = $request->applicable_month ? Carbon::parse($request->applicable_month) : Carbon::now();
        $startDate = $applicableMonth->copy()->startOfMonth();
        $endDate = $applicableMonth->copy()->endOfMonth();
        $currentTotalDays = $applicableMonth->daysInMonth;
        // $currentTotalHours = $currentTotalDays * 24;
        // $generateTimerSql = function ($status, $forAvailability = false) use ($ticketModel, $ticketTimerModel, $ticketTable, $model) {
        //     $sqlNow = config('app.timezone') === 'UTC' ? 'UTC_TIMESTAMP()' : 'NOW()';

        //     if (! is_array($status)) {
        //         $status = [$status];
        //     }

        //     $query = TicketTimer::addSelect(DB::raw('SUM(TIMESTAMPDIFF(SECOND, started_at, IF(ended_at IS NULL, ' . $sqlNow . ', ended_at)))'))
        //         ->whereRaw($ticketModel->qualifyColumn('id') . ' = ' . $ticketTimerModel->qualifyColumn('ticket_id'))
        //         ->whereIn($ticketTimerModel->qualifyColumn('status'), $status);

        //     if ($forAvailability) {
        //         $query->leftJoin($ticketTable, function ($join) use ($ticketModel, $model) {
        //             $join->on($ticketModel->qualifyColumn('id'), '=', $model->qualifyColumn('customer_id'))
        //                 ->whereNull($ticketModel->qualifyColumn('deleted_at'));
        //         })->where($ticketModel->qualifyColumn('down_time_caused_by'), Ticket::DOWN_TIME_PROVIDER);
        //     }

        //     return $query->toRawSql();
        // };

        // $generateMttrSql = function () use ($ticketModel, $ticketTimerModel) {
        //     $sqlNow = config('app.timezone') === 'UTC' ? 'UTC_TIMESTAMP()' : 'NOW()';

        //     $query = TicketTimer::addSelect(DB::raw('SUM(TIMESTAMPDIFF(SECOND, started_at, IF(ended_at IS NULL, ' . $sqlNow . ', ended_at)))'))
        //         ->whereRaw($ticketModel->qualifyColumn('id') . ' = ' . $ticketTimerModel->qualifyColumn('ticket_id'))
        //         ->whereIn($ticketTimerModel->qualifyColumn('status'), [TicketTimer::TIMER_OPEN, TicketTimer::TIMER_START])
        //         ->where($ticketModel->qualifyColumn('down_time_caused_by'), Ticket::DOWN_TIME_PROVIDER);

        //     return $query->toRawSql();
        // };

        // $generateDownTimeSql = function ($causedBy) use ($ticketModel, $ticketTimerModel) {
        //     $sqlNow = config('app.timezone') === 'UTC' ? 'UTC_TIMESTAMP()' : 'NOW()';

        //     $query = TicketTimer::addSelect(DB::raw('SUM(TIMESTAMPDIFF(SECOND, started_at, IF(ended_at IS NULL, ' . $sqlNow . ', ended_at)))'))
        //         ->whereRaw($ticketModel->qualifyColumn('id') . ' = ' . $ticketTimerModel->qualifyColumn('ticket_id'))
        //         ->whereIn($ticketTimerModel->qualifyColumn('status'), [TicketTimer::TIMER_START, TicketTimer::TIMER_PENDING, TicketTimer::TIMER_OPEN])
        //         ->where($ticketModel->qualifyColumn('down_time_caused_by'), $causedBy);

        //     return $query->toRawSql();
        // };

        $query->addSelect(DB::raw($model->getTable() . '.*'));
        $query->addSelect(DB::raw($currentTotalDays . ' as current_total_days'));
        $query->addSelect(DB::raw($customerModel->qualifyColumn('name') . ' as customer_name'));
        $query->addSelect(DB::raw($customerModel->qualifyColumn('sla') . ' as customer_sla'));
        // $query->addSelect(DB::raw('count(distinct ' . $ticketModel->qualifyColumn('id') . ') as total_tickets'));
        $query->addSelect(DB::raw("'" . $startDate->format('Y-m-d') . "' as start_date"));
        $query->addSelect(DB::raw("'" . $endDate->format('Y-m-d') . "' as end_date"));
        // $query->addSelect(DB::raw('('. $generateTimerSql(TicketTimer::TIMER_OPEN) . ') as open_clock'));
        // $query->addSelect(DB::raw('(' . $generateTimerSql(TicketTimer::TIMER_PENDING) . ') as pending_clock'));
        // $query->addSelect(DB::raw('(' . $generateTimerSql(TicketTimer::TIMER_START) . ') as start_clock'));
        // $query->addSelect(DB::raw('(' . $generateMttrSql() . ') as mttr'));
        // $query->addSelect(DB::raw('(' . $generateDownTimeSql(Ticket::DOWN_TIME_PROVIDER) . ') as down_time_provider'));
        // $query->addSelect(DB::raw('(' . $generateDownTimeSql(Ticket::DOWN_TIME_CUSTOMER) . ') as down_time_customer'));
        // $query->addSelect(DB::raw(sprintf(
        //     '(100.0 - ((' . $generateTimerSql([TicketTimer::TIMER_OPEN, TicketTimer::TIMER_PENDING, TicketTimer::TIMER_START], true) . ') / (%d * 3600.0) * 100.0)) as availability',
        //     $currentTotalHours,
        // )));


        // $query->leftJoin($ticketTable, function ($join) use ($ticketModel, $model, $startDate, $endDate) {
        //     $join->on($ticketModel->qualifyColumn('remote_location_id'), '=', $model->qualifyColumn('id'))
        //         ->whereNull($ticketModel->qualifyColumn('deleted_at'))
        //         ->where(function ($query) use ($ticketModel, $startDate, $endDate) {
        //             $query->whereNull($ticketModel->qualifyColumn('id'))
        //                 ->orWhere(function ($query) use ($ticketModel, $startDate, $endDate) {
        //                     $query->whereDate($ticketModel->qualifyColumn('created_at'), '>=', $startDate)
        //                         ->whereDate($ticketModel->qualifyColumn('created_at'), '<=', $endDate);
        //                 });
        //         })
        //         ->where($ticketModel->qualifyColumn('status'), Ticket::STATUS_CLOSED);
        // });

        $query->leftJoin($customerTable, function ($join) use ($customerModel, $model) {
            $join->on($customerModel->qualifyColumn('id'), '=', $model->qualifyColumn('customer_id'))
                ->whereNull($customerModel->qualifyColumn('deleted_at'));
        });

        // $query->groupBy($model->qualifyColumn('id'));

        foreach ($searches->values() as $search) {
            $operator = $search->operator();

            if ($search->hasRelation()) {
                $query->whereHas($search->relation(), function ($query) use ($search, $operator) {
                    $value = $search->value();
                    $model = $query->getModel();
                    $column = $search->column();

                    if ($operator->isLike()) {
                        if ($model->getKeyName() !== $search->column()) {
                            $value = '%' . $value . '%';
                        }
                    }

                    $column = $model->qualifyColumn($column);

                    if ($operator->isIn() || $operator->isNotIn()) {
                        $query->whereIn($column, $value, 'and', $operator->isNotIn());
                    } else if ($operator->isNull() || $operator->isNotNull()) {
                        $query->whereNull($column, 'and', $operator->isNotNull());
                    } else {
                        $query->where($column, $operator->value(), $value);
                    }
                });
            } else if ($search->column() === 'fuzzy') {
                $query->where(function ($q) use ($search) {
                    $value = $search->value();

                    if ($value) {
                        $value = '%' . $value . '%';

                        $q->where('terminal_name', 'like', $value)
                            ->orWhere('location', 'like', $value);
                    }
                });

            } else if ($model->isColumnExists($search->column())) {
                $value = $search->value();
                $column = $search->column();

                if ($operator->isLike()) {
                    if ($model->getKeyName() === $search->column()) {
                        $value .= '%';
                    } else {
                        $value = '%' . $value . '%';
                    }
                }

                $column = $model->qualifyColumn($column);

                if ($operator->isIn() || $operator->isNotIn()) {
                    $query->whereIn($column, $value, 'and', $operator->isNotIn());
                } else if ($operator->isNull() || $operator->isNotNull()) {
                    $query->whereNull($column, 'and', $operator->isNotNull());
                } else {
                    $query->where($column, $operator->value(), $value);
                }
            }
        }

        foreach ($sorts->values() as $sort) {
            if ($sort->hasRelation()) {
                $query->without($sort->relation())->with([$sort->relation() => function ($query) use ($sort) {
                    $query->orderBy($sort->column(), $sort->direction());
                }]);
            } else if ($model->isColumnExists($sort->column())) {
                $query->orderBy($sort->column(), $sort->direction());
            }
        }

        if ($includes->hasValues()) {
            $query->with($includes->values());
        }

        if ($excludes->hasValues()) {
            $query->without($excludes->values());
        }

        if ($trashed && method_exists($query, 'withTrashed')) {
            $query->withTrashed();
        }

        return $query;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $entry = $this->getQuery($request)->findOrFail($id);

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'report' => new ReportResource($entry),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $this->authorize('create.report');

        $input = $request->validate([
            'terminal_name' => ['required', 'max:191'],
            'location' => ['required'],
            'address' => ['sometimes', 'nullable'],
            'latitude' => ['sometimes', 'nullable', 'numeric'],
            'longitude' => ['sometimes', 'nullable', 'numeric'],
            'pic_name' => ['sometimes', 'nullable'],
            'pic_phone_number' => ['sometimes', 'nullable'],
            'pic_phone_number2' => ['sometimes', 'nullable'],
            'online_at' => ['sometimes', 'nullable', 'date'],
            'mttr' => ['sometimes', 'nullable', 'numeric'],
            'total_time' => ['sometimes', 'nullable', 'numeric'],
            'status' => ['sometimes', 'nullable', 'in:0,1'],
        ]);

        $report = Report::create($input);

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully created', ['entity' => __('Report')]),
            'data' => [
                'report' => new ReportResource($report),
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        // $this->authorize('edit.report');

        $report = Report::find($id);

        if (!$report) {
            return abort(404, __(':entity not found', ['entity' => __('Report')]));
        }

        $request->validate([
            'terminal_name' => ['sometimes', 'required', 'max:191'],
            'location' => ['sometimes', 'required'],
            'address' => ['sometimes', 'nullable'],
            'latitude' => ['sometimes', 'nullable', 'numeric'],
            'longitude' => ['sometimes', 'nullable', 'numeric'],
            'pic_name' => ['sometimes', 'nullable'],
            'pic_phone_number' => ['sometimes', 'nullable'],
            'pic_phone_number2' => ['sometimes', 'nullable'],
            'online_at' => ['sometimes', 'nullable', 'date'],
            'mttr' => ['sometimes', 'nullable', 'numeric'],
            'total_time' => ['sometimes', 'nullable', 'numeric'],
            'status' => ['sometimes', 'nullable', 'in:0,1'],
        ]);

        $report->fill([
            'terminal_name' => $request->filled('terminal_name') ? $request->terminal_name : $report->terminal_name,
            'location' => $request->filled('location') ? $request->location : $report->location,
            'address' => $request->has('address') ? $request->address : $report->address,
            'latitude' => $request->has('latitude') ? $request->latitude : $report->latitude,
            'longitude' => $request->has('longitude') ? $request->longitude : $report->longitude,
            'pic_name' => $request->has('pic_name') ? $request->pic_name : $report->pic_name,
            'pic_phone_number' => $request->has('pic_phone_number') ? $request->pic_phone_number : $report->pic_phone_number,
            'pic_phone_number2' => $request->has('pic_phone_number2') ? $request->pic_phone_number2 : $report->pic_phone_number2,
            'online_at' => $request->has('online_at') ? $request->online_at : $report->online_at,
            'mttr' => $request->has('mttr') ? $request->mttr : $report->mttr,
            'total_time' => $request->has('total_time') ? $request->total_time : $report->total_time,
            'status' => $request->has('status') ? (int) $request->status : $report->status,
        ]);

        $report->save();

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully updated', ['entity' => __('Report')]),
            'data' => [
                'report' => new ReportResource($report),
            ],
        ]);
    }

    public function destroy($id)
    {
        // $this->authorize('delete.report');

        $report = Report::find($id);

        if (!$report) {
            return abort(404, __(':entity not found', ['entity' => __('Report')]));
        }

        $report->delete();

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully deleted', ['entity' => __('Report')]),
            'data' => new \stdClass(),
        ]);
    }

    public function revisions(Request $request, $id)
    {
        $this->authorize('manage.report');

        $urlQuery = $this->urlQuery($request);
        $searches = $urlQuery->searches();
        $trashed = $urlQuery->trashed();
        $sorts = $urlQuery->sorts();
        $includes = $urlQuery->includes();
        $excludes = $urlQuery->excludes();
        $limit = $urlQuery->limit();
        $page = $urlQuery->page();

        $entry = Report::findOrFail($id);

        $query = $entry->revisions()->with('report');

        if (count($sorts->values())) {
            foreach ($sorts->values() as $sort) {
                if ($query->getModel()->isFillable($sort->column()) || $sort->column() == $entry->getKeyName()) {
                    $query->orderBy($sort->column(), $sort->direction());
                }
            }
        } else {
            $query->latest();
        }

        if ($includes->hasValues()) {
            $query->with($includes->values());
        }

        if ($excludes->hasValues()) {
            $query->without($excludes->values());
        }

        if ($trashed) {
            $query->withTrashed();
        }

        $entries = $query->paginate($limit, ['*'], 'page', $page);

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'reports' => $entries->data(),
            ],
            'meta' => [
                'searches' => $searches->original(),
                'total' => $entries->total(),
                'sorts' => $sorts->original(),
                'limit' => $limit,
                'page' => $page,
            ],
        ]);
    }



    public function export(Request $request)
    {
        if (!in_array($request->ext, ['xlsx', 'pdf'])) {
            return redirect('/reports');
        }

        // $data = $this->getData($request);
        // $searches = $this->getRequestSearch($request);

        // $date = null;

        // foreach ($searches as $search) {
        //     if ($search['column'] === 'date') {
        //         $date = $search['value'];
        //     }
        // }

        $ext = $request->ext;

        $date = $request->applicable_month ? Carbon::parse($request->applicable_month)->format('m-Y') : null;

        if ($ext === 'xlsx') {
            $ext = ExcelFactory::XLSX;
        } else if ($ext === 'pdf') {
            $ext = ExcelFactory::MPDF;
        }

        $export = new AvailabilityExport($request->except(['ext', 'api_token']), $request->ext);
        $exportFilename = 'report-' . $date . '-' . (date('U')) . '.' . $request->ext;

        try {
            if (intval($request->dl) === 1) {
                return Excel::download($export, $exportFilename);
            }

            if (intval($request->inspect) === 1) {
                return Excel::raw($export, $ext);
            }

            return Excel::download($export, $exportFilename);
        } catch (\Throwable $e) {
            //dd($e);
        }
    }
}
