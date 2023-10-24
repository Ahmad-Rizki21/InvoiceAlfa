<?php

namespace App\Http\Controllers;

use App\Http\Resources\DistributionCenterResource;
use App\Http\Resources\FranchiseResource;
use App\Http\Resources\UserResource;
use App\Models\DistributionCenter;
use App\Models\Franchise;
use App\Models\UserAccessToken;
use App\Models\User;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $input = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
            'client_code' => ['required'],
        ]);

        $user = User::where('username', $input['username'])->orWhere('email', $input['username'])->first();

        $failedErrorMessage = trans('auth.failed');

        if (! $user) {
            $user = DistributionCenter::where('username', $input['username'])->orWhere('email', $input['username'])->first();
        }

        if (! $user) {
            $user = Franchise::where('username', $input['username'])->orWhere('email', $input['username'])->first();
        }

        if (! $user) {
            return $this->json([
                'status' => 'fail',
                'message' => $failedErrorMessage,
                'errors' => [
                    'username' => [$failedErrorMessage],
                ],
            ], 422);
        }

        if (! $user->password) {
            return $this->json([
                'status' => 'fail',
                'message' => $failedErrorMessage,
                'errors' => [
                    'username' => [$failedErrorMessage],
                ],
            ], 422);
        }

        if (! Hash::check($request->password, $user->password)) {
            return $this->json([
                'status' => 'fail',
                'message' => $failedErrorMessage,
                'errors' => [
                    'password' => [$failedErrorMessage],
                ],
            ], 422);
        }

        event(new Authenticated('api', $user));

        $token = $user->makeToken([
            'client_code' => $request->client_code,
            'revoked' => 0,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // $accessTokenClass::where('channel', $authChannel)
        //                 ->where('user_id', $token->user_id)
        //                 ->where('user_agent', $token->user_agent)
        //                 ->where('id', '!=', $token->id)
        //                 ->delete();

        if ($user instanceof User) {
            $result = new UserResource($user);
        } else if ($user instanceof DistributionCenter) {
            $result = new DistributionCenterResource($user);
        } else if ($user instanceof Franchise) {
            $result = new FranchiseResource($user);
        }

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'token' => [
                    'token_type' => 'Bearer',
                    'access_token' => $token->token_hash,
                    'expires_at' => (string) $token->expires_at,
                ],
                'user' => $result,
            ],
        ]);
    }
}
