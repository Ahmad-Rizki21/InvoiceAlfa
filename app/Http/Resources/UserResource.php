<?php

namespace App\Http\Resources;

use App\Models\Auth\Role;
use App\Models\UserAccessToken;
use App\Services\Encrypter\Hashids;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => (int) $this->id,
            'hash' => Hashids::encodeUserId((int) $this->id),
            'username' => $this->nullableString($this->username),
            'name' => $this->nullableString($this->name),
            'phone_number' => $this->nullableString($this->phone_number),
            'email' => $this->nullableString($this->email),
            'role_id' => (int) $this->role_id,
            'role' => new RoleResource($this->role),
            'locale' => (string) $this->locale,
            'status' => (string) $this->status,
            'auth_type' => UserAccessToken::tokenPrefix(),
            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
        ];
    }
}
