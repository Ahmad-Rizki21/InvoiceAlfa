<?php

namespace App\Http\Resources;

use App\Models\FranchiseAccessToken;

class FranchiseResource extends JsonResource
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
            'code' => (string) $this->code,
            'name' => (string) $this->name,
            'location' => $this->nullableString($this->location),
            'address' => $this->nullableString($this->address),
            'username' => (string) $this->username,
            'email' => (string) $this->email,
            'landline_number' => $this->nullableString($this->landline_number),
            'phone_number' => $this->nullableString($this->phone_number),
            'locale' => (string) $this->locale,
            'distribution_center_id' => $this->nullableString($this->distribution_center_id),
            'distribution_center' => $this->whenLoaded('distributionCenter', function () {
                return new DistributionCenterResource($this->distributionCenter);
            }),
            'approval_date' => $this->formatDateTime($this->approval_date),
            'fo_approval_date' => $this->formatDateTime($this->fo_approval_date),
            'offering_letter_reference_number' => $this->nullableString($this->offering_letter_reference_number),
            'fo_offering_letter_reference_number' => $this->nullableString($this->fo_offering_letter_reference_number),
            'issuance_number' => $this->nullableString($this->issuance_number),
            'fo_issuance_number' => $this->nullableString($this->fo_issuance_number),
            'has_password' => ! empty($this->password),
            'auth_type' => FranchiseAccessToken::tokenPrefix(),
            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
        ];
    }
}
