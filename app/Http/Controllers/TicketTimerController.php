<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TicketTimer;
use App\Http\Resources\TicketTimerResource;
use App\Models\Ticket;
use Carbon\Carbon;

class TicketTimerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $ticketId)
    {
        $request->merge([
            'ticket_id' => $ticketId,
        ]);

        $urlQuery = $this->urlQuery($request);
        $searches = $urlQuery->searches();
        $limit = $urlQuery->limit();
        $sorts = $urlQuery->sorts();
        $page = $urlQuery->page();

        $entries = $this->getData($request);

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'ticket_timers' => TicketTimerResource::collection($entries->items()),
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

        $query = TicketTimer::with('createdBy');
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
                'ticket_timer' => new TicketTimerResource($entry),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $ticketId)
    {
        // $this->authorize('create.ticket_timer');

        $input = $request->validate([
            'started_at' => ['required', 'max:191'],
            'ended_at' => ['sometimes', 'nullable'],
            'progress_message' => ['required'],
        ]);

        $input['ticket_id'] = $ticketId;

        $ticketTimer = TicketTimer::create($input);

        $ticketTimer->load('createdBy');

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully created', ['entity' => __('Timer')]),
            'data' => [
                'ticket_timer' => new TicketTimerResource($ticketTimer),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function toggle(Request $request, $ticketId)
    {
        // $this->authorize('create.ticket_timer');

        $request->validate([
            'status' => ['required', 'in:' . implode(',', TicketTimer::ALL_TIMERS)],
        ]);

        if ($request->status != TicketTimer::TIMER_OPEN) {
            $request->validate([
                'progress_message' => ['required'],
            ]);
        }

        $ticket = Ticket::find($ticketId);

        if (! $ticket) {
            return abort(404, __(':entity not found', ['entity' => __('Ticket')]));
        }

        $latestTimer = $ticket->latestTicketTimer;

        if (! $latestTimer) {
            if ($request->status != TicketTimer::TIMER_OPEN) {
                return $this->json([
                    'status' => 'fail',
                    'message' => __('Opening clock was not started. Try to reload your browser and try again.'),
                    // 'message' => __('Opening clock was already started. Try to reload your browser and try again.'),
                ], 400);
            }

            $nextStatus = TicketTimer::TIMER_OPEN;

            $ticketTimer = TicketTimer::create([
                'ticket_id' => $ticketId,
                'status' => $nextStatus,
                'progress_message' => $request->progress_message,
                'started_at' => Carbon::now(),
            ]);
        } else {
            if ($request->status == $latestTimer->status) {
                $message = __('Pending clock was already started. Try to reload your browser and try again.');

                if ($request->status == TicketTimer::TIMER_OPEN) {
                    return $this->json([
                        'status' => 'fail',
                        'message' => __('Opening clock was already started. Try to reload your browser and try again.'),
                    ], 400);
                } else if ($request->status == TicketTimer::TIMER_START) {
                    $message = __('Start clock was already started. Try to reload your browser and try again.');
                }

                return $this->json([
                    'status' => 'fail',
                    'message' => $message,
                ], 400);
            }

            $nextStatus = $request->status;

            $now = Carbon::now();

            $latestTimer->fill([
                'ended_at' => $now,
            ])->save();

            $ticketTimer = TicketTimer::create([
                'ticket_id' => $ticketId,
                'status' => $nextStatus,
                'progress_message' => $request->progress_message,
                'started_at' => $now,
            ]);
        }

        if ($request->status == TicketTimer::TIMER_OPEN) {
            $entity = __('Opening clock');
        } else if ($request->status == TicketTimer::TIMER_PENDING) {
            $entity = __('Pending clock');
        } else if ($request->status == TicketTimer::TIMER_START) {
            $entity = __('Start clock');
        }

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully started', ['entity' => $entity]),
            'data' => [
                'ticket_timer' => new TicketTimerResource($ticketTimer),
                'open_clock' => TicketTimer::calculateTimer($ticket, null, TicketTimer::TIMER_OPEN),
                'pending_clock' => TicketTimer::calculateTimer($ticket, null, TicketTimer::TIMER_PENDING),
                'start_clock' => TicketTimer::calculateTimer($ticket, null, TicketTimer::TIMER_START),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function complete(Request $request, $ticketId)
    {
        // $this->authorize('create.ticket_timer');

        $ticket = Ticket::find($ticketId);

        if (! $ticket) {
            return abort(404, __(':entity not found', ['entity' => __('Ticket')]));
        }

        $latestTimer = $ticket->latestTicketTimer;

        if (! $latestTimer) {
            return $this->json([
                'status' => 'fail',
                'message' => __('Opening clock was not started. Try to reload your browser and try again.'),
            ], 400);
        }

        if ($latestTimer->status == TicketTimer::TIMER_OPEN) {
            return $this->json([
                'status' => 'fail',
                'message' => __('Timer is still in an opening state. Try to reload your browser and try again.'),
            ], 400);
        }
        if ($latestTimer->status == TicketTimer::TIMER_PENDING) {
            return $this->json([
                'status' => 'fail',
                'message' => __('Timer is still in pending state. Try to reload your browser and try again.'),
            ], 400);
        }

        $request->validate([
            'progress_message' => ['required'],
        ]);

        $latestTimer->fill([
            'ended_at' => Carbon::now(),
        ])->save();

        $ticket->fill([
            'status' => Ticket::STATUS_CLOSED,
            'closed_at' => Carbon::now(),
            'closed_by_id' => $request->user()->id,
            'closed_message' => $request->progress_message,
        ])->save();

        $latestTimer->refresh();
        $latestTimer->load('createdBy');

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully closed', ['entity' => __('Ticket')]),
            'data' => [
                'ticket_timer' => new TicketTimerResource($latestTimer),
                'open_clock' => TicketTimer::calculateTimer($ticket, null, TicketTimer::TIMER_OPEN),
                'pending_clock' => TicketTimer::calculateTimer($ticket, null, TicketTimer::TIMER_PENDING),
                'start_clock' => TicketTimer::calculateTimer($ticket, null, TicketTimer::TIMER_START),
            ],
        ]);
    }

    public function update(Request $request, $ticketId, $id)
    {
        // $this->authorize('edit.ticket_timer');

        $ticketTimer = TicketTimer::find($id);

        if (!$ticketTimer) {
            return abort(404, __(':entity not found', ['entity' => __('Timer')]));
        }

        $request->validate([
            'progress_message' => ['required'],
        ]);

        $ticketTimer->fill([
            'progress_message' => $request->filled('progress_message') ? $request->progress_message : $ticketTimer->progress_message,
        ]);

        $ticketTimer->save();

        $ticketTimer->load('createdBy');

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully updated', ['entity' => __('Timer')]),
            'data' => [
                'ticket_timer' => new TicketTimerResource($ticketTimer),
            ],
        ]);
    }

    public function destroy($id)
    {
        // $this->authorize('delete.ticket_timer');

        $ticketTimer = TicketTimer::find($id);

        if (!$ticketTimer) {
            return abort(404, __(':entity not found', ['entity' => __('Timer')]));
        }

        $ticketTimer->delete();

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully deleted', ['entity' => __('Timer')]),
            'data' => new \stdClass(),
        ]);
    }

    public function revisions(Request $request, $id)
    {
        $this->authorize('manage.ticket_timer');

        $urlQuery = $this->urlQuery($request);
        $searches = $urlQuery->searches();
        $trashed = $urlQuery->trashed();
        $sorts = $urlQuery->sorts();
        $includes = $urlQuery->includes();
        $excludes = $urlQuery->excludes();
        $limit = $urlQuery->limit();
        $page = $urlQuery->page();

        $entry = TicketTimer::findOrFail($id);

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
                'ticket_timers' => $entries->data(),
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
}
