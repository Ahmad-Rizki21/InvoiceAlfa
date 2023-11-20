<?php

namespace App\Http\Controllers;

use App\Enums\ImportType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Franchise;
use App\Http\Resources\FranchiseResource;
use App\Imports\ImportCacheImport;
use App\Models\DistributionCenter;
use App\Models\ImportCache;
use App\Rules\UniqueEmailRule;
use App\Rules\UniqueUsernameRule;
use App\Rules\ValidUsernameRule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class FranchiseController extends Controller
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
                'franchises' => FranchiseResource::collection($entries->items()),
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

        $query = Franchise::query();
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
                'franchise' => new FranchiseResource($entry),
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
        // $this->authorize('create.franchise');

        $input = $request->validate([
            'distribution_center_id' => ['required', 'exists:' . DistributionCenter::class . ',id'],
            'code' => ['sometimes', 'nullable', 'unique:' . Franchise::class . ',code,NULL,id'],
            'name' => ['required', 'max:191'],
            'email' => ['sometimes', 'nullable', 'email', 'max:191', new UniqueEmailRule()],
            'username' => ['bail', 'sometimes', 'nullable', 'min:3', 'max:30', new ValidUsernameRule(), new UniqueUsernameRule()],
            // 'password' => ['required', 'min:6', 'confirmed'],
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
            'transfer_to_virtual_account_bank_name' => ['sometimes', 'nullable'],
            'transfer_to_virtual_account_number' => ['sometimes', 'nullable'],
            'npwp' => ['sometimes', 'nullable'],
        ]);

        if ($request->password) {
            $request->validate([
                'password' => ['required', 'min:6', 'confirmed'],
            ]);

            $input['password'] = bcrypt($request->password);
        }

        $entry = Franchise::create($input);

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully created', ['entity' => __('Franchise')]),
            'data' => [
                'franchise' => new FranchiseResource($entry),
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        // $this->authorize('edit.franchise');

        $entry = Franchise::find($id);

        if (!$entry) {
            return abort(404, __(':entity not found', ['entity' => __('Franchise')]));
        }

        $request->validate([
            'code' => ['sometimes', 'nullable', 'unique:' . Franchise::class . ',code,' . $id . ',id'],
            'name' => ['sometimes', 'nullable', 'max:191'],
            'email' => ['sometimes', 'nullable', 'email', 'max:191', new UniqueEmailRule($entry)],
            'username' => ['sometimes', 'nullable', 'min:3', 'max:30', new ValidUsernameRule(), new UniqueUsernameRule($entry)],
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
            'transfer_to_virtual_account_bank_name' => ['sometimes', 'nullable'],
            'transfer_to_virtual_account_number' => ['sometimes', 'nullable'],
            'npwp' => ['sometimes', 'nullable'],
        ]);

        if ($request->password) {
            $request->validate([
                'password' => ['required', 'min:6', 'confirmed'],
            ]);

            $entry->password = bcrypt($request->password);
        }

        $entry->fill([
            'code' => $request->filled('code') ? $request->code : $entry->code,
            'name' => $request->filled('name') ? $request->name : $entry->name,
            'email' => $request->filled('email') ? $request->email : $entry->email,
            'username' => $request->filled('username') ? $request->username : $entry->username,
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
            'transfer_to_virtual_account_bank_name' => $request->has('transfer_to_virtual_account_bank_name') ? $request->transfer_to_virtual_account_bank_name : $entry->transfer_to_virtual_account_bank_name,
            'transfer_to_virtual_account_number' => $request->has('transfer_to_virtual_account_number') ? $request->transfer_to_virtual_account_number : $entry->transfer_to_virtual_account_number,
            'npwp' => $request->has('npwp') ? $request->npwp : $entry->npwp,
        ]);

        $entry->save();

        $entry->refresh();

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully updated', ['entity' => __('Franchise')]),
            'data' => [
                'franchise' => new FranchiseResource($entry),
            ],
        ]);
    }


    public function importUpload(Request $request)
    {
        // $this->authorize('manage.distribution_center');

        $request->validate([
            'file' => ['bail', 'required', 'file', 'mimes:xls,xlsx', 'max:' . (1024 * 500)],
        ]);

        $importPath = ImportCacheImport::uploadFile($request->file('file'));

        return $this->json([
            'status' => 'success',
            'data' => [
                'import_path' => $importPath,
            ],
        ]);
    }

    public function importCache(Request $request)
    {
        $importPath = $request->import_path;
        // $this->authorize('manage.distribution_center');

        Excel::import(new ImportCacheImport(ImportType::DistributionCenter, $importPath), ImportCacheImport::getUploadedFile($importPath));

        return $this->json([
            'status' => 'success',
            'data' => [
                'import_path' => $importPath,
            ],
        ]);
    }

    public function importProcess(Request $request)
    {
        $importPath = $request->import_path;
        // $this->authorize('manage.distribution_center');

        $limit = 50;
        $page = $request->page ?: 1;
        $entries = ImportCache::where('import_path', $importPath)->paginate($limit, ['*'], 'page', $page);

        $rules = [
            'distribution_center_id' => ['required', 'exists:' . DistributionCenter::class . ',id'],
            'code' => ['sometimes', 'nullable', 'unique:' . Franchise::class . ',code,NULL,id'],
            'name' => ['required', 'max:191'],
            'email' => ['sometimes', 'nullable', 'email', 'max:191', new UniqueEmailRule()],
            'username' => ['bail', 'sometimes', 'nullable', 'min:3', 'max:30', new ValidUsernameRule(), new UniqueUsernameRule()],
            // 'password' => ['required', 'min:6', 'confirmed'],
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
            'transfer_to_virtual_account_bank_name' => ['sometimes', 'nullable'],
            'transfer_to_virtual_account_number' => ['sometimes', 'nullable'],
            'npwp' => ['sometimes', 'nullable'],
        ];
        $deletingIds = [];
        $hasError = false;

        foreach ($entries as $entry) {
            $content = $entry->content ?: [];

            $content = [
                'name' => $content[0] ?? null,
                'code' => $content[1] ?? null,
                'location' => $content[2] ?? null,
                'address' => $content[3] ?? null,
                'approval_date' => $content[4] ?? null,
                'fo_approval_date' => $content[5] ?? null,
                'offering_letter_reference_number' => $content[6] ?? null,
                'fo_offering_letter_reference_number' => $content[7] ?? null,
                'issuance_number' => $content[8] ?? null,
                'fo_issuance_number' => $content[9] ?? null,
                'email' => $content[10] ?? null,
                'username' => $content[11] ?? null,
                'password' => $content[12] ?? $content[1] ?? null,
                'landline_number' => $content[13] ?? null,
                'phone_number' => $content[14] ?? null,
            ];

            $validator = Validator::make($content, $rules);
            $errors = [];

            if ($validator->fails()) {
                $errors = $validator->errors()->getMessages();
                $entry->content = $content;
                $entry->errors = $errors;
                $entry->save();
                $hasError = true;
            } else {
                $deletingIds[] = $entry->id;

                if (isset($content['approval_date']) && !empty($content['approval_date'])) {
                    $content['approval_date'] = Carbon::createFromFormat('d/m/Y', $content['approval_date']);
                }
                if (isset($content['approval_date']) && !empty($content['approval_date'])) {
                    $content['fo_approval_date'] = Carbon::createFromFormat('d/m/Y', $content['fo_approval_date']);
                }
                if (isset($content['password']) && !empty($content['password'])) {
                    $content['password'] = bcrypt($content['password']);
                }
                Franchise::create($content);
            }
        }

        if (count($deletingIds)) {
            ImportCache::whereIn('id', $deletingIds)->delete();
        }

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'import_path' => $importPath,
                'has_error' => $hasError,
            ],
            'meta' => [
                'searches' => '',
                'total' => $entries->total(),
                'sorts' => '',
                'limit' => $limit,
                'page' => $page,
                'has_more_page' => $entries->hasMorePages(),
            ],
        ]);
    }

    public function importFix(Request $request)
    {
        $importPath = $request->import_path;

        $rules = [
            'distribution_center_id' => ['required', 'exists:' . DistributionCenter::class . ',id'],
            'code' => ['sometimes', 'nullable', 'unique:' . Franchise::class . ',code,NULL,id'],
            'name' => ['required', 'max:191'],
            'email' => ['sometimes', 'nullable', 'email', 'max:191', new UniqueEmailRule()],
            'username' => ['bail', 'sometimes', 'nullable', 'min:3', 'max:30', new ValidUsernameRule(), new UniqueUsernameRule()],
            // 'password' => ['required', 'min:6', 'confirmed'],
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
            'transfer_to_virtual_account_bank_name' => ['sometimes', 'nullable'],
            'transfer_to_virtual_account_number' => ['sometimes', 'nullable'],
            'npwp' => ['sometimes', 'nullable'],
        ];


        $deletingIds = [];
        $hasError = false;

        foreach ($request->import_cache as $data) {
            $entry = ImportCache::findOrFail($data['id']);

            $validator = Validator::make($data['content'], $rules);
            $errors = [];

            if ($validator->fails()) {
                $errors = $validator->errors()->getMessages();
                $entry->content = $data['content'];
                $entry->errors = $errors;
                $entry->save();
                $hasError = true;
            } else {
                $deletingIds[] = $entry->id;
                $content = $data['content'];

                if (isset($content['approval_date']) && !empty($content['approval_date'])) {
                    $content['approval_date'] = Carbon::createFromFormat('d/m/Y', $content['approval_date']);
                }
                if (isset($content['fo_approval_date']) && !empty($content['fo_approval_date'])) {
                    $content['fo_approval_date'] = Carbon::createFromFormat('d/m/Y', $content['fo_approval_date']);
                }
                if (isset($content['password']) && !empty($content['password'])) {
                    $content['password'] = bcrypt($content['password']);
                }
                DistributionCenter::create($content);
            }
        }

        if (count($deletingIds)) {
            ImportCache::whereIn('id', $deletingIds)->delete();
        }

        return $this->json([
            'status' => $hasError ? 'fail' : 'success',
            'message' => __('Ok'),
        ]);
    }

    public function importErrors(Request $request)
    {
        $urlQuery = $this->urlQuery($request);
        $searches = $urlQuery->searches();
        $limit = $urlQuery->limit();
        $sorts = $urlQuery->sorts();
        $page = $urlQuery->page();
        $importPath = $request->import_path;
        // $this->authorize('manage.distribution_center');

        $entries = ImportCache::where('import_path', $importPath)->whereNotNull('errors')->paginate($limit, ['*'], 'page', $page);

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'import_cache' => $entries->items(),
            ],
            'meta' => [
                'searches' => '',
                'total' => $entries->total(),
                'sorts' => '',
                'limit' => $limit,
                'page' => $page,
                'has_more_page' => $entries->hasMorePages(),
            ],
        ]);
    }

    public function importRowDelete(Request $request, $id)
    {
        ImportCache::findOrFail($id)->delete();

        return $this->json([]);
    }

    public function destroy($id)
    {
        // $this->authorize('delete.franchise');

        $entry = Franchise::find($id);

        if (!$entry) {
            return abort(404, __(':entity not found', ['entity' => __('Franchise')]));
        }

        $entry->delete();

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully deleted', ['entity' => __('Franchise')]),
            'data' => new \stdClass(),
        ]);
    }

    public function revisions(Request $request, $id)
    {
        $this->authorize('manage.franchise');

        $urlQuery = $this->urlQuery($request);
        $searches = $urlQuery->searches();
        $trashed = $urlQuery->trashed();
        $sorts = $urlQuery->sorts();
        $includes = $urlQuery->includes();
        $excludes = $urlQuery->excludes();
        $limit = $urlQuery->limit();
        $page = $urlQuery->page();

        $entry = Franchise::findOrFail($id);

        $query = $entry->revisions()->with('franchise');

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
                'franchises' => $entries->data(),
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
