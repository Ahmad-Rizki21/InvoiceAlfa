<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Rules\UniqueEmailRule;
use App\Rules\UniqueUsernameRule;
use App\Rules\ValidUsernameRule;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
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
                'users' => UserResource::collection($entries->items()),
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

        $query = User::query();
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

                        $q->where('name', 'like', $value)
                            ->orWhere('email', 'like', $value)
                            ->orWhere('username', 'like', $value);
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
        $entry = $this->getQuery($request)->where(function ($q) use ($id) {
            $q->where('id', $id)->orWhere('username', $id);
        })->firstOrFail();

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'user' => new UserResource($entry),
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
        // $this->authorize('create.user');

        $input = $request->validate([
            'name' => ['required', 'max:191'],
            'email' => ['required', 'email', 'max:191', 'unique:' . User::class . ',email,NULL,id,deleted_at,NULL'],
            'username' => ['sometimes', 'nullable', 'min:3', 'max:30', new ValidUsernameRule(), 'unique:' . User::class . ',username,NULL,id,deleted_at,NULL'],
            'password' => ['required', 'min:6', 'confirmed'],
            'role_id' => ['required', 'exists:' . Role::class . ',id'],
        ]);

        $input['password'] = bcrypt($input['password']);

        $user = User::create($input);

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully created', ['entity' => __('User')]),
            'data' => [
                'user' => new UserResource($user),
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        // $this->authorize('edit.user');

        $user = User::find($id);

        if (!$user) {
            return abort(404, __(':entity not found', ['entity' => __('User')]));
        }

        $request->validate([
            'name' => ['sometimes', 'max:191'],
            'email' => ['sometimes', 'max:191', new UniqueEmailRule($user)],
            'username' => ['sometimes', 'nullable', 'min:3', 'max:30', new ValidUsernameRule(), new UniqueUsernameRule($user)],
            'role_id' => ['sometimes', 'exists:' . Role::class . ',id,deleted_at,NULL'],
        ]);

        if ($request->password) {
            $request->validate([
                'password' => ['required', 'min:6', 'confirmed'],
            ]);

            $user->password = bcrypt($request->password);
        }

        $user->fill([
            'name' => $request->filled('name') ? $request->name : $user->name,
            'email' => $request->filled('email') ? $request->email : $user->email,
            'username' => $request->filled('username') ? $request->username : $user->username,
            'role_id' => $request->filled('role_id') ? $request->role_id : $user->role_id,
        ]);

        $user->save();

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully updated', ['entity' => __('User')]),
            'data' => [
                'user' => new UserResource($user),
            ],
        ]);
    }

    public function destroy($id)
    {
        // $this->authorize('delete.user');

        $user = User::find($id);

        if (!$user) {
            return abort(404, __(':entity not found', ['entity' => __('User')]));
        }

        $user->delete();

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully deleted', ['entity' => __('User')]),
            'data' => new \stdClass(),
        ]);
    }

    public function revisions(Request $request, $id)
    {
        $this->authorize('manage.user');

        $urlQuery = $this->urlQuery($request);
        $searches = $urlQuery->searches();
        $trashed = $urlQuery->trashed();
        $sorts = $urlQuery->sorts();
        $includes = $urlQuery->includes();
        $excludes = $urlQuery->excludes();
        $limit = $urlQuery->limit();
        $page = $urlQuery->page();

        $entry = User::findOrFail($id);

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
                'users' => $entries->data(),
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
