<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TicketNote;
use App\Http\Resources\TicketNoteResource;

class TicketNoteController extends Controller
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
                'ticket_notes' => TicketNoteResource::collection($entries->items()),
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

        $query = TicketNote::with('createdBy');
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
                'ticket_note' => new TicketNoteResource($entry),
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
        // $this->authorize('create.ticket_note');

        $input = $request->validate([
            'message' => ['required'],
        ]);

        $input['ticket_id'] = $ticketId;

        $ticketNote = TicketNote::create($input);

        $ticketNote->load('createdBy');

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully created', ['entity' => __('Note')]),
            'data' => [
                'ticket_note' => new TicketNoteResource($ticketNote),
            ],
        ]);
    }

    public function update(Request $request, $ticketId, $id)
    {
        // $this->authorize('edit.ticket_note');

        $ticketNote = $this->getQuery($request)->find($id);

        if (!$ticketNote) {
            return abort(404, __(':entity not found', ['entity' => __('Note')]));
        }

        $request->validate([
            'message' => ['sometimes', 'nullable'],
        ]);

        $ticketNote->fill([
            'message' => $request->has('message') ? $request->message : $ticketNote->message,
        ]);

        $ticketNote->save();

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully updated', ['entity' => __('Note')]),
            'data' => [
                'ticket_note' => new TicketNoteResource($ticketNote),
            ],
        ]);
    }

    public function destroy($ticketId, $id)
    {
        // $this->authorize('delete.ticket_note');
        $ticketNote = TicketNote::find($id);

        if (!$ticketNote) {
            return abort(404, __(':entity not found', ['entity' => __('Note')]));
        }

        $ticketNote->delete();

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully deleted', ['entity' => __('Note')]),
            'data' => new \stdClass(),
        ]);
    }

    public function revisions(Request $request, $id)
    {
        $this->authorize('manage.ticket_note');

        $urlQuery = $this->urlQuery($request);
        $searches = $urlQuery->searches();
        $trashed = $urlQuery->trashed();
        $sorts = $urlQuery->sorts();
        $includes = $urlQuery->includes();
        $excludes = $urlQuery->excludes();
        $limit = $urlQuery->limit();
        $page = $urlQuery->page();

        $entry = TicketNote::findOrFail($id);

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
                'ticket_notes' => $entries->data(),
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
