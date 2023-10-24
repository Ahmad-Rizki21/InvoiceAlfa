<?php

namespace App\Http\Controllers;

use App\Http\Resources\DistributionCenterResource;
use App\Http\Resources\FranchiseResource;
use App\Http\Resources\UserResource;
use App\Models\DistributionCenter;
use App\Models\Franchise;
use App\Models\User;
use App\Rules\UniqueEmailRule;
use App\Rules\UniqueUsernameRule;
use App\Rules\ValidUsernameRule;
use App\Services\AppleSignin\RevokeToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    /**
     * Fetch current authenticated user data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function me(Request $request)
    {
        $user = $request->user();

        if ($user instanceof DistributionCenter) {
            $resource = DistributionCenterResource::class;
        } else if ($user instanceof Franchise) {
            $resource = FranchiseResource::class;
        } else {
            $resource = UserResource::class;
        }

        $data = [
            'user' => new $resource($user),
        ];

        $data['permissions'] = $user->permissions;

        return response()->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => $data,
        ]);
    }

    /**
     * Update current authenticated user data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateMe(Request $request)
    {
        $user = $request->user();

        $input = $this->validate($request, [
            'name' => ['sometimes', 'string'],
            'email' => ['sometimes', 'email', new UniqueEmailRule($user)],
            'username' => ['sometimes', 'min:3', 'max:30', new ValidUsernameRule(), new UniqueUsernameRule($user)],
            'locale' => ['sometimes', 'string', 'in:en,id'],
        ]);

        $user->fill([
            'name' => $input['name'] ?? $user->name,
            'username' => $input['username'] ?? $user->username,
            'email' => $input['email'] ?? $user->email,
            'locale' => $input['locale'] ?? $user->locale,
        ]);

        if ($request->password) {
            $request->validate([
                'password' => ['required', 'confirmed', 'min:6'],
            ]);

            $user->password = bcrypt($request->password);
        }

        $user->save();

        if (isset($input['locale'])) {
            app()->setLocale($input['locale']);
        }

        return response()->json([
            'status' => 'success',
            'message' => __('Successfully updated your profile'),
            'data' => [
                'user' => new UserResource($user),
                'permissions' => $user->permissions,
            ],
        ]);
    }

    public function destroyMe(request $request)
    {
        $user = $request->user();

        return DB::transaction(function () use ($user) {
            $user->delete();

            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            return response()->json([
                'status' => 'success',
                'message' => __(':entity successfully deleted', ['entity' => __('User')]),
                'data' => [
                    'user' => new UserResource($user),
                ],
            ]);
        }, 2);

    }
}
