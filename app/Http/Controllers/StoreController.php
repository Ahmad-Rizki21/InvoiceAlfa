<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Http\Resources\StoreResource;
use App\Models\DistributionCenter;
use App\Rules\UniqueEmailRule;
use App\Rules\UniqueUsernameRule;
use App\Rules\ValidUsernameRule;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
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

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'stores' => StoreResource::collection($entries->items()),
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

        $query = Store::query();
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

                        $q->where('name', 'like', $value);
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
                'store' => new StoreResource($entry),
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
        // $this->authorize('create.store');

        $input = $request->validate([
            'distribution_center_id' => ['required', 'exists:' . DistributionCenter::class . ',id'],
            'code' => ['sometimes', 'nullable', 'unique:' . Store::class . ',code'],
            'name' => ['required', 'max:191'],
            'email' => ['sometimes', 'nullable', 'email', 'max:191'],
            'landline_number' => ['sometimes', 'nullable'],
            'phone_number' => ['sometimes', 'nullable'],
            'location' => ['sometimes', 'nullable'],
            'address' => ['sometimes', 'nullable'],
            'approval_date' => ['sometimes', 'nullable', 'date'],
            'fo_approval_date' => ['sometimes', 'nullable', 'date'],
            'offering_letter_reference_number' => ['sometimes', 'nullable'],
            'fo_offering_letter_reference_number' => ['sometimes', 'nullable'],
            'issuance_number' => ['sometimes', 'nullable'],
            'fo_issuance_number' => ['sometimes', 'nullable'],
        ]);

        $entry = Store::create($input);

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully created', ['entity' => __('Store')]),
            'data' => [
                'store' => new StoreResource($entry),
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        // $this->authorize('edit.store');

        $entry = Store::find($id);

        if (!$entry) {
            return abort(404, __(':entity not found', ['entity' => __('Store')]));
        }

        $request->validate([
            'code' => ['sometimes', 'nullable', 'unique:' . Store::class . ',code,' . $id . ',id'],
            'name' => ['sometimes', 'nullable', 'max:191'],
            'email' => ['sometimes', 'nullable', 'email', 'max:191'],
            'landline_number' => ['sometimes', 'nullable'],
            'phone_number' => ['sometimes', 'nullable'],
            'location' => ['sometimes', 'nullable'],
            'address' => ['sometimes', 'nullable'],
            'approval_date' => ['sometimes', 'nullable', 'date'],
            'fo_approval_date' => ['sometimes', 'nullable', 'date'],
            'offering_letter_reference_number' => ['sometimes', 'nullable'],
            'fo_offering_letter_reference_number' => ['sometimes', 'nullable'],
            'issuance_number' => ['sometimes', 'nullable'],
            'fo_issuance_number' => ['sometimes', 'nullable'],
        ]);

        $entry->fill([
            'code' => $request->filled('code') ? $request->code : $entry->code,
            'name' => $request->filled('name') ? $request->name : $entry->name,
            'email' => $request->has('email') ? $request->email : $entry->email,
            'landline_number' => $request->has('landline_number') ? $request->landline_number : $entry->landline_number,
            'phone_number' => $request->has('phone_number') ? $request->phone_number : $entry->phone_number,
            'location' => $request->has('location') ? $request->location : $entry->location,
            'address' => $request->has('address') ? $request->address : $entry->address,
            'approval_date' => $request->has('approval_date') ? $request->approval_date : $entry->approval_date,
            'fo_approval_date' => $request->has('fo_approval_date') ? $request->fo_approval_date : $entry->fo_approval_date,
            'offering_letter_reference_number' => $request->has('offering_letter_reference_number') ? $request->offering_letter_reference_number : $entry->offering_letter_reference_number,
            'fo_offering_letter_reference_number' => $request->has('fo_offering_letter_reference_number') ? $request->fo_offering_letter_reference_number : $entry->fo_offering_letter_reference_number,
            'issuance_number' => $request->has('issuance_number') ? $request->issuance_number : $entry->issuance_number,
            'fo_issuance_number' => $request->has('fo_issuance_number') ? $request->fo_issuance_number : $entry->fo_issuance_number,
        ]);

        $entry->save();

        $entry->refresh();

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully updated', ['entity' => __('Store')]),
            'data' => [
                'store' => new StoreResource($entry),
            ],
        ]);
    }

    public function destroy($id)
    {
        // $this->authorize('delete.store');

        $entry = Store::find($id);

        if (!$entry) {
            return abort(404, __(':entity not found', ['entity' => __('Store')]));
        }

        $entry->delete();

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully deleted', ['entity' => __('Store')]),
            'data' => new \stdClass(),
        ]);
    }

    public function revisions(Request $request, $id)
    {
        $this->authorize('manage.store');

        $urlQuery = $this->urlQuery($request);
        $searches = $urlQuery->searches();
        $trashed = $urlQuery->trashed();
        $sorts = $urlQuery->sorts();
        $includes = $urlQuery->includes();
        $excludes = $urlQuery->excludes();
        $limit = $urlQuery->limit();
        $page = $urlQuery->page();

        $entry = Store::findOrFail($id);

        $query = $entry->revisions()->with('store');

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
                'stores' => $entries->data(),
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
