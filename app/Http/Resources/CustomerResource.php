<?php

namespace App\Http\Resources;

use App\Models\Customer;

class CustomerResource extends JsonResource
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
            'id' => (string) $this->id,
            'code' => (string) $this->code,
            'type' => (int) $this->type,
            'address' => $this->nullableString($this->address),
            'distribution_center_id' => $this->nullableString($this->distribution_center_id),
            'distribution_center' => $this->when($this->type == Customer::TYPE_STORE, $this->whenLoaded('distributionCenter', function () {
                return new static($this->distributionCenter);
            })),
            'stores' => $this->when($this->type == Customer::TYPE_DISTRIBUTION_CENTER, $this->whenLoaded('stores', function () {
                return static::collection($this->stores);
            })),
            'name' => (string) $this->name,
            'corporate_name' => (string) $this->corporate_name,
            'username' => (string) $this->username,
            'email' => (string) $this->email,
            'landline_number' => (string) $this->landline_number,
            'phone_number' => (string) $this->phone_number,
            'locale' => (string) $this->locale,
            'approval_date' => $this->formatDateTime($this->approval_date),
            'fo_approval_date' => $this->formatDateTime($this->fo_approval_date),
            'offering_letter_reference_number' => $this->nullableString($this->offering_letter_reference_number),
            'fo_offering_letter_reference_number' => $this->nullableString($this->fo_offering_letter_reference_number),
            'issuance_number' => $this->nullableString($this->issuance_number),
            'fo_issuance_number' => $this->nullableString($this->fo_issuance_number),
            'price_per_distribution_center' => $this->nullableFloat($this->price_per_distribution_center),
            'price_per_store' => $this->nullableFloat($this->price_per_store),
            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
        ];
    }
}
