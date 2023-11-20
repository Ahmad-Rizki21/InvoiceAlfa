<?php

namespace App\Http\Resources;

use App\Models\DistributionCenterAccessToken;

class DistributionCenterResource extends JsonResource
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
            'npwp' => (string) $this->npwp,
            'npwp_formatted' =>  $this->npwp ? format_npwp((string) $this->npwp) : null,
            'name' => (string) $this->name,
            'location' => $this->nullableString($this->location),
            'address' => $this->nullableString($this->address),
            'username' => (string) $this->username,
            'email' => (string) $this->email,
            'landline_number' => $this->nullableString($this->landline_number),
            'phone_number' => $this->nullableString($this->phone_number),
            'locale' => (string) $this->locale,
            'stores' => $this->whenLoaded('stores', function () {
                return StoreResource::collection($this->stores);
            }),
            'approval_date' => $this->formatDateTime($this->approval_date),
            'fo_approval_date' => $this->formatDateTime($this->fo_approval_date),
            'offering_letter_reference_number' => $this->nullableString($this->offering_letter_reference_number),
            'fo_offering_letter_reference_number' => $this->nullableString($this->fo_offering_letter_reference_number),
            'issuance_number' => $this->nullableString($this->issuance_number),
            'fo_issuance_number' => $this->nullableString($this->fo_issuance_number),
            'transfer_to_virtual_account_bank_name' => $this->nullableString($this->transfer_to_virtual_account_bank_name),
            'transfer_to_virtual_account_number' => $this->nullableString($this->transfer_to_virtual_account_number),
            'auth_type' => DistributionCenterAccessToken::tokenPrefix(),
            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
        ];
    }
}
