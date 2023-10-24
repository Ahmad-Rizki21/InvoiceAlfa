<?php

namespace App\Http\Controllers;

use App\Exports\TicketExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Http\Resources\TicketResource;
use App\Http\Resources\TicketTimerResource;
use App\Models\Customer;
use App\Models\RemoteLocation;
use App\Models\TicketTimer;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFactory;


class TicketController extends Controller
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
        $includes = $urlQuery->includes();

        $entries = $this->getData($request);

        $items = $entries->items();

        if ($includes->has('allClock')) {
            foreach ($items as $i => $item) {
                $items[$i]->setAttribute('open_clock', TicketTimer::calculateTimer($item, null, TicketTimer::TIMER_OPEN));
                $items[$i]->setAttribute('pending_clock', TicketTimer::calculateTimer($item, null, TicketTimer::TIMER_PENDING));
                $items[$i]->setAttribute('start_clock', TicketTimer::calculateTimer($item, null, TicketTimer::TIMER_START));
            }
        }

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'tickets' => TicketResource::collection($items),
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

        return $this->getQuery($request)->paginate($limit, ['*'], 'page', $page);
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

        $query = Ticket::query();
        $model = $query->getModel();

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

                        $q->where('no', 'like', $value)
                        ->orWhere('title', 'like', $value);
                    }
                });
            } else if ($search->column() === 'created_at_range') {
                $query->where(function ($q) use ($search) {
                    $value = $search->value();

                    if ($value) {
                        $value = explode(' - ', $value);

                        if (count($value) === 1) {
                            $q->whereDate('created_at', Carbon::createFromFormat('d/m/Y', $value[0]));
                        } else {
                            $q->whereDate('created_at', '>=', Carbon::createFromFormat('d/m/Y', $value[0]))
                                ->whereDate('created_at', '<=', Carbon::createFromFormat('d/m/Y', $value[1]));
                        }

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

                if (in_array($column, ['name', 'email', 'description'])) {
                    $value = strtolower($value);
                    $column = DB::raw('lower('.$model->qualifyColumn($column).')');
                } else {
                    $column = $model->qualifyColumn($column);
                }

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
            $query->with(array_filter($includes->values(), function ($include) {
                return !in_array($include, ['totalTimeTaken', 'allClock']);
            }));
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
                'ticket' => new TicketResource($entry),
            ],
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function latestTimer(Request $request, $id)
    {
        $request->merge(['includes' => 'latestTicketTimer']);

        $entry = $this->getQuery($request)->findOrFail($id);
        $latestTicketTimer = $entry->latestTicketTimer;

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'ticket_timer' => $latestTicketTimer ? new TicketTimerResource($latestTicketTimer) : new \stdClass(),
                'open_clock' => TicketTimer::calculateTimer($entry, null, TicketTimer::TIMER_OPEN),
                'pending_clock' => TicketTimer::calculateTimer($entry, null, TicketTimer::TIMER_PENDING),
                'start_clock' => TicketTimer::calculateTimer($entry, null, TicketTimer::TIMER_START),
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
        // $this->authorize('create.ticket');

        $input = $request->validate([
            'title' => ['sometimes', 'nullable', 'max:191'],
            'content' => ['required'],
            'customer_id' => ['required', 'exists:' . Customer::class . ',id,deleted_at,NULL'],
            'remote_location_id' => ['required', 'exists:' . RemoteLocation::class . ',id,deleted_at,NULL'],
            'down_time_caused_by' => ['required', 'in:' . implode(',', Ticket::ALL_DOWN_TIMES)],
            'status' => ['sometimes', 'nullable', 'in:' . implode(',', Ticket::ALL_STATUSES)],
        ]);

        DB::beginTransaction();
        $ticketTable = (new Ticket())->getTable();

        try {
            DB::statement("LOCK TABLE {$ticketTable} WRITE");

            $input['no'] = Ticket::generateTicketNo();
            $ticket = Ticket::create($input);

            DB::commit();

            DB::statement('UNLOCK TABLES');
        } catch (Exception $e) {
            DB::rollBack();

            DB::statement('UNLOCK TABLES');

            return $this->json([
                'status' => 'fail',
                'message' => __('Unable to create :entity. Please try again', ['entity' => __('Ticket')]),
            ], 500);
        }

        if ($request->auto_start_timer) {
            $ticketTimer = new TicketTimer([
                'ticket_id' => $ticket->id,
                'status' => TicketTimer::TIMER_OPEN,
                'started_at' => Carbon::now(),
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->created_at,
            ]);

            $ticketTimer->save(['touch' => false]);
        }

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully created', ['entity' => __('Ticket')]),
            'data' => [
                'ticket' => new TicketResource($ticket),
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        // $this->authorize('edit.ticket');

        $ticket = Ticket::find($id);

        if (!$ticket) {
            return abort(404, __(':entity not found', ['entity' => __('Ticket')]));
        }

        $request->validate([
            'title' => ['sometimes', 'nullable', 'max:191'],
            'content' => ['sometimes', 'nullable'],
            'customer_id' => ['sometimes', 'nullable', 'exists:' . Customer::class . ',id,deleted_at,NULL'],
            'remote_location_id' => ['sometimes', 'nullable', 'exists:' . RemoteLocation::class . ',id,deleted_at,NULL'],
            'down_time_caused_by' => ['sometimes', 'nullable', 'in:' . implode(',', Ticket::ALL_DOWN_TIMES)],
            'status' => ['sometimes', 'nullable', 'in:' . implode(',', Ticket::ALL_STATUSES)],
        ]);

        if ($request->status == Ticket::STATUS_CLOSED) {
            $request->merge(['closed_at' => Carbon::now()]);
        }

        $ticket->fill([
            'title' => $request->filled('title') ? $request->title : $ticket->title,
            'content' => $request->has('content') ? $request->content : $ticket->content,
            'customer_id' => $request->filled('customer_id') ? $request->customer_id : $ticket->customer_id,
            'remote_location_id' => $request->filled('remote_location_id') ? $request->remote_location_id : $ticket->remote_location_id,
            'status' => $request->filled('status') ? $request->status : $ticket->status,
            'down_time_caused_by' => $request->filled('down_time_caused_by') ? $request->down_time_caused_by : $ticket->down_time_caused_by,
            'closed_at' => $request->filled('closed_at') ? $request->closed_at : $ticket->closed_at,
            'closed_by_id' => $request->filled('closed_at') ? $request->user()->id : $ticket->closed_by_id,
        ]);

        $ticket->save();

        if ($ticket->status == Ticket::STATUS_CLOSED) {
            $now = Carbon::now();

            $ticket->ticketTimers()->whereNull('ended_at')->update([
                'ended_at' => $now,
            ]);

            $latestTimer = $ticket->latestTicketTimer()->first();

            if (! $latestTimer) {
                $ticket->ticketTimers()->create([
                    'status' => TicketTimer::TIMER_OPEN,
                    'started_at' => $now,
                    'ended_at' => $now,
                ]);
                $ticket->ticketTimers()->create([
                    'status' => TicketTimer::TIMER_PENDING,
                    'started_at' => $now,
                    'ended_at' => $now,
                ]);
                $ticket->ticketTimers()->create([
                    'status' => TicketTimer::TIMER_START,
                    'started_at' => $now,
                    'ended_at' => $now,
                ]);
            } else if ($latestTimer->status == TicketTimer::TIMER_OPEN) {
                $ticket->ticketTimers()->create([
                    'status' => TicketTimer::TIMER_PENDING,
                    'started_at' => $now,
                    'ended_at' => $now,
                ]);
                $ticket->ticketTimers()->create([
                    'status' => TicketTimer::TIMER_START,
                    'started_at' => $now,
                    'ended_at' => $now,
                ]);
            } else if ($latestTimer->status == TicketTimer::TIMER_PENDING) {
                $ticket->ticketTimers()->create([
                    'status' => TicketTimer::TIMER_START,
                    'started_at' => $now,
                    'ended_at' => $now,
                ]);
            }
        }

        $ticket->load('customer', 'remoteLocation');

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully updated', ['entity' => __('Ticket')]),
            'data' => [
                'ticket' => new TicketResource($ticket),
            ],
        ]);
    }

    public function destroy($id)
    {
        // $this->authorize('delete.ticket');

        $ticket = Ticket::find($id);

        if (!$ticket) {
            return abort(404, __(':entity not found', ['entity' => __('Ticket')]));
        }

        $ticket->delete();

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully deleted', ['entity' => __('Ticket')]),
            'data' => new \stdClass(),
        ]);
    }

    public function revisions(Request $request, $id)
    {
        $this->authorize('manage.ticket');

        $urlQuery = $this->urlQuery($request);
        $searches = $urlQuery->searches();
        $trashed = $urlQuery->trashed();
        $sorts = $urlQuery->sorts();
        $includes = $urlQuery->includes();
        $excludes = $urlQuery->excludes();
        $limit = $urlQuery->limit();
        $page = $urlQuery->page();

        $entry = Ticket::findOrFail($id);

        $query = $entry->revisions()->with('user');

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
                'tickets' => $entries->data(),
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
            return redirect('/tickets');
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

        $export = new TicketExport($request->except(['ext', 'api_token']), $request->ext);
        $exportFilename = 'tickets-' . $date . '' . (date('U')) . '.' . $request->ext;

        try {
            if (intval($request->dl) === 1) {
                return Excel::download($export, $exportFilename);
            }

            if (intval($request->inspect) === 1) {
                return Excel::raw($export, $ext);
            }

            return Excel::download($export, $exportFilename);
        } catch (\Throwable $e) {
            dd($e);
        }
    }
}
