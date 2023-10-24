<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RemoteLocation;
use App\Http\Resources\RemoteLocationResource;
use App\Models\Customer;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RemoteLocationController extends Controller
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
                'remote_locations' => RemoteLocationResource::collection($entries->items()),
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

        $query = RemoteLocation::query();
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

                        $q->where('code', 'like', $value)
                            ->orWhere('terminal_name', 'like', $value)
                            ->orWhere('distribution_center', 'like', $value);
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
                'remote_location' => new RemoteLocationResource($entry),
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
        // $this->authorize('create.remote_location');

        $input = $request->validate([
            'customer_id' => ['required', 'exists:' . Customer::class . ',id,deleted_at,NULL'],
            'code' => ['required', 'max:191'],
            'name' => ['required', 'max:191'],
            'terminal_name' => ['required', 'max:191'],
            'address' => ['sometimes', 'nullable'],
            'postal_code' => ['sometimes', 'nullable', 'numeric'],
            'latitude' => ['sometimes', 'nullable', 'numeric'],
            'longitude' => ['sometimes', 'nullable', 'numeric'],
            'online_at' => ['sometimes', 'nullable', 'date'],
            'pic_remote_name' => ['sometimes', 'nullable'],
            'pic_remote_phone_number' => ['sometimes', 'nullable', 'numeric'],
            'pic_it_sat_name' => ['sometimes', 'nullable'],
            'pic_it_sat_phone_number' => ['sometimes', 'nullable', 'numeric'],
            'infrastructure_type' => ['sometimes', 'nullable'],
            'gsm_no' => ['sometimes', 'nullable'],
            'gsm_provider' => ['sometimes', 'nullable'],
            'gsm_no2' => ['sometimes', 'nullable'],
            'gsm_provider2' => ['sometimes', 'nullable'],
            'cid_provider' => ['sometimes', 'nullable'],
            'fo_provider' => ['sometimes', 'nullable'],
            'sla' => ['sometimes', 'nullable', 'numeric'],
            'pic_fo_provider_name' => ['sometimes', 'nullable'],
            'pic_fo_provider_phone_number' => ['sometimes', 'nullable', 'numeric'],
            'pic_service_point_name' => ['sometimes', 'nullable'],
            'pic_service_point_phone_number' => ['sometimes', 'nullable', 'numeric'],
            'fo_contract_by_name' => ['sometimes', 'nullable'],
            'remark' => ['sometimes', 'nullable'],
            'distribution_center' => ['required'],
            'cid_no' => ['sometimes', 'nullable'],
        ]);

        $entry = RemoteLocation::create($input);

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully created', ['entity' => __('Remote location')]),
            'data' => [
                'remote_location' => new RemoteLocationResource($entry),
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        // $this->authorize('edit.remote_location');

        $entry = RemoteLocation::find($id);

        if (!$entry) {
            return abort(404, __(':entity not found', ['entity' => __('Remote location')]));
        }

        $request->validate([
            'customer_id' => ['sometimes', 'nullable', 'exists:' . Customer::class . ',id,deleted_at,NULL'],
            'code' => ['sometimes', 'nullable', 'max:191'],
            'name' => ['sometimes', 'nullable', 'string', 'max:191'],
            'terminal_name' => ['sometimes', 'nullable', 'string', 'max:191'],
            'address' => ['sometimes', 'nullable'],
            'postal_code' => ['sometimes', 'nullable', 'numeric'],
            'latitude' => ['sometimes', 'nullable', 'numeric'],
            'longitude' => ['sometimes', 'nullable', 'numeric'],
            'online_at' => ['sometimes', 'nullable', 'date'],
            'pic_remote_name' => ['sometimes', 'nullable'],
            'pic_remote_phone_number' => ['sometimes', 'nullable', 'numeric'],
            'pic_it_sat_name' => ['sometimes', 'nullable'],
            'pic_it_sat_phone_number' => ['sometimes', 'nullable', 'numeric'],
            'infrastructure_type' => ['sometimes', 'nullable'],
            'gsm_no' => ['sometimes', 'nullable'],
            'gsm_provider' => ['sometimes', 'nullable'],
            'gsm_no2' => ['sometimes', 'nullable'],
            'gsm_provider2' => ['sometimes', 'nullable'],
            'cid_provider' => ['sometimes', 'nullable'],
            'fo_provider' => ['sometimes', 'nullable'],
            // 'sla' => ['sometimes', 'nullable', 'numeric'],
            'pic_fo_provider_name' => ['sometimes', 'nullable'],
            'pic_fo_provider_phone_number' => ['sometimes', 'nullable', 'numeric'],
            'pic_service_point_name' => ['sometimes', 'nullable'],
            'pic_service_point_phone_number' => ['sometimes', 'nullable', 'numeric'],
            'fo_contract_by_name' => ['sometimes', 'nullable'],
            'remark' => ['sometimes', 'nullable'],
            'distribution_center' => ['sometimes', 'nullable'],
            'cid_no' => ['sometimes', 'nullable'],
        ]);

        $entry->fill([
            'customer_id' => $request->filled('customer_id') ? $request->customer_id : $entry->customer_id,
            'code' => $request->filled('code') ? $request->code : $entry->code,
            'name' => $request->filled('name') ? $request->name : $entry->name,
            'terminal_name' => $request->filled('terminal_name') ? $request->terminal_name : $entry->terminal_name,
            'address' => $request->filled('address') ? $request->address : $entry->address,
            'postal_code' => $request->has('postal_code') ? $request->postal_code : $entry->postal_code,
            'latitude' => $request->has('latitude') ? $request->latitude : $entry->latitude,
            'longitude' => $request->has('longitude') ? $request->longitude : $entry->longitude,
            'online_at' => $request->has('online_at') ? $request->online_at : $entry->online_at,
            'pic_remote_name' => $request->has('pic_remote_name') ? $request->pic_remote_name : $entry->pic_remote_name,
            'pic_remote_phone_number' => $request->has('pic_remote_phone_number') ? $request->pic_remote_phone_number : $entry->pic_remote_phone_number,
            'pic_it_sat_name' => $request->has('pic_it_sat_name') ? $request->pic_it_sat_name : $entry->pic_it_sat_name,
            'pic_it_sat_phone_number' => $request->has('pic_it_sat_phone_number') ? $request->pic_it_sat_phone_number : $entry->pic_it_sat_phone_number,
            'infrastructure_type' => $request->has('infrastructure_type') ? $request->infrastructure_type : $entry->infrastructure_type,
            'gsm_no' => $request->has('gsm_no') ? $request->gsm_no : $entry->gsm_no,
            'gsm_provider' => $request->has('gsm_provider') ? $request->gsm_provider : $entry->gsm_provider,
            'gsm_no2' => $request->has('gsm_no2') ? $request->gsm_no2 : $entry->gsm_no2,
            'gsm_provider2' => $request->has('gsm_provider2') ? $request->gsm_provider2 : $entry->gsm_provider2,
            'cid_provider' => $request->has('cid_provider') ? $request->cid_provider : $entry->cid_provider,
            'fo_provider' => $request->has('fo_provider') ? $request->fo_provider : $entry->fo_provider,
            // 'sla' => $request->has('sla') ? $request->sla : $entry->sla,
            'pic_fo_provider_name' => $request->has('pic_fo_provider_name') ? $request->pic_fo_provider_name : $entry->pic_fo_provider_name,
            'pic_fo_provider_phone_number' => $request->has('pic_fo_provider_phone_number') ? $request->pic_fo_provider_phone_number : $entry->pic_fo_provider_phone_number,
            'pic_service_point_name' => $request->has('pic_service_point_name') ? $request->pic_service_point_name : $entry->pic_service_point_name,
            'pic_service_point_phone_number' => $request->has('pic_service_point_phone_number') ? $request->pic_service_point_phone_number : $entry->pic_service_point_phone_number,
            'fo_contract_by_name' => $request->has('fo_contract_by_name') ? $request->fo_contract_by_name : $entry->fo_contract_by_name,
            'remark' => $request->has('remark') ? $request->remark : $entry->remark,
            'cid_no' => $request->filled('cid_no') ? $request->cid_no : $entry->cid_no,
            'distribution_center' => $request->filled('distribution_center') ? $request->distribution_center : $entry->distribution_center,
        ]);

        $entry->save();

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully updated', ['entity' => __('Remote location')]),
            'data' => [
                'remote_location' => new RemoteLocationResource($entry),
            ],
        ]);
    }

    public function destroy($id)
    {
        // $this->authorize('delete.remote_location');

        $entry = RemoteLocation::find($id);

        if (!$entry) {
            return abort(404, __(':entity not found', ['entity' => __('Remote location')]));
        }

        DB::transaction(function () use ($entry) {
            $entry->tickets()->delete();
            $entry->delete();
        });

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully deleted', ['entity' => __('Remote location')]),
            'data' => new \stdClass(),
        ]);
    }

    public function revisions(Request $request, $id)
    {
        $this->authorize('manage.remote_location');

        $urlQuery = $this->urlQuery($request);
        $searches = $urlQuery->searches();
        $trashed = $urlQuery->trashed();
        $sorts = $urlQuery->sorts();
        $includes = $urlQuery->includes();
        $excludes = $urlQuery->excludes();
        $limit = $urlQuery->limit();
        $page = $urlQuery->page();

        $entry = RemoteLocation::findOrFail($id);

        $query = $entry->revisions()->with('remote_location');

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
                'remote_locations' => $entries->data(),
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
