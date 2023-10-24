<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RequestQueryParamBuilder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('manage.permission');

        $permissions = Permission::all()->groupBy('module');

        $response = [
            'status' => 'success',
            'message' => 'Ok',
            'data' => [
                'permissions' => $permissions
            ],
        ];

        return $this->json($response);
    }

    public function showForRole($id)
    {
        $this->authorize('edit.permission');

        $role = Role::find($id);

        if (! $role) {
            return abort(404, __(':entity not found', ['entity' => __('Role')]));
        }

        $permissions = $role->permissions()->pluck('id');

        return $this->json([
            'status' => 'success',
            'message' => 'Ok',
            'data' => [
                'permission_ids' => $permissions,
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
        $this->authorize('create.role');

        $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'nullable', 'in:0,1'],
        ]);

        $role = Role::create($request->all());

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully created', ['entity' => __('Role')]),
            'data' => [
                'role' => $role,
            ],
        ]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->authorize('edit.role');

        $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'nullable', 'in:0,1'],
        ]);

        $role = Role::find($id);

        if (! $role) {
            abort(404, __(':entity not found', ['entity' => __('Role')]));
        }

        $role->fill($request->all());
        $role->save();

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully updated', ['entity' => __('Role')]),
            'data' => [
                'role' => $role,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateForRole(Request $request, $id)
    {
        $this->authorize('edit.permission');

        $request->validate([
            'permissions' => ['required', 'array'],
        ]);

        $role = Role::find($id);

        if (! $role) {
            abort(404, __(':entity not found', ['entity' => __('Role')]));
        }

        $role->permissions()->sync($request->permissions);

        $role->flushCachedPermissionKeys();

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully updated', ['entity' => __('Permissions')]),
            'data' => new \stdClass(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('delete.role');

        $role = Role::find($id);

        if (! $role) {
            return abort(404, __(':entity not found', ['entity' => __('Role')]));
        }

        if ($role->id == Role::TYPE_SUPER_ADMIN) {
            return abort(403, __('Role can not be deleted'));
        }

        $role->delete();

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully deleted', ['entity' => __('Role')]),
            'data' => new \stdClass(),
        ]);
    }
}
